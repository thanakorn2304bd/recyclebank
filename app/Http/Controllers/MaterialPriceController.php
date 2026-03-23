<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MaterialPriceController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $q = trim($request->string('q')->toString());
        $categoryId = $request->integer('category_id') ?: null;
        $materialId = $request->integer('material_id') ?: null;

        $materials = Material::query()
            ->select([
                'material.material_id',
                'material.material_name',
                'material.unit',
                'material.is_active',
                'material.category_id',
                'material_category.category_name',
                'current_price.price_id as current_price_id',
                'current_price.price as current_price_value',
                'current_price.effective_date as current_effective_date',
                'current_price.expired_date as current_expired_date',
            ])
            ->leftJoin('material_category', 'material.category_id', '=', 'material_category.category_id')
            ->leftJoinSub($this->currentPriceReferenceQuery($today), 'current_price_ref', function ($join) {
                $join->on('material.material_id', '=', 'current_price_ref.material_id');
            })
            ->leftJoin('material_price as current_price', 'current_price.price_id', '=', 'current_price_ref.price_id')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('material.material_name', 'like', "%{$q}%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('material.category_id', $categoryId);
            })
            ->when($materialId, function ($query) use ($materialId) {
                $query->where('material.material_id', $materialId);
            })
            ->orderBy('material_category.category_name')
            ->orderBy('material.material_name')
            ->get();

        $categories = MaterialCategory::query()
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        $materialOptions = Material::query()
            ->orderBy('material_name')
            ->get(['material_id', 'material_name']);

        $summary = [
            'materials' => $materials->count(),
            'priced' => $materials->whereNotNull('current_price_id')->count(),
            'missing' => $materials->whereNull('current_price_id')->count(),
            'active' => $materials->where('is_active', true)->count(),
        ];

        return view('material_prices.index', compact(
            'materials',
            'categories',
            'materialOptions',
            'summary',
            'q',
            'categoryId',
            'materialId'
        ));
    }

    public function create(Request $request)
    {
        $materialId = $request->integer('material_id') ?: null;

        return redirect()
            ->route('material-prices.index', array_filter([
                'material_id' => $materialId,
            ]))
            ->with('success', 'เปลี่ยนเป็นหน้าแก้ไขราคาแล้ว สามารถแก้หลายรายการพร้อมกันได้');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id'    => ['required','integer','exists:material,material_id'],
            'price'          => ['required','numeric','min:0'],
            'effective_date' => ['required','date'],
            'expired_date'   => ['nullable','date','after_or_equal:effective_date'],
        ]);

        $createdBy = Auth::id() ?? 1;

        DB::transaction(function () use ($data, $createdBy) {
            // ถ้ามี “ราคาที่ active อยู่” (expired_date null) ของวัสดุเดียวกัน
            // และกำลังใส่ราคาตัวใหม่ ให้ปิดของเก่าด้วย expired_date = วันก่อน effective_date ใหม่
            $effective = $data['effective_date'];

            $active = MaterialPrice::query()
                ->where('material_id', $data['material_id'])
                ->whereNull('expired_date')
                ->orderByDesc('effective_date')
                ->first();

            if ($active) {
                // ปิดราคาเก่าถ้าวันเริ่มใหม่ >= วันเริ่มเก่า
                $active->expired_date = date('Y-m-d', strtotime($effective . ' -1 day'));
                $active->save();
            }

            MaterialPrice::create([
                'material_id'    => $data['material_id'],
                'price'          => $data['price'],
                'effective_date' => $data['effective_date'],
                'expired_date'   => $data['expired_date'] ?? null,
                'created_by'     => $createdBy,
                'created_at'     => now(),
            ]);
        });

        $material = Material::query()->find($data['material_id']);
        $materialName = $material?->material_name ?? ('#' . $data['material_id']);

        ActivityLogger::forCurrentUser(
            'material_prices',
            "เพิ่มราคาวัสดุ {$materialName} = " . number_format((float) $data['price'], 2)
            . " บาท มีผล {$data['effective_date']}"
        );

        return redirect()->route('material-prices.index', ['material_id' => $data['material_id']])
            ->with('success', 'เพิ่มราคาวัสดุเรียบร้อย');
    }

    public function bulkUpdate(Request $request)
    {
        $rows = collect($request->input('rows', []));

        if ($rows->isEmpty()) {
            return redirect()
                ->route('material-prices.index', $this->priceEditorFilters($request))
                ->withErrors('ไม่พบข้อมูลราคาสำหรับบันทึก');
        }

        $materialIds = $rows->keys()
            ->map(fn ($materialId) => (int) $materialId)
            ->filter()
            ->values();

        $materials = Material::query()
            ->whereIn('material_id', $materialIds)
            ->get(['material_id', 'material_name'])
            ->keyBy('material_id');

        $priceIds = $rows->pluck('price_id')
            ->filter(fn ($priceId) => filled($priceId))
            ->map(fn ($priceId) => (int) $priceId)
            ->values();

        $existingPrices = MaterialPrice::query()
            ->whereIn('price_id', $priceIds)
            ->get()
            ->keyBy('price_id');

        $updates = [];
        $creates = [];
        $errors = [];

        foreach ($rows as $materialId => $row) {
            $materialId = (int) $materialId;
            $material = $materials->get($materialId);

            if (! $material) {
                continue;
            }

            $priceId = filled($row['price_id'] ?? null) ? (int) $row['price_id'] : null;
            $price = trim((string) ($row['price'] ?? ''));
            $effectiveDate = trim((string) ($row['effective_date'] ?? ''));
            $expiredDate = trim((string) ($row['expired_date'] ?? ''));

            if ($priceId === null && $price === '' && $effectiveDate === '' && $expiredDate === '') {
                continue;
            }

            $existingPrice = $priceId ? $existingPrices->get($priceId) : null;

            if ($priceId && (! $existingPrice || (int) $existingPrice->material_id !== $materialId)) {
                $errors["rows.$materialId.price"] = 'ไม่พบรายการราคาที่ต้องการแก้ไข';
                continue;
            }

            $validator = Validator::make([
                'price' => $price,
                'effective_date' => $effectiveDate,
                'expired_date' => $expiredDate,
            ], [
                'price' => ['required', 'numeric', 'min:0'],
                'effective_date' => ['required', 'date'],
                'expired_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            ], [
                'price.required' => 'กรุณากรอกราคา',
                'price.numeric' => 'ราคาต้องเป็นตัวเลข',
                'price.min' => 'ราคาต้องไม่น้อยกว่า 0',
                'effective_date.required' => 'กรุณาเลือกวันที่เริ่มใช้',
                'effective_date.date' => 'วันที่เริ่มใช้ไม่ถูกต้อง',
                'expired_date.date' => 'วันที่สิ้นสุดไม่ถูกต้อง',
                'expired_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มใช้',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errors["rows.$materialId.$field"] = $messages[0];
                }

                continue;
            }

            $normalizedPrice = number_format((float) $price, 2, '.', '');
            $normalizedExpiredDate = $expiredDate !== '' ? $expiredDate : null;

            if ($existingPrice) {
                $hasChanged = number_format((float) $existingPrice->price, 2, '.', '') !== $normalizedPrice
                    || $existingPrice->effective_date?->format('Y-m-d') !== $effectiveDate
                    || $existingPrice->expired_date?->format('Y-m-d') !== $normalizedExpiredDate;

                if ($hasChanged) {
                    $updates[] = [
                        'model' => $existingPrice,
                        'material_name' => $material->material_name,
                        'price' => $normalizedPrice,
                        'effective_date' => $effectiveDate,
                        'expired_date' => $normalizedExpiredDate,
                    ];
                }

                continue;
            }

            $creates[] = [
                'material_id' => $materialId,
                'material_name' => $material->material_name,
                'price' => $normalizedPrice,
                'effective_date' => $effectiveDate,
                'expired_date' => $normalizedExpiredDate,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        if ($updates === [] && $creates === []) {
            return redirect()
                ->route('material-prices.index', $this->priceEditorFilters($request))
                ->with('success', 'ไม่มีรายการราคาที่เปลี่ยนแปลง');
        }

        $createdBy = Auth::id() ?? 1;

        DB::transaction(function () use ($updates, $creates, $createdBy) {
            foreach ($updates as $update) {
                $update['model']->update([
                    'price' => $update['price'],
                    'effective_date' => $update['effective_date'],
                    'expired_date' => $update['expired_date'],
                ]);
            }

            foreach ($creates as $create) {
                MaterialPrice::create([
                    'material_id' => $create['material_id'],
                    'price' => $create['price'],
                    'effective_date' => $create['effective_date'],
                    'expired_date' => $create['expired_date'],
                    'created_by' => $createdBy,
                    'created_at' => now(),
                ]);
            }
        });

        $touchedMaterials = collect([...$updates, ...$creates])
            ->pluck('material_name')
            ->filter()
            ->values();

        $preview = $touchedMaterials->take(5)->implode(', ');
        $remaining = $touchedMaterials->count() - min(5, $touchedMaterials->count());

        ActivityLogger::forCurrentUser(
            'material_prices',
            'แก้ไขราคาวัสดุ ' . $touchedMaterials->count() . ' รายการ'
            . ($preview !== '' ? ' (' . $preview . ($remaining > 0 ? ' และอีก ' . $remaining . ' รายการ' : '') . ')' : '')
        );

        return redirect()
            ->route('material-prices.index', $this->priceEditorFilters($request))
            ->with('success', 'บันทึกการแก้ไขราคาเรียบร้อย ' . $touchedMaterials->count() . ' รายการ');
    }

    public function destroy(MaterialPrice $material_price)
    {
        $materialId = $material_price->material_id;
        $materialName = $material_price->material?->material_name ?? ('#' . $materialId);
        $price = (float) $material_price->price;
        $effectiveDate = $material_price->effective_date?->format('Y-m-d') ?? '-';

        $material_price->delete();

        ActivityLogger::forCurrentUser(
            'material_prices',
            "ลบราคาวัสดุ {$materialName} = " . number_format($price, 2) . " บาท มีผล {$effectiveDate}"
        );

        return redirect()->route('material-prices.index', ['material_id' => $materialId])
            ->with('success', 'ลบราคาวัสดุเรียบร้อย');
    }

    public function materialPrices(Material $material)
    {
        $prices = MaterialPrice::where('material_id', $material->material_id)
            ->orderByDesc('effective_date')
            ->paginate(20);

        return view('material_prices.material_prices', compact('material', 'prices'));
    }

    private function currentPriceReferenceQuery(string $today)
    {
        return DB::table('material_price')
            ->select('material_id', DB::raw('MAX(price_id) as price_id'))
            ->where('effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', $today);
            })
            ->groupBy('material_id');
    }

    private function priceEditorFilters(Request $request): array
    {
        return array_filter([
            'q' => trim($request->string('q')->toString()),
            'category_id' => $request->integer('category_id') ?: null,
            'material_id' => $request->integer('material_id') ?: null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}

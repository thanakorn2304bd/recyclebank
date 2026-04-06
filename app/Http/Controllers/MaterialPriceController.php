<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateMaterialPricesRequest;
use App\Http\Requests\StoreMaterialPriceRequest;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Support\ActivityLogger;
use App\Support\MaterialPrices\MaterialPriceEditorViewDataFactory;
use App\Support\MaterialPrices\MaterialPriceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialPriceController extends Controller
{
    public function index(Request $request, MaterialPriceEditorViewDataFactory $viewDataFactory)
    {
        $targetMonthDate = $this->resolveTargetMonth($request->string('target_month')->toString());
        $minimumTargetMonth = CarbonImmutable::now()->startOfMonth()->format('Y-m');
        $targetMonth = $targetMonthDate->format('Y-m');
        $monthStart = $targetMonthDate->startOfMonth()->toDateString();
        $carryForwardDate = $targetMonthDate->subDay()->toDateString();
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
                'selected_month_price.price_id as selected_month_price_id',
                'selected_month_price.price as selected_month_price_value',
                'selected_month_price.effective_date as selected_month_effective_date',
                'selected_month_price.expired_date as selected_month_expired_date',
                'carry_price.price_id as carry_forward_price_id',
                'carry_price.price as carry_forward_price_value',
                'carry_price.effective_date as carry_forward_effective_date',
                'carry_price.expired_date as carry_forward_expired_date',
            ])
            ->leftJoin('material_category', 'material.category_id', '=', 'material_category.category_id')
            ->leftJoinSub($this->monthStartPriceReferenceQuery($monthStart), 'selected_month_ref', function ($join) {
                $join->on('material.material_id', '=', 'selected_month_ref.material_id');
            })
            ->leftJoin('material_price as selected_month_price', 'selected_month_price.price_id', '=', 'selected_month_ref.price_id')
            ->leftJoinSub($this->currentPriceReferenceQuery($carryForwardDate), 'carry_price_ref', function ($join) {
                $join->on('material.material_id', '=', 'carry_price_ref.material_id');
            })
            ->leftJoin('material_price as carry_price', 'carry_price.price_id', '=', 'carry_price_ref.price_id')
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
            'published' => $materials->whereNotNull('selected_month_price_id')->count(),
            'carry_forward' => $materials->whereNull('selected_month_price_id')->whereNotNull('carry_forward_price_value')->count(),
            'missing' => $materials->whereNull('selected_month_price_id')->whereNull('carry_forward_price_value')->count(),
            'active' => $materials->where('is_active', true)->count(),
        ];
        $priceEditorRows = $viewDataFactory->buildRows($materials);
        $targetMonthLabel = $this->thaiMonthLabel($targetMonthDate);
        $dirtySummary = 'ยังไม่ได้ปรับราคาจากชุดตั้งต้น';

        return view('material_prices.index', compact(
            'materials',
            'categories',
            'materialOptions',
            'summary',
            'priceEditorRows',
            'dirtySummary',
            'q',
            'categoryId',
            'materialId',
            'minimumTargetMonth',
            'targetMonth',
            'targetMonthLabel',
            'monthStart'
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

    public function store(StoreMaterialPriceRequest $request, MaterialPriceService $materialPriceService)
    {
        $data = $request->payload();
        $createdBy = $this->currentUserId($request);

        $materialPriceService->create($data, $createdBy);

        $material = Material::query()->find($data['material_id']);
        $materialName = $material?->material_name ?? ('#'.$data['material_id']);

        ActivityLogger::forCurrentUser(
            'material_prices',
            "เพิ่มราคาวัสดุ {$materialName} = ".number_format((float) $data['price'], 2)
            ." บาท มีผล {$data['effective_date']}"
        );

        return redirect()->route('material-prices.index', ['material_id' => $data['material_id']])
            ->with('success', 'เพิ่มราคาวัสดุเรียบร้อย');
    }

    public function bulkUpdate(BulkUpdateMaterialPricesRequest $request, MaterialPriceService $materialPriceService)
    {
        $rows = $request->rows();
        $targetMonth = $request->targetMonth();

        if ($rows === []) {
            return redirect()
                ->route('material-prices.index', $request->editorFilters())
                ->withErrors('ไม่พบข้อมูลราคาสำหรับบันทึก');
        }

        ['updates' => $updates, 'creates' => $creates] = $materialPriceService->planMonthlyPublish($targetMonth, $rows);

        if ($updates === [] && $creates === []) {
            return redirect()
                ->route('material-prices.index', $request->editorFilters())
                ->with('success', 'เดือนนี้มีชุดราคาอยู่แล้ว หรือยังไม่มีรายการที่พร้อมเผยแพร่');
        }

        $createdBy = $this->currentUserId($request);
        $materialPriceService->applyMonthlyPublish($updates, $creates, $createdBy);
        $touchedMaterials = $materialPriceService->touchedMaterialNames($updates, $creates);
        $targetMonthLabel = $this->thaiMonthLabel(CarbonImmutable::createFromFormat('Y-m', $targetMonth)->startOfMonth());

        $preview = $touchedMaterials->take(5)->implode(', ');
        $remaining = $touchedMaterials->count() - min(5, $touchedMaterials->count());

        ActivityLogger::forCurrentUser(
            'material_prices',
            'เผยแพร่ชุดราคาวัสดุเดือน '.$targetMonthLabel.' '.$touchedMaterials->count().' รายการ'
            .($preview !== '' ? ' ('.$preview.($remaining > 0 ? ' และอีก '.$remaining.' รายการ' : '').')' : '')
        );

        return redirect()
            ->route('material-prices.index', $request->editorFilters())
            ->with('success', 'เผยแพร่ชุดราคาประจำเดือน '.$targetMonthLabel.' เรียบร้อย '.$touchedMaterials->count().' รายการ');
    }

    public function destroy(MaterialPrice $material_price)
    {
        $materialId = $material_price->material_id;
        $materialName = $material_price->material?->material_name ?? ('#'.$materialId);
        $price = (float) $material_price->price;
        $effectiveDate = $material_price->effective_date?->format('Y-m-d') ?? '-';

        $material_price->delete();

        ActivityLogger::forCurrentUser(
            'material_prices',
            "ลบราคาวัสดุ {$materialName} = ".number_format($price, 2)." บาท มีผล {$effectiveDate}"
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
        return DB::table('material_price as current_price')
            ->select('current_price.material_id', 'current_price.price_id')
            ->where('current_price.effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('current_price.expired_date')
                    ->orWhere('current_price.expired_date', '>=', $today);
            })
            ->whereNotExists(function ($query) use ($today) {
                $query->select(DB::raw(1))
                    ->from('material_price as newer_price')
                    ->whereColumn('newer_price.material_id', 'current_price.material_id')
                    ->where('newer_price.effective_date', '<=', $today)
                    ->where(function ($subQuery) use ($today) {
                        $subQuery->whereNull('newer_price.expired_date')
                            ->orWhere('newer_price.expired_date', '>=', $today);
                    })
                    ->where(function ($subQuery) {
                        $subQuery->whereColumn('newer_price.effective_date', '>', 'current_price.effective_date')
                            ->orWhere(function ($tieQuery) {
                                $tieQuery->whereColumn('newer_price.effective_date', 'current_price.effective_date')
                                    ->whereColumn('newer_price.price_id', '>', 'current_price.price_id');
                            });
                    });
            });
    }

    private function monthStartPriceReferenceQuery(string $monthStart)
    {
        return DB::table('material_price as month_price')
            ->select('month_price.material_id', 'month_price.price_id')
            ->whereDate('month_price.effective_date', $monthStart)
            ->whereNotExists(function ($query) use ($monthStart) {
                $query->select(DB::raw(1))
                    ->from('material_price as newer_month_price')
                    ->whereColumn('newer_month_price.material_id', 'month_price.material_id')
                    ->whereDate('newer_month_price.effective_date', $monthStart)
                    ->whereColumn('newer_month_price.price_id', '>', 'month_price.price_id');
            });
    }

    private function currentUserId(Request $request): int
    {
        $userId = $request->user()?->user_id;

        if (! $userId) {
            abort(403, 'ไม่พบบัญชีผู้ใช้ที่เข้าสู่ระบบ');
        }

        return (int) $userId;
    }

    private function resolveTargetMonth(string $targetMonth): CarbonImmutable
    {
        $currentMonth = CarbonImmutable::now()->startOfMonth();

        if (preg_match('/^\d{4}-\d{2}$/', $targetMonth) === 1) {
            try {
                $requestedMonth = CarbonImmutable::createFromFormat('Y-m', $targetMonth)->startOfMonth();

                return $requestedMonth->lessThan($currentMonth) ? $currentMonth : $requestedMonth;
            } catch (\Throwable) {
                // fall through to current month
            }
        }

        return $currentMonth;
    }

    private function thaiMonthLabel(CarbonImmutable $month): string
    {
        return match ((int) $month->format('n')) {
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            default => 'ธันวาคม',
        }.' '.((int) $month->format('Y') + 543);
    }
}

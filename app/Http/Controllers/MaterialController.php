<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $q = $request->string('q')->toString();
        $categoryId = $request->input('category_id');
        $sort = $request->string('sort')->toString();
        $dir = strtolower($request->string('dir')->toString() ?: 'asc');
        $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc';

        $sortMap = [
            'id' => 'material.material_id',
            'name' => 'material.material_name',
            'category' => 'material_category.category_name',
            'unit' => 'material.unit',
            'status' => 'material.is_active',
            'price' => 'current_price.price',
        ];
        $sortColumn = $sortMap[$sort] ?? 'material.material_name';

        $materialsQuery = Material::query()
            ->select('material.*')
            ->selectRaw('current_price.price as current_price')
            ->selectRaw('current_price.effective_date as current_price_effective_date')
            ->with('category')
            ->leftJoinSub($this->currentPriceReferenceQuery($today), 'current_price_ref', function ($join) {
                $join->on('material.material_id', '=', 'current_price_ref.material_id');
            })
            ->leftJoin('material_price as current_price', 'current_price.price_id', '=', 'current_price_ref.price_id')
            ->when($q, fn($qb) => $qb->where('material.material_name', 'like', "%{$q}%"))
            ->when($categoryId, fn($qb) => $qb->where('material.category_id', $categoryId))
            ->when($sort === 'category', function ($qb) {
                $qb->leftJoin('material_category', 'material.category_id', '=', 'material_category.category_id');
            });

        if ($sort === 'price') {
            $materialsQuery
                ->orderByRaw('current_price.price IS NULL')
                ->orderBy('current_price.price', $dir)
                ->orderBy('material.material_name');
        } else {
            $materialsQuery
                ->orderBy($sortColumn, $dir)
                ->orderBy('material.material_id');
        }

        $materials = $materialsQuery
            ->paginate(15)
            ->withQueryString();

        $categories = MaterialCategory::orderBy('category_name')->get();

        return view('materials.index', compact('materials', 'categories', 'q', 'categoryId', 'sort', 'dir'));
    }

    public function create()
    {
        $categories = MaterialCategory::orderBy('category_name')->get();
        return view('materials.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedMaterialData($request);

        $material = Material::create($data);

        ActivityLogger::forCurrentUser(
            'materials',
            "เพิ่มวัสดุ {$material->material_name}"
        );

        return redirect()->route('materials.index')
            ->with('success', 'เพิ่มวัสดุเรียบร้อย');
    }

    public function edit(Material $material)
    {
        $categories = MaterialCategory::orderBy('category_name')->get();
        return view('materials.edit', compact('material', 'categories'));
    }

    public function update(Request $request, Material $material)
    {
        $data = $this->validatedMaterialData($request);

        $material->update($data);

        ActivityLogger::forCurrentUser(
            'materials',
            "แก้ไขวัสดุ {$material->material_name}"
        );

        return redirect()->route('materials.index')
            ->with('success', 'แก้ไขวัสดุเรียบร้อย');
    }

    public function destroy(Material $material)
    {
        // กันลบถ้ามี transaction_detail หรือมีราคา
        if ($material->transactionDetails()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีรายการธุรกรรมที่อ้างถึงวัสดุนี้');
        }

        if ($material->prices()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีราคาวัสดุที่อ้างถึงวัสดุนี้');
        }

        $materialName = $material->material_name;

        $material->delete();

        ActivityLogger::forCurrentUser(
            'materials',
            "ลบวัสดุ {$materialName}"
        );

        return redirect()->route('materials.index')
            ->with('success', 'ลบวัสดุเรียบร้อย');
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

    private function validatedMaterialData(Request $request): array
    {
        $data = $request->validate([
            'category_id'   => ['required', 'integer', 'exists:material_category,category_id'],
            'material_name' => ['required', 'string', 'max:150'],
            'unit'          => ['required', 'string', 'max:20'],
            'description'   => ['nullable', 'string', 'max:255'],
            'is_active'     => ['required', 'boolean'],
        ]);

        $data['description'] = $data['description'] ?? '';

        return $data;
    }
}

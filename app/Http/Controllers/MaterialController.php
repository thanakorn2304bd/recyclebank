<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
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
        ];
        $sortColumn = $sortMap[$sort] ?? 'material.material_name';

        $materials = Material::query()
            ->select('material.*')
            ->with('category')
            ->when($q, fn($qb) => $qb->where('material.material_name', 'like', "%{$q}%"))
            ->when($categoryId, fn($qb) => $qb->where('category_id', $categoryId))
            ->when($sort === 'category', function ($qb) {
                $qb->leftJoin('material_category', 'material.category_id', '=', 'material_category.category_id');
            })
            ->orderBy($sortColumn, $dir)
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
        $data = $request->validate([
            'category_id'   => ['required','integer','exists:material_category,category_id'],
            'material_name' => ['required','string','max:150'],
            'unit'          => ['required','string','max:20'],
            'description'   => ['nullable','string','max:255'],
            'is_active'     => ['required','boolean'],
        ]);

        Material::create($data);

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
        $data = $request->validate([
            'category_id'   => ['required','integer','exists:material_category,category_id'],
            'material_name' => ['required','string','max:150'],
            'unit'          => ['required','string','max:20'],
            'description'   => ['nullable','string','max:255'],
            'is_active'     => ['required','boolean'],
        ]);

        $material->update($data);

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

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'ลบวัสดุเรียบร้อย');
    }
}

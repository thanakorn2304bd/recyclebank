<?php

namespace App\Http\Controllers;

use App\Models\MaterialCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{
    public function index()
    {
        $categories = MaterialCategory::query()
            ->orderBy('category_name')
            ->paginate(15);

        return view('material_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('material_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_name' => ['required','string','max:100'],
        ]);

        MaterialCategory::create($data);

        ActivityLogger::forCurrentUser(
            'material_categories',
            "เพิ่มหมวดวัสดุ {$data['category_name']}"
        );

        return redirect()->route('material-categories.index')
            ->with('success', 'เพิ่มหมวดวัสดุเรียบร้อย');
    }

    public function edit(MaterialCategory $material_category)
    {
        return view('material_categories.edit', ['category' => $material_category]);
    }

    public function update(Request $request, MaterialCategory $material_category)
    {
        $data = $request->validate([
            'category_name' => ['required','string','max:100'],
        ]);

        $material_category->update($data);

        ActivityLogger::forCurrentUser(
            'material_categories',
            "แก้ไขหมวดวัสดุเป็น {$material_category->category_name}"
        );

        return redirect()->route('material-categories.index')
            ->with('success', 'แก้ไขหมวดวัสดุเรียบร้อย');
    }

    public function destroy(MaterialCategory $material_category)
    {
        // กันลบถ้ามี material ผูกอยู่
        if ($material_category->materials()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีวัสดุผูกอยู่ในหมวดนี้');
        }

        $categoryName = $material_category->category_name;

        $material_category->delete();

        ActivityLogger::forCurrentUser(
            'material_categories',
            "ลบหมวดวัสดุ {$categoryName}"
        );

        return redirect()->route('material-categories.index')
            ->with('success', 'ลบหมวดวัสดุเรียบร้อย');
    }
}

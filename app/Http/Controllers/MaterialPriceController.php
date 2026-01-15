<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialPriceController extends Controller
{
    // list ราคา (รวมวัสดุ)
    public function index(Request $request)
    {
        $materialId = $request->input('material_id');

        $materials = Material::orderBy('material_name')->get();

        $prices = MaterialPrice::query()
            ->with('material')
            ->when($materialId, fn($qb) => $qb->where('material_id', $materialId))
            ->orderByDesc('effective_date')
            ->orderByDesc('price_id')
            ->paginate(20)
            ->withQueryString();

        return view('material_prices.index', compact('prices', 'materials', 'materialId'));
    }

    // หน้าเพิ่มราคา
    public function create(Request $request)
    {
        $materials = Material::where('is_active', 1)->orderBy('material_name')->get();
        $materialId = $request->input('material_id');
        return view('material_prices.create', compact('materials', 'materialId'));
    }

    // เพิ่มราคาใหม่
    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id'    => ['required','integer','exists:material,material_id'],
            'price'          => ['required','numeric','min:0'],
            'effective_date' => ['required','date'],
            'expired_date'   => ['nullable','date','after_or_equal:effective_date'],
        ]);

        // created_by: ถ้ายังไม่มีระบบ login user_account ให้ fallback
        $createdBy = session('user_id') ?? 1;

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

        return redirect()->route('material-prices.index', ['material_id' => $data['material_id']])
            ->with('success', 'เพิ่มราคาวัสดุเรียบร้อย');
    }

    // ลบราคา (เฉพาะกรณีใส่ผิด)
    public function destroy(MaterialPrice $material_price)
    {
        $materialId = $material_price->material_id;
        $material_price->delete();

        return redirect()->route('material-prices.index', ['material_id' => $materialId])
            ->with('success', 'ลบราคาวัสดุเรียบร้อย');
    }

    // ดูราคาของวัสดุ 1 ชนิด (history)
    public function materialPrices(Material $material)
    {
        $prices = MaterialPrice::where('material_id', $material->material_id)
            ->orderByDesc('effective_date')
            ->paginate(20);

        return view('material_prices.material_prices', compact('material', 'prices'));
    }
}

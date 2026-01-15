<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function create()
    {
        $households = Household::orderBy('account_no')->get(['household_id','account_no','contact_person','total_balance']);
        $materials  = Material::where('is_active', 1)->orderBy('material_name')->get(['material_id','material_name','unit']);

        // ส่ง “ราคาปัจจุบัน” ให้หน้าเว็บใช้แสดง/คำนวณ
        // ดึงแบบ 1 query ต่อ material ไม่ดี -> ใช้ subquery เลือกราคาที่ effective_date ล่าสุด
        $today = now()->toDateString();

        $currentPrices = MaterialPrice::query()
            ->select('material_id', 'price')
            ->where(function($q) use ($today){
                $q->whereNull('expired_date')->orWhere('expired_date', '>=', $today);
            })
            ->where('effective_date', '<=', $today)
            ->orderByDesc('effective_date')
            ->get()
            ->groupBy('material_id')
            ->map(fn($rows) => (float) $rows->first()->price)
            ->toArray();

        return view('deposits.create', compact('households', 'materials', 'currentPrices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'household_id' => ['required','integer','exists:household,household_id'],
            'transaction_date' => ['required','date'],

            // รายการวัสดุ
            'items' => ['required','array','min:1'],
            'items.*.material_id' => ['required','integer','exists:material,material_id'],
            'items.*.weight' => ['required','numeric','min:0.01'],
        ]);

        $householdId = (int)$data['household_id'];
        $date = $data['transaction_date'];

        // recorded_by: ยังไม่ผูก login user_account ก็ใช้ fallback เป็น user_id ที่มีอยู่จริง
        $recordedBy = session('user_id') ?? DB::table('user_account')->min('user_id') ?? 1;

        $today = now()->toDateString();

        return DB::transaction(function () use ($householdId, $date, $recordedBy, $data, $today) {

            $totalWeight = 0.0;
            $totalAmount = 0.0;

            // สร้าง transaction ก่อน (ยังไม่รู้ total จนกว่าจะคำนวณ)
            $tx = Transaction::create([
                'household_id' => $householdId,
                'transaction_date' => $date,
                'transaction_type' => 'deposit',
                'total_weight' => 0.00,
                'total_amount' => 0.00,
                'recorded_by' => $recordedBy,
            ]);

            foreach ($data['items'] as $item) {
                $materialId = (int)$item['material_id'];
                $weight = (float)$item['weight'];

                // หา “ราคาปัจจุบัน” ของ material นี้
                $priceRow = MaterialPrice::query()
                    ->where('material_id', $materialId)
                    ->where('effective_date', '<=', $today)
                    ->where(function($q) use ($today){
                        $q->whereNull('expired_date')->orWhere('expired_date', '>=', $today);
                    })
                    ->orderByDesc('effective_date')
                    ->first();

                $ppu = (float)($priceRow?->price ?? 0);

                // ถ้าไม่มีราคา -> กันพัง (ไม่ให้ฝากได้)
                if ($ppu <= 0) {
                    throw new \RuntimeException("ไม่พบราคาปัจจุบันสำหรับ material_id={$materialId}");
                }

                $amount = round($weight * $ppu, 2);

                TransactionDetail::create([
                    'transaction_id' => $tx->transaction_id,
                    'material_id' => $materialId,
                    'weight' => $weight,
                    'price_per_unit' => $ppu,
                    'amount' => $amount,
                ]);

                $totalWeight += $weight;
                $totalAmount += $amount;
            }

            // อัปเดตยอดรวมใน transaction
            $tx->update([
                'total_weight' => round($totalWeight, 2),
                'total_amount' => round($totalAmount, 2),
            ]);

            // อัปเดตยอดเงินคงเหลือ household
            DB::table('household')
                ->where('household_id', $householdId)
                ->update(['total_balance' => DB::raw('total_balance + ' . round($totalAmount, 2))]);

            return redirect()
                ->route('deposits.create')
                ->with('success', "บันทึกฝาก/รับซื้อสำเร็จ (ยอดรวม " . number_format($totalAmount, 2) . ")");
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $staffUserId = DB::table('user_account')->where('username', 'staff')->value('user_id');
        $householdId = DB::table('household')->where('account_no', '2026010123')->value('household_id');

        // หา "ราคาล่าสุด" ของวัสดุ (effective_date ล่าสุด และ expired_date เป็น null หรือยังไม่หมดอายุ)
        $getCurrentPrice = function(int $materialId): float {
            $row = DB::table('material_price')
                ->where('material_id', $materialId)
                ->where(function($q) {
                    $q->whereNull('expired_date')
                      ->orWhere('expired_date', '>=', now()->toDateString());
                })
                ->orderByDesc('effective_date')
                ->first();

            return (float) ($row?->price ?? 0);
        };

        // ====== 1) DEPOSIT ตัวอย่าง (ฝากจากการขายวัสดุ) ======
        $items = [
            ['name' => 'ขวด PET', 'weight' => 2.50],
            ['name' => 'กระดาษลัง', 'weight' => 5.00],
        ];

        $details = [];
        $totalWeight = 0.0;
        $totalAmount = 0.0;

        foreach ($items as $it) {
            $materialId = DB::table('material')->where('material_name', $it['name'])->value('material_id');
            $ppu = $getCurrentPrice($materialId);
            $amt = round($it['weight'] * $ppu, 2);

            $details[] = [
                'material_id' => $materialId,
                'weight' => $it['weight'],
                'price_per_unit' => $ppu,
                'amount' => $amt,
            ];

            $totalWeight += $it['weight'];
            $totalAmount += $amt;
        }

        $depositId = DB::table('transaction')->insertGetId([
            'household_id' => $householdId,
            'transaction_date' => now()->toDateString(),
            'transaction_type' => 'deposit',
            'total_weight' => round($totalWeight, 2),
            'total_amount' => round($totalAmount, 2),
            'recorded_by' => $staffUserId,
        ]);

        foreach ($details as $d) {
            DB::table('transaction_detail')->insert([
                'transaction_id' => $depositId,
                'material_id' => $d['material_id'],
                'weight' => $d['weight'],
                'price_per_unit' => $d['price_per_unit'],
                'amount' => $d['amount'],
            ]);
        }

        // อัปเดตยอดคงเหลือใน household (เพราะ schema ไม่มี trigger)
        DB::table('household')->where('household_id', $householdId)->update([
            'total_balance' => DB::raw('total_balance + ' . round($totalAmount, 2))
        ]);

                // ====== 2) WITHDRAW ตัวอย่าง (ถอนเงิน) ======
        $withdrawAmount = 50.00;

        DB::table('transaction')->insert([
            'household_id' => $householdId,
            'transaction_date' => now()->toDateString(),
            'transaction_type' => 'withdraw',
            'total_weight' => 0.00,            // ✅ แก้ตรงนี้ (ห้าม null)
            'total_amount' => $withdrawAmount,
            'recorded_by' => $staffUserId,
        ]);

        DB::table('household')->where('household_id', $householdId)->update([
            'total_balance' => DB::raw('total_balance - ' . $withdrawAmount)
        ]);

    }
}

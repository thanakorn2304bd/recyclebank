<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaterialPriceSeeder extends Seeder
{
    public function run(): void
    {
        $staffUserId = DB::table('user_account')->where('username', 'staff')->value('user_id');

        $monthStart = now()->startOfMonth()->toDateString();

        $prices = [
            ['material_name' => 'ขวด PET', 'price' => 12.00],
            ['material_name' => 'กระดาษลัง', 'price' => 3.50],
            ['material_name' => 'กระป๋องอลูมิเนียม', 'price' => 35.00],
        ];

        foreach ($prices as $p) {
            $materialId = DB::table('material')->where('material_name', $p['material_name'])->value('material_id');

            $row = [
                'material_id' => $materialId,
                'price' => $p['price'],
                'effective_date' => $monthStart,
                'expired_date' => null,
                'created_by' => $staffUserId,
            ];

            // เอกสารกำหนด material_price มี created_at (DATETIME) :contentReference[oaicite:2]{index=2}
            if (Schema::hasColumn('material_price', 'created_at')) $row['created_at'] = now();

            // กันซ้ำแบบง่าย: 1 วัสดุ 1 ราคา ณ เดือนเริ่มต้น
            DB::table('material_price')->updateOrInsert(
                ['material_id' => $materialId, 'effective_date' => $monthStart],
                $row
            );
        }
    }
}

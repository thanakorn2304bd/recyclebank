<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarchMaterialPriceSeeder extends Seeder
{
    public function run(): void
    {
        $staffUserId = DB::table('user_account')->where('username', 'staff')->value('user_id');

        $monthStart = '2026-03-01';

        $prices = [
            ['material_name' => 'ทองแดง', 'price' => 158.00],
            ['material_name' => 'ทองแดงเผา', 'price' => 137.00],
            ['material_name' => 'หม้อน้ำไส้ทองแดง', 'price' => 75.00],
            ['material_name' => 'หม้อน้ำไส้ทองเหลือง', 'price' => 102.00],
            ['material_name' => 'ทองเหลือง', 'price' => 116.00],
            ['material_name' => 'ตะกั่ว', 'price' => 29.40],
            ['material_name' => 'สแตนเลสจริง', 'price' => 23.80],
            ['material_name' => 'สแตนเลสปลอม', 'price' => 6.65],
            ['material_name' => 'อลูมิเนียมหนา', 'price' => 34.30],
            ['material_name' => 'อลูมิเนียมเครื่อง', 'price' => 37.80],
            ['material_name' => 'อลูมิเนียมบาง', 'price' => 32.90],
            ['material_name' => 'อลูมิเนียมโค้ก', 'price' => 37.80],
            ['material_name' => 'อลูมิเนียมฉากสวย', 'price' => 39.90],
            ['material_name' => 'อลูมิเนียมล้อแม็ก', 'price' => 48.30],
            ['material_name' => 'อลูมิเนียมสายไฟ', 'price' => 45.50],
            ['material_name' => 'หม้อน้ำมิเนียม', 'price' => 26.25],
            ['material_name' => 'อลูมิเนียมกระทะ', 'price' => 26.60],
            ['material_name' => 'อลูมิเนียมก้นกระทะ', 'price' => 23.80],
            ['material_name' => 'อลูมิเนียมฝาจุกแกะ', 'price' => 23.80],
            ['material_name' => 'อลูมิเนียมมู่หลี่', 'price' => 21.00],
            ['material_name' => 'แบตเตอรี่ใหญ่', 'price' => 17.50],
            ['material_name' => 'แบตเตอรี่เล็ก', 'price' => 15.40],
            ['material_name' => 'แบตเตอรี่ดำ', 'price' => 13.30],
            ['material_name' => 'ลังกระดาษ', 'price' => 3.00],
            ['material_name' => 'เศษกระดาษ', 'price' => 1.82],
            ['material_name' => 'กระดาษขาวดำ', 'price' => 3.43],
            ['material_name' => 'เหล็ก', 'price' => 5.18],
            ['material_name' => 'สังกะสี', 'price' => 1.82],
            ['material_name' => 'กระป๋อง', 'price' => 3.43],
            ['material_name' => 'ท่อฟ้าไม่ติดเหล็ก,สี', 'price' => 3.08],
            ['material_name' => 'ข้อต่อไม่มีบอลวอล์ว', 'price' => 1.68],
            ['material_name' => 'ท่อเทา', 'price' => 0.63],
            ['material_name' => 'ท่อเหลือง', 'price' => 0.49],
            ['material_name' => 'ท่อขาว', 'price' => 1.05],
            ['material_name' => 'สายยางอ่อน', 'price' => 1.68],
            ['material_name' => 'สายยางเขียว', 'price' => 0.63],
            ['material_name' => 'รองเท้าบู๊ท', 'price' => 1.68],
            ['material_name' => 'รองเท้ายาง', 'price' => 1.68],
            ['material_name' => 'สายน้ำหยด', 'price' => 2.73],
            ['material_name' => 'เปลือกสายไฟอ่อน', 'price' => 0.35],
            ['material_name' => 'แผนป้ายอคิลิค', 'price' => 8.40],
            ['material_name' => 'แก้วขาว', 'price' => 0.91],
            ['material_name' => 'แก้วแดง', 'price' => 0.70],
            ['material_name' => 'แก้วเขียว', 'price' => 1.26],
            ['material_name' => 'เพทใส', 'price' => 6.30],
            ['material_name' => 'เพทสกีน', 'price' => 2.80],
            ['material_name' => 'พลาสติกรวม', 'price' => 3.08],
            ['material_name' => 'พลาสติกดำ', 'price' => 1.68],
            ['material_name' => 'เพทสี', 'price' => 0.35],
            ['material_name' => 'เพทฟ้า', 'price' => 1.33],
            ['material_name' => 'เพทเขียว', 'price' => 1.33],
            ['material_name' => 'เทียนไข', 'price' => 5.60],
            ['material_name' => 'แผ่นซีดี', 'price' => 12.60],
            ['material_name' => 'ที่นอนนุ๋น', 'price' => 3.50],
            ['material_name' => 'น้ำมันพืชเก่า', 'price' => 10.50],
            ['material_name' => 'น้ำมันเครื่องเก่า', 'price' => 2.10],
            ['material_name' => 'ซากมือถือไม่มีแบต(ครบ)', 'price' => 61.60],
            ['material_name' => 'ซากโน๊ตบุ๊ค(ครบ)', 'price' => 35.00],
            ['material_name' => 'ตูดทีวี', 'price' => 35.00],
            ['material_name' => 'จอคอม(จอแบน)', 'price' => 24.50],
            ['material_name' => 'ทีวี', 'price' => 49.00],
            ['material_name' => 'พัดลม(ครบ)', 'price' => 14.00],
            ['material_name' => 'พัดลม(ไม่ครบ)', 'price' => 7.00],
            ['material_name' => 'เครื่องซักผ้า(ครบ)', 'price' => 105.00],
            ['material_name' => 'ตู้เย็น(ครบ)', 'price' => 175.00],
        ];

        foreach ($prices as $p) {
            $materialId = DB::table('material')->where('material_name', $p['material_name'])->value('material_id');
            if (! $materialId) {
                throw new \RuntimeException("ไม่พบวัสดุสำหรับตั้งราคา: {$p['material_name']}");
            }

            $row = [
                'material_id' => $materialId,
                'price' => $p['price'],
                'effective_date' => $monthStart,
                'expired_date' => null,
                'created_by' => $staffUserId,
            ];

            if (Schema::hasColumn('material_price', 'created_at')) {
                $row['created_at'] = now();
            }

            DB::table('material_price')->updateOrInsert(
                ['material_id' => $materialId, 'effective_date' => $monthStart],
                $row
            );
        }
    }
}

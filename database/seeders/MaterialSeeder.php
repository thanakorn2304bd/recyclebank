<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = DB::table('material_category')
            ->pluck('category_id', 'category_name')
            ->toArray();

        $items = [
            ['category' => 'โลหะ', 'material_name' => 'ทองแดง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'ทองแดงเผา', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'หม้อน้ำไส้ทองแดง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'หม้อน้ำไส้ทองเหลือง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'ทองเหลือง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'ตะกั่ว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'สแตนเลสจริง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'โลหะ', 'material_name' => 'สแตนเลสปลอม', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมหนา', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมเครื่อง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมบาง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมโค้ก', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมฉากสวย', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมล้อแม็ก', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมสายไฟ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'หม้อน้ำมิเนียม', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมกระทะ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมก้นกระทะ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมฝาจุกแกะ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อลูมิเนียม', 'material_name' => 'อลูมิเนียมมู่หลี่', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'แบตเตอรี่', 'material_name' => 'แบตเตอรี่ใหญ่', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'แบตเตอรี่', 'material_name' => 'แบตเตอรี่เล็ก', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'แบตเตอรี่', 'material_name' => 'แบตเตอรี่ดำ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'กระดาษ', 'material_name' => 'ลังกระดาษ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'กระดาษ', 'material_name' => 'เศษกระดาษ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'กระดาษ', 'material_name' => 'กระดาษขาวดำ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เหล็ก', 'material_name' => 'เหล็ก', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เหล็ก', 'material_name' => 'สังกะสี', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เหล็ก', 'material_name' => 'กระป๋อง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'ท่อฟ้าไม่ติดเหล็ก,สี', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'ข้อต่อไม่มีบอลวอล์ว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'ท่อเทา', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'ท่อเหลือง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'ท่อขาว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'สายยางอ่อน', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'สายยางเขียว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'รองเท้าบู๊ท', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'รองเท้ายาง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เสื่อน้ำมัน', 'unit' => 'kg', 'description' => 'งดรับ', 'is_active' => 0],
            ['category' => 'พลาสติก', 'material_name' => 'สายน้ำหยด', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เปลือกสายไฟอ่อน', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'แผนป้ายอคิลิค', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'แก้ว', 'material_name' => 'แก้วขาว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'แก้ว', 'material_name' => 'แก้วแดง', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'แก้ว', 'material_name' => 'แก้วเขียว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เพทใส', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เพทสกีน', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'พลาสติกรวม', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'พลาสติกดำ', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เพทสี', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เพทฟ้า', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'พลาสติก', 'material_name' => 'เพทเขียว', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เบ็ดเตล็ด', 'material_name' => 'เทียนไข', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เบ็ดเตล็ด', 'material_name' => 'แผ่นซีดี', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เบ็ดเตล็ด', 'material_name' => 'ที่นอนนุ๋น', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เบ็ดเตล็ด', 'material_name' => 'น้ำมันพืชเก่า', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'เบ็ดเตล็ด', 'material_name' => 'น้ำมันเครื่องเก่า', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'ซากมือถือไม่มีแบต(ครบ)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'ซากโน๊ตบุ๊ค(ครบ)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'ตูดทีวี', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'จอคอม(จอแบน)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'ทีวี', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'พัดลม(ครบ)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'พัดลม(ไม่ครบ)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'เครื่องซักผ้า(ครบ)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
            ['category' => 'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า', 'material_name' => 'ตู้เย็น(ครบ)', 'unit' => 'kg', 'description' => '', 'is_active' => 1],
        ];

        foreach ($items as $item) {
            $categoryId = $categoryMap[$item['category']] ?? null;
            if (! $categoryId) {
                throw new \RuntimeException("ไม่พบหมวดวัสดุ: {$item['category']}");
            }

            $m = [
                'category_id' => $categoryId,
                'material_name' => $item['material_name'],
                'unit' => $item['unit'],
                'description' => $item['description'],
                'is_active' => $item['is_active'],
            ];

            if (Schema::hasColumn('material', 'created_at')) {
                $m['created_at'] = now();
            }
            if (Schema::hasColumn('material', 'updated_at')) {
                $m['updated_at'] = now();
            }

            DB::table('material')->updateOrInsert(
                ['material_name' => $m['material_name']],
                $m
            );
        }
    }
}

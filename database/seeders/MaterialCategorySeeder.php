<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'โลหะ',
            'อลูมิเนียม',
            'แบตเตอรี่',
            'กระดาษ',
            'เหล็ก',
            'พลาสติก',
            'แก้ว',
            'เบ็ดเตล็ด',
            'อิเล็กทรอนิก/เครื่องใช้ไฟฟ้า',
        ];

        foreach ($categories as $name) {
            DB::table('material_category')->updateOrInsert(
                ['category_name' => $name],
                ['category_name' => $name]
            );
        }
    }
}

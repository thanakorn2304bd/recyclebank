<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $plasticId = DB::table('material_category')->where('category_name', 'พลาสติก')->value('category_id');
        $paperId   = DB::table('material_category')->where('category_name', 'กระดาษ')->value('category_id');
        $metalId   = DB::table('material_category')->where('category_name', 'โลหะ')->value('category_id');

        $items = [
            ['category_id' => $plasticId, 'material_name' => 'ขวด PET', 'unit' => 'kg', 'description' => 'ขวดน้ำพลาสติก', 'is_active' => 1],
            ['category_id' => $paperId,   'material_name' => 'กระดาษลัง', 'unit' => 'kg', 'description' => 'กล่อง/ลัง', 'is_active' => 1],
            ['category_id' => $metalId,   'material_name' => 'กระป๋องอลูมิเนียม', 'unit' => 'kg', 'description' => 'กระป๋องเครื่องดื่ม', 'is_active' => 1],
        ];

        foreach ($items as $m) {
            if (Schema::hasColumn('material', 'created_at')) $m['created_at'] = now();
            if (Schema::hasColumn('material', 'updated_at')) $m['updated_at'] = now();

            DB::table('material')->updateOrInsert(
                ['material_name' => $m['material_name']],
                $m
            );
        }
    }
}

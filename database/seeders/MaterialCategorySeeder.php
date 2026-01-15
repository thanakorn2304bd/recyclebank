<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('material_category')->updateOrInsert(['category_name' => 'พลาสติก'], ['category_name' => 'พลาสติก']);
        DB::table('material_category')->updateOrInsert(['category_name' => 'กระดาษ'], ['category_name' => 'กระดาษ']);
        DB::table('material_category')->updateOrInsert(['category_name' => 'โลหะ'], ['category_name' => 'โลหะ']);
    }
}

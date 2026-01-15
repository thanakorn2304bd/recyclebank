<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('community')->upsert([
            ['community_id' => '01', 'community_name' => 'ชุมชนหนองไผ่'],
            ['community_id' => '02', 'community_name' => 'ชุมชนตัวอย่าง 2'],
        ], ['community_id'], ['community_name']);
    }
}

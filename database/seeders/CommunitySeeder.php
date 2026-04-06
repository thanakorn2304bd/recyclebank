<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('community')->upsert([
            ['community_id' => '01', 'community_name' => 'ไผ่สีสุก'],
            ['community_id' => '02', 'community_name' => 'ไผ่ตง'],
            ['community_id' => '03', 'community_name' => 'ไผ่หอม'],
            ['community_id' => '04', 'community_name' => 'ไผ่น้ำเต้า'],
            ['community_id' => '05', 'community_name' => 'ไผ่แก้ว'],
            ['community_id' => '06', 'community_name' => 'ไผ่หลัก'],
            ['community_id' => '07', 'community_name' => 'ไผ่เพ็ชร'],
            ['community_id' => '08', 'community_name' => 'ไผ่งาม'],
            ['community_id' => '09', 'community_name' => 'ไผ่เลี้ยง'],
            ['community_id' => '10', 'community_name' => 'ไผ่ป่า'],
            ['community_id' => '11', 'community_name' => 'ไผ่ปล้อง'],
        ], ['community_id'], ['community_name']);
    }
}

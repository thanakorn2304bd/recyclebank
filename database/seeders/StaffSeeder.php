<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // ใช้ insertGetId เพื่อเอา staff_id ไปผูก user_account
        // ถ้าตาราง staff ของคุณ "ไม่ใช่ auto_increment" ให้ใส่ staff_id เอง
        DB::table('staff')->updateOrInsert(
            ['full_name' => 'เจ้าหน้าที่ทดสอบ'],
            ['phone' => '0800000000', 'position' => 'เจ้าหน้าที่']
        );

        DB::table('staff')->updateOrInsert(
            ['full_name' => 'แอดมินระบบ'],
            ['phone' => '0899999999', 'position' => 'ผู้ดูแลระบบ']
        );
    }
}

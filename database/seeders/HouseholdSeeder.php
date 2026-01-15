<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HouseholdSeeder extends Seeder
{
    public function run(): void
    {
        $staffUserId = DB::table('user_account')->where('username', 'staff')->value('user_id');

        // account_no 10 หลัก: 4 หลักปีสมัคร + 2 หลักชุมชน + 4 หลักท้ายบ้านเลขที่ (ตัวอย่าง)
        // ตามเอกสาร household.account_no เป็น UNIQUE :contentReference[oaicite:1]{index=1}
        $data = [
            'account_no' => '2026010123', // 2026 + 01 + 0123
            'house_no' => '123',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0811111111',
            'contact_person' => 'นายตัวอย่าง ครัวเรือน',
            'register_date' => now()->toDateString(),
            'active_status' => 'active',
            'accumulated_months' => 0,
            'total_balance' => 0.00,
            'created_by' => $staffUserId,
        ];

        // ถ้าคุณเปิด timestamps ใน household แบบ Level B ก็จะมี created_at/updated_at
        if (Schema::hasColumn('household', 'created_at')) $data['created_at'] = now();
        if (Schema::hasColumn('household', 'updated_at')) $data['updated_at'] = now();

        DB::table('household')->updateOrInsert(['account_no' => '2026010123'], $data);
    }
}

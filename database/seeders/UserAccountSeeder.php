<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserAccountSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $staffId = DB::table('staff')->where('full_name', 'เจ้าหน้าที่ทดสอบ')->value('staff_id');
        $adminStaffId = DB::table('staff')->where('full_name', 'แอดมินระบบ')->value('staff_id');

        // admin
        $admin = [
            'username' => 'admin',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'staff_id' => $adminStaffId,
            'is_active' => 1,
        ];
        if (Schema::hasColumn('user_account', 'created_at')) $admin['created_at'] = $now;
        if (Schema::hasColumn('user_account', 'last_login')) $admin['last_login'] = null;

        DB::table('user_account')->updateOrInsert(['username' => 'admin'], $admin);

        // staff
        $staff = [
            'username' => 'staff',
            'password' => Hash::make('staff1234'),
            'role' => 'staff',
            'staff_id' => $staffId,
            'is_active' => 1,
        ];
        if (Schema::hasColumn('user_account', 'created_at')) $staff['created_at'] = $now;
        if (Schema::hasColumn('user_account', 'last_login')) $staff['last_login'] = null;

        DB::table('user_account')->updateOrInsert(['username' => 'staff'], $staff);
    }
}

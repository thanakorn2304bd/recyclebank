<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MemberUserAccountSeeder extends Seeder
{
    public function run(): void
    {
        $householdId = DB::table('household')
            ->where('account_no', '2026010123')
            ->value('household_id');

        if (! $householdId) {
            return;
        }

        $now = now();

        $member = [
            'username' => 'member',
            'password' => Hash::make('member1234'),
            'role' => 'member',
            'household_id' => $householdId,
            'is_active' => 1,
        ];

        if (Schema::hasColumn('user_account', 'created_at')) {
            $member['created_at'] = $now;
        }

        if (Schema::hasColumn('user_account', 'last_login')) {
            $member['last_login'] = null;
        }

        DB::table('user_account')->updateOrInsert(['username' => 'member'], $member);
    }
}


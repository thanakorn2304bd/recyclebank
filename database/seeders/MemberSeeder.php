<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $householdId = DB::table('household')->where('account_no', '2026010123')->value('household_id');

        $rows = [
            [
                'household_id' => $householdId,
                'full_name' => 'นายตัวอย่าง ครัวเรือน',
                'id_card' => '1111111111111',
                'is_head' => 1,
                'relation' => 'หัวหน้า',
            ],
            [
                'household_id' => $householdId,
                'full_name' => 'นางตัวอย่าง ครัวเรือน',
                'id_card' => '2222222222222',
                'is_head' => 0,
                'relation' => 'ภรรยา',
            ],
        ];

        foreach ($rows as $r) {
            if (Schema::hasColumn('member', 'created_at')) $r['created_at'] = now();
            if (Schema::hasColumn('member', 'updated_at')) $r['updated_at'] = now();

            DB::table('member')->updateOrInsert(
                ['household_id' => $r['household_id'], 'id_card' => $r['id_card']],
                $r
            );
        }
    }
}

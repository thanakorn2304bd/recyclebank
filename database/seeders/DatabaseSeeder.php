<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CommunitySeeder::class,
            StaffSeeder::class,
            UserAccountSeeder::class,
            HouseholdSeeder::class,
            MemberSeeder::class,

            MaterialCategorySeeder::class,
            MaterialSeeder::class,
            MaterialPriceSeeder::class,

            TransactionSeeder::class,
        ]);
    }
}

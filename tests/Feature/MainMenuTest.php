<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MainMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_public_material_catalog_with_the_current_price(): void
    {
        $staffUser = $this->createStaffUser('main-menu-price-owner');
        $categoryId = DB::table('material_category')->insertGetId([
            'category_name' => 'พลาสติก',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'ขวดใส',
            'unit' => 'kg',
            'description' => 'ขวดพลาสติกใส',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('material_price')->insert([
            [
                'material_id' => $materialId,
                'price' => 3.00,
                'effective_date' => now()->subDays(10)->toDateString(),
                'expired_date' => now()->subDay()->toDateString(),
                'created_by' => $staffUser->user_id,
                'created_at' => now()->subDays(10),
            ],
            [
                'material_id' => $materialId,
                'price' => 5.25,
                'effective_date' => now()->subDay()->toDateString(),
                'expired_date' => null,
                'created_by' => $staffUser->user_id,
                'created_at' => now()->subDay(),
            ],
            [
                'material_id' => $materialId,
                'price' => 9.99,
                'effective_date' => now()->addDay()->toDateString(),
                'expired_date' => null,
                'created_by' => $staffUser->user_id,
                'created_at' => now(),
            ],
        ]);

        $this->get(route('main-menu'))
            ->assertOk()
            ->assertSeeText('ดูราคารับซื้อวันนี้ พร้อมสมัครสมาชิกครัวเรือนออนไลน์')
            ->assertSeeText('รายการวัสดุและราคา')
            ->assertSeeText('พลาสติก')
            ->assertSeeText('ขวดใส')
            ->assertSeeText('5.25')
            ->assertDontSeeText('9.99');
    }

    public function test_staff_sees_the_privileged_dashboard_on_main_menu(): void
    {
        $staffUser = $this->createStaffUser('main-menu-staff');

        $this->actingAs($staffUser)
            ->get(route('main-menu'))
            ->assertOk()
            ->assertSeeText('เมนูหลักสำหรับการทำงานประจำวัน')
            ->assertSeeText('เมนูจัดการระบบ')
            ->assertSeeText('รับซื้อวัสดุรีไซเคิ้ล')
            ->assertDontSeeText('PDPA / Compliance')
            ->assertDontSeeText('ดูราคารับซื้อวันนี้ พร้อมสมัครสมาชิกครัวเรือนออนไลน์')
            ->assertDontSeeText('รายการวัสดุและราคา');
    }

    public function test_guest_can_view_material_prices_for_a_selected_past_month(): void
    {
        Carbon::setTestNow('2026-04-15 09:30:00');

        try {
            $staffUser = $this->createStaffUser('main-menu-history-owner');
            $categoryId = DB::table('material_category')->insertGetId([
                'category_name' => 'โลหะ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $materialId = DB::table('material')->insertGetId([
                'category_id' => $categoryId,
                'material_name' => 'ทองแดงคัดพิเศษ',
                'unit' => 'kg',
                'description' => 'ทดสอบราคาย้อนหลัง',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('material_price')->insert([
                [
                    'material_id' => $materialId,
                    'price' => 110.00,
                    'effective_date' => '2026-02-01',
                    'expired_date' => '2026-02-28',
                    'created_by' => $staffUser->user_id,
                    'created_at' => '2026-02-01 08:00:00',
                ],
                [
                    'material_id' => $materialId,
                    'price' => 125.50,
                    'effective_date' => '2026-03-01',
                    'expired_date' => '2026-03-31',
                    'created_by' => $staffUser->user_id,
                    'created_at' => '2026-03-01 08:00:00',
                ],
                [
                    'material_id' => $materialId,
                    'price' => 140.75,
                    'effective_date' => '2026-04-01',
                    'expired_date' => null,
                    'created_by' => $staffUser->user_id,
                    'created_at' => '2026-04-01 08:00:00',
                ],
            ]);

            $this->get(route('main-menu', ['price_month' => '2026-03']))
                ->assertOk()
                ->assertSeeText('มีนาคม 2569')
                ->assertSeeText('31/03/2026')
                ->assertSeeText('125.50')
                ->assertDontSeeText('140.75');
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createStaffUser(string $username): UserAccount
    {
        return UserAccount::create([
            'username' => $username,
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaterialPriceStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-04-07 10:00:00'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    public function test_staff_can_store_material_price_and_close_previous_open_ended_price(): void
    {
        ['staff' => $staffUser, 'materialId' => $materialId, 'previousPriceId' => $previousPriceId] = $this->seedPriceFixtures();

        $response = $this->actingAs($staffUser)->post(route('material-prices.store'), [
            'material_id' => $materialId,
            'price' => '14.75',
            'effective_date' => '2026-04-20',
            'expired_date' => '',
        ]);

        $response
            ->assertRedirect(route('material-prices.index', ['material_id' => $materialId]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('material_price', [
            'material_id' => $materialId,
            'price' => '14.75',
            'effective_date' => '2026-04-20',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
        ]);

        $this->assertDatabaseHas('material_price', [
            'price_id' => $previousPriceId,
            'expired_date' => '2026-04-19',
        ]);

        $this->assertDatabaseHas('log_activity', [
            'user_id' => $staffUser->user_id,
            'module' => 'material_prices',
        ]);
    }

    public function test_store_rejects_overlapping_material_price_periods(): void
    {
        ['staff' => $staffUser, 'materialId' => $materialId] = $this->seedPriceFixtures();

        $this->actingAs($staffUser)->post(route('material-prices.store'), [
            'material_id' => $materialId,
            'price' => '14.75',
            'effective_date' => '2026-04-10',
            'expired_date' => '2026-04-25',
        ])->assertSessionHasErrors('effective_date');
    }

    public function test_store_rejects_effective_dates_before_current_month(): void
    {
        ['staff' => $staffUser, 'materialId' => $materialId] = $this->seedPriceFixtures();

        $this->actingAs($staffUser)->post(route('material-prices.store'), [
            'material_id' => $materialId,
            'price' => '14.75',
            'effective_date' => '2026-03-20',
            'expired_date' => '',
        ])->assertSessionHasErrors('effective_date');
    }

    private function seedPriceFixtures(): array
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-store',
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $categoryId = DB::table('material_category')->insertGetId([
            'category_name' => 'โลหะ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'ทองเหลือง',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $previousPriceId = DB::table('material_price')->insertGetId([
            'material_id' => $materialId,
            'price' => 12.00,
            'effective_date' => '2026-04-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        return [
            'staff' => $staffUser,
            'materialId' => $materialId,
            'previousPriceId' => $previousPriceId,
        ];
    }
}

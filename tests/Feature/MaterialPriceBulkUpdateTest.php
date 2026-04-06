<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaterialPriceBulkUpdateTest extends TestCase
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

    public function test_staff_can_view_bulk_price_editor_page(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-page',
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
            'material_name' => 'ทองแดง',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('material_price')->insert([
            'material_id' => $materialId,
            'price' => 10.00,
            'effective_date' => '2026-04-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $this->actingAs($staffUser)
            ->get(route('material-prices.index', ['target_month' => '2026-04']))
            ->assertOk()
            ->assertSee('จัดชุดราคาวัสดุรายเดือน')
            ->assertSee('มีชุดราคาเดือนนี้แล้ว')
            ->assertSee('ทองแดง');
    }

    public function test_staff_can_publish_monthly_material_prices_using_carried_values(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-bulk',
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

        $firstMaterialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'ทองแดง',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondMaterialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'อลูมิเนียม',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstPreviousPriceId = DB::table('material_price')->insertGetId([
            'material_id' => $firstMaterialId,
            'price' => 10.00,
            'effective_date' => '2026-03-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $secondPreviousPriceId = DB::table('material_price')->insertGetId([
            'material_id' => $secondMaterialId,
            'price' => 8.75,
            'effective_date' => '2026-03-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($staffUser)->post(route('material-prices.bulk-update'), [
            'target_month' => '2026-04',
            'rows' => [
                $firstMaterialId => [
                    'price_id' => '',
                    'price' => '12.50',
                ],
                $secondMaterialId => [
                    'price_id' => '',
                    'price' => '8.75',
                ],
            ],
        ]);

        $response
            ->assertRedirect(route('material-prices.index', ['target_month' => '2026-04']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('material_price', [
            'material_id' => $firstMaterialId,
            'price_id' => $firstPreviousPriceId,
            'price' => '10.00',
            'effective_date' => '2026-03-01',
            'expired_date' => '2026-03-31',
        ]);

        $this->assertDatabaseHas('material_price', [
            'material_id' => $secondMaterialId,
            'price_id' => $secondPreviousPriceId,
            'price' => '8.75',
            'effective_date' => '2026-03-01',
            'expired_date' => '2026-03-31',
        ]);

        $this->assertDatabaseHas('material_price', [
            'material_id' => $firstMaterialId,
            'price' => '12.50',
            'effective_date' => '2026-04-01',
            'expired_date' => null,
        ]);

        $this->assertDatabaseHas('material_price', [
            'material_id' => $secondMaterialId,
            'price' => '8.75',
            'effective_date' => '2026-04-01',
            'expired_date' => null,
        ]);

        $this->assertDatabaseHas('log_activity', [
            'user_id' => $staffUser->user_id,
            'module' => 'material_prices',
        ]);
    }

    public function test_create_route_redirects_to_bulk_editor(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-redirect',
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $this->actingAs($staffUser)
            ->get(route('material-prices.create', ['material_id' => 5]))
            ->assertRedirect(route('material-prices.index', ['material_id' => 5]));
    }

    public function test_bulk_publish_updates_existing_selected_month_price_without_creating_duplicates(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-overlap',
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $categoryId = DB::table('material_category')->insertGetId([
            'category_name' => 'พลาสติก',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'ขวดน้ำพลาสติก',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('material_price')->insertGetId([
            'material_id' => $materialId,
            'price' => 4.00,
            'effective_date' => '2026-02-01',
            'expired_date' => '2026-02-28',
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $currentMonthPriceId = DB::table('material_price')->insertGetId([
            'material_id' => $materialId,
            'price' => 4.50,
            'effective_date' => '2026-04-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $this->actingAs($staffUser)->post(route('material-prices.bulk-update'), [
            'target_month' => '2026-04',
            'rows' => [
                $materialId => [
                    'price_id' => $currentMonthPriceId,
                    'price' => '4.25',
                ],
            ],
        ])->assertRedirect(route('material-prices.index', ['target_month' => '2026-04']));

        $this->assertDatabaseHas('material_price', [
            'price_id' => $currentMonthPriceId,
            'material_id' => $materialId,
            'price' => '4.25',
            'effective_date' => '2026-04-01',
        ]);

        $this->assertEquals(2, DB::table('material_price')->where('material_id', $materialId)->count());
    }

    public function test_bulk_publish_rejects_past_month_requests(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-past',
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $categoryId = DB::table('material_category')->insertGetId([
            'category_name' => 'กระดาษ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'หนังสือพิมพ์',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('material_price')->insert([
            'material_id' => $materialId,
            'price' => 3.10,
            'effective_date' => '2026-03-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $this->actingAs($staffUser)->from(route('material-prices.index', ['target_month' => '2026-04']))
            ->post(route('material-prices.bulk-update'), [
                'target_month' => '2026-03',
                'rows' => [
                    $materialId => [
                        'price_id' => '',
                        'price' => '3.25',
                    ],
                ],
            ])->assertRedirect(route('material-prices.index', ['target_month' => '2026-04']))
            ->assertSessionHasErrors('target_month');

        $this->assertDatabaseMissing('material_price', [
            'material_id' => $materialId,
            'price' => '3.25',
            'effective_date' => '2026-03-01',
        ]);
    }

    public function test_bulk_price_editor_clamps_past_month_selection_to_current_month(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-price-clamp',
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $this->actingAs($staffUser)
            ->get(route('material-prices.index', ['target_month' => '2026-03']))
            ->assertOk()
            ->assertSee('value="2026-04"', false)
            ->assertSee('min="2026-04"', false);
    }
}

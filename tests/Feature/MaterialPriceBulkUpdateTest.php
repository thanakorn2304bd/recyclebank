<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaterialPriceBulkUpdateTest extends TestCase
{
    use RefreshDatabase;

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
            'effective_date' => '2026-03-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $this->actingAs($staffUser)
            ->get(route('material-prices.index'))
            ->assertOk()
            ->assertSee('แก้ไขราคาวัสดุ')
            ->assertSee('มีราคาปัจจุบัน')
            ->assertSee('ทองแดง');
    }

    public function test_staff_can_bulk_update_material_prices(): void
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

        $existingPriceId = DB::table('material_price')->insertGetId([
            'material_id' => $firstMaterialId,
            'price' => 10.00,
            'effective_date' => '2026-03-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($staffUser)->post(route('material-prices.bulk-update'), [
            'rows' => [
                $firstMaterialId => [
                    'price_id' => $existingPriceId,
                    'price' => '12.50',
                    'effective_date' => '2026-03-10',
                    'expired_date' => '',
                ],
                $secondMaterialId => [
                    'price_id' => '',
                    'price' => '8.75',
                    'effective_date' => '2026-03-11',
                    'expired_date' => '',
                ],
            ],
        ]);

        $response
            ->assertRedirect(route('material-prices.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('material_price', [
            'price_id' => $existingPriceId,
            'material_id' => $firstMaterialId,
            'price' => '12.50',
            'effective_date' => '2026-03-10',
        ]);

        $this->assertDatabaseHas('material_price', [
            'material_id' => $secondMaterialId,
            'price' => '8.75',
            'effective_date' => '2026-03-11',
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

    public function test_bulk_update_rejects_overlapping_price_periods(): void
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

        $currentPriceId = DB::table('material_price')->insertGetId([
            'material_id' => $materialId,
            'price' => 4.00,
            'effective_date' => '2026-03-01',
            'expired_date' => '2026-03-19',
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        DB::table('material_price')->insert([
            'material_id' => $materialId,
            'price' => 4.50,
            'effective_date' => '2026-03-20',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $this->actingAs($staffUser)->post(route('material-prices.bulk-update'), [
            'rows' => [
                $materialId => [
                    'price_id' => $currentPriceId,
                    'price' => '4.25',
                    'effective_date' => '2026-03-15',
                    'expired_date' => '',
                ],
            ],
        ])->assertSessionHasErrors("rows.$materialId.effective_date");
    }
}

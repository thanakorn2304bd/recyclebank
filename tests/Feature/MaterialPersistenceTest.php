<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaterialPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_store_material_with_blank_description(): void
    {
        $staffUser = $this->createStaffUser('staff-material-store');
        $categoryId = $this->createCategory();

        $response = $this->actingAs($staffUser)->post(route('materials.store'), [
            'category_id' => $categoryId,
            'material_name' => 'กระดาษรวม',
            'unit' => 'ชิ้น',
            'description' => '',
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect(route('materials.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('material', [
            'material_name' => 'กระดาษรวม',
            'unit' => 'ชิ้น',
            'description' => '',
        ]);
    }

    public function test_staff_can_update_material_unit_with_blank_description(): void
    {
        $staffUser = $this->createStaffUser('staff-material-update');
        $categoryId = $this->createCategory();

        $materialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'กระดาษขาวดำ',
            'unit' => 'kg',
            'description' => '',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($staffUser)->put(route('materials.update', $materialId), [
            'category_id' => $categoryId,
            'material_name' => 'กระดาษขาวดำ',
            'unit' => 'ชิ้น',
            'description' => '',
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect(route('materials.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('material', [
            'material_id' => $materialId,
            'unit' => 'ชิ้น',
            'description' => '',
        ]);
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

    private function createCategory(): int
    {
        return DB::table('material_category')->insertGetId([
            'category_name' => 'กระดาษ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

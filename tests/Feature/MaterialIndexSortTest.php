<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaterialIndexSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_index_shows_current_price(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-material-price',
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
            'price' => 125.50,
            'effective_date' => now()->toDateString(),
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        $this->actingAs($staffUser)
            ->get(route('materials.index'))
            ->assertOk()
            ->assertSee('125.50')
            ->assertSee('บาท/kg');
    }

    public function test_material_index_sorts_across_all_pages_by_id_descending(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-material-sort',
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

        foreach (range(1, 16) as $index) {
            DB::table('material')->insert([
                'category_id' => $categoryId,
                'material_name' => 'วัสดุ ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'unit' => 'kg',
                'description' => '',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($staffUser)->get(route('materials.index', [
            'sort' => 'id',
            'dir' => 'desc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['วัสดุ 16', 'วัสดุ 15', 'วัสดุ 14'])
            ->assertDontSee('วัสดุ 01');
    }
}

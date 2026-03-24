<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HouseholdPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_household_index_with_summary_metrics(): void
    {
        ['staff' => $staffUser, 'activeHousehold' => $activeHousehold, 'pendingHousehold' => $pendingHousehold] = $this->seedHouseholdFixtures();

        $this->actingAs($staffUser)
            ->get(route('households.index'))
            ->assertOk()
            ->assertSee($activeHousehold->account_no)
            ->assertSee($pendingHousehold->account_no)
            ->assertViewHas('activeCount', 1)
            ->assertViewHas('pendingCount', 1)
            ->assertViewHas('inactiveCount', 0)
            ->assertViewHas('isPrivileged', true);
    }

    public function test_member_is_redirected_to_their_household_detail_from_index(): void
    {
        ['member' => $memberUser, 'activeHousehold' => $activeHousehold] = $this->seedHouseholdFixtures();

        $this->actingAs($memberUser)
            ->get(route('households.index'))
            ->assertRedirect(route('households.show', $activeHousehold));
    }

    public function test_staff_can_view_household_create_page(): void
    {
        ['staff' => $staffUser] = $this->seedHouseholdFixtures();

        $this->actingAs($staffUser)
            ->get(route('households.create'))
            ->assertOk()
            ->assertSee('ขั้นตอน 1 จาก 2')
            ->assertSee('เพิ่มสมาชิกครัวเรือน');
    }

    public function test_staff_can_view_household_detail_page(): void
    {
        ['staff' => $staffUser, 'activeHousehold' => $activeHousehold, 'member' => $memberUser] = $this->seedHouseholdFixtures();

        $this->actingAs($staffUser)
            ->get(route('households.show', $activeHousehold))
            ->assertOk()
            ->assertSee($activeHousehold->account_no)
            ->assertSee($memberUser->username)
            ->assertViewHas('isPrivileged', true);
    }

    public function test_staff_can_view_pending_household_credentials_page(): void
    {
        ['staff' => $staffUser, 'pendingHousehold' => $pendingHousehold] = $this->seedHouseholdFixtures();

        $this->actingAs($staffUser)
            ->get(route('households.credentials.create', $pendingHousehold))
            ->assertOk()
            ->assertSee('สร้างบัญชีเข้าใช้งานครัวเรือน')
            ->assertSee('รออนุมัติ')
            ->assertSee('บัญชียังอยู่ในสถานะรออนุมัติ');
    }

    private function seedHouseholdFixtures(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่ทดสอบ',
            'phone' => '0812345678',
            'position' => 'เจ้าหน้าที่',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-household-pages',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $activeHousehold = Household::create([
            'account_no' => 'ACC2000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000001',
            'contact_person' => 'สมาชิกหนึ่ง',
            'register_date' => '2026-01-01',
            'active_status' => 'active',
            'accumulated_months' => 2,
            'total_balance' => 150,
            'created_by' => $staffUser->user_id,
        ]);

        $pendingHousehold = Household::create([
            'account_no' => 'ACC2000002',
            'house_no' => '12',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000002',
            'contact_person' => 'สมาชิกสอง',
            'register_date' => '2026-01-02',
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => $staffUser->user_id,
        ]);

        $memberUser = UserAccount::create([
            'username' => $activeHousehold->account_no,
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $activeHousehold->household_id,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        DB::table('member')->insert([
            'household_id' => $activeHousehold->household_id,
            'full_name' => 'สมาชิกหนึ่ง',
            'id_card' => '1101700203451',
            'relation' => 'หัวหน้าครัวเรือน',
            'is_head' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'staff' => $staffUser,
            'member' => $memberUser,
            'activeHousehold' => $activeHousehold,
            'pendingHousehold' => $pendingHousehold,
        ];
    }
}

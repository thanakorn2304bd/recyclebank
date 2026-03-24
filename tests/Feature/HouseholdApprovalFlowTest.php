<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HouseholdApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_credentials_for_pending_household_keeps_account_inactive_until_approved(): void
    {
        ['staff' => $staffUser, 'household' => $household] = $this->seedPendingHouseholdFixtures();

        $this->actingAs($staffUser)->post(route('households.credentials.store', $household), [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('households.show', $household));

        $this->assertDatabaseHas('user_account', [
            'username' => $household->account_no,
            'household_id' => $household->household_id,
            'role' => 'member',
            'is_active' => 0,
        ]);

        $this->actingAs($staffUser)->put(route('households.update', $household), [
            'account_no' => $household->account_no,
            'house_no' => $household->house_no,
            'village_no' => $household->village_no,
            'community_id' => $household->community_id,
            'phone' => $household->phone,
            'contact_person' => $household->contact_person,
            'register_date' => $household->register_date->toDateString(),
            'active_status' => 'active',
            'accumulated_months' => $household->accumulated_months,
        ])->assertRedirect(route('households.index'));

        $this->assertDatabaseHas('user_account', [
            'username' => $household->account_no,
            'household_id' => $household->household_id,
            'role' => 'member',
            'is_active' => 1,
        ]);
    }

    private function seedPendingHouseholdFixtures(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-household-approval',
            'password' => 'password123',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $household = Household::create([
            'account_no' => '2026010011',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0812345678',
            'contact_person' => 'สมหญิง ใจดี',
            'register_date' => '2026-03-01',
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => null,
        ]);

        return [
            'staff' => $staffUser,
            'household' => $household,
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HouseholdStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_store_household_with_generated_account_and_members(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-household-store',
            'password' => 'password123',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staffUser)->post(route('households.store'), [
            'house_no' => '11/2',
            'village_no' => '3',
            'community_id' => '01',
            'phone' => '0812345678',
            'contact_person' => 'สมหญิง ใจดี',
            'register_date' => '2026-03-01',
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'members' => [
                [
                    'full_name' => 'สมหญิง ใจดี',
                    'id_card' => '1234567890123',
                    'relation' => 'หัวหน้าครัวเรือน',
                    'is_head' => '1',
                ],
                [
                    'full_name' => 'สมชาย ใจดี',
                    'id_card' => '1234567890124',
                    'relation' => 'คู่สมรส',
                    'is_head' => '0',
                ],
            ],
        ]);

        $expectedAccountNo = now()->format('Y').'010112';

        $household = Household::query()
            ->where('account_no', $expectedAccountNo)
            ->firstOrFail();

        $response->assertRedirect(route('households.credentials.create', $household));

        $this->assertDatabaseHas('household', [
            'household_id' => $household->household_id,
            'account_no' => $expectedAccountNo,
            'contact_person' => 'สมหญิง ใจดี',
            'community_id' => '01',
            'created_by' => $staffUser->user_id,
        ]);

        $this->assertDatabaseCount('member', 2);
        $this->assertDatabaseHas('member', [
            'household_id' => $household->household_id,
            'full_name' => 'สมหญิง ใจดี',
            'is_head' => 1,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\LogActivity;
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

        $this->actingAs($staffUser)->patch(route('households.review', $household), [
            'status' => 'active',
            'review_notes' => 'ตรวจสอบข้อมูลและเอกสารครบถ้วนแล้ว',
        ])->assertRedirect(route('households.show', $household));

        $this->assertDatabaseHas('user_account', [
            'username' => $household->account_no,
            'household_id' => $household->household_id,
            'role' => 'member',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('household', [
            'household_id' => $household->household_id,
            'active_status' => 'active',
            'reviewed_by' => $staffUser->user_id,
            'review_notes' => 'ตรวจสอบข้อมูลและเอกสารครบถ้วนแล้ว',
        ]);

        $auditLog = LogActivity::query()
            ->where('module', 'households.review')
            ->where('entity_type', 'household')
            ->where('entity_id', (string) $household->household_id)
            ->firstOrFail();

        $this->assertNotNull($auditLog->ip_address);
        $this->assertSame('pending', $auditLog->before_values['active_status'] ?? null);
        $this->assertSame('active', $auditLog->after_values['active_status'] ?? null);
        $this->assertSame(
            'ตรวจสอบข้อมูลและเอกสารครบถ้วนแล้ว',
            $auditLog->metadata['review_notes'] ?? null
        );
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

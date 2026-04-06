<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComplianceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_notice_page_is_hidden_when_pdpa_feature_is_disabled(): void
    {
        $this->get(route('privacy-notice.show'))
            ->assertNotFound();
    }

    public function test_staff_cannot_access_data_subject_request_routes_when_pdpa_feature_is_disabled(): void
    {
        $staffUser = $this->createStaffUser('staff-hidden-dsar');

        $this->actingAs($staffUser)
            ->get(route('compliance.dsars.index'))
            ->assertNotFound();

        $this->actingAs($staffUser)
            ->get(route('compliance.dsars.create'))
            ->assertNotFound();

        $this->actingAs($staffUser)
            ->post(route('compliance.dsars.store'), [])
            ->assertNotFound();
    }

    public function test_staff_cannot_access_security_incident_routes_when_pdpa_feature_is_disabled(): void
    {
        $staffUser = $this->createStaffUser('staff-hidden-incident');

        $this->actingAs($staffUser)
            ->get(route('compliance.incidents.index'))
            ->assertNotFound();

        $this->actingAs($staffUser)
            ->get(route('compliance.incidents.create'))
            ->assertNotFound();

        $this->actingAs($staffUser)
            ->post(route('compliance.incidents.store'), [])
            ->assertNotFound();
    }

    private function createStaffUser(string $username): UserAccount
    {
        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่กำกับดูแล',
            'phone' => '0800000002',
            'position' => 'เจ้าหน้าที่',
        ]);

        return UserAccount::create([
            'username' => $username,
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);
    }
}

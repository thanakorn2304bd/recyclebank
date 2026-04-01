<?php

namespace Tests\Feature;

use App\Models\DataSubjectRequest;
use App\Models\SecurityIncident;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComplianceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_notice_page_can_be_rendered(): void
    {
        $this->get(route('privacy-notice.show'))
            ->assertOk()
            ->assertSee('ประกาศคุ้มครองข้อมูลส่วนบุคคล')
            ->assertSee('เวอร์ชัน 1.0');
    }

    public function test_staff_can_create_and_update_data_subject_request(): void
    {
        ['staff' => $staffUser, 'householdId' => $householdId, 'admin' => $adminUser] = $this->seedComplianceFixtures();

        $response = $this->actingAs($staffUser)->post(route('compliance.dsars.store'), [
            'household_id' => $householdId,
            'requester_name' => 'สมหญิง เจ้าของข้อมูล',
            'requester_contact' => '0811111111',
            'request_type' => 'access',
            'status' => 'submitted',
            'submitted_at' => '2026-04-01',
            'due_at' => '2026-04-30',
            'assigned_to' => $adminUser->user_id,
            'request_details' => 'ขอสำเนาข้อมูลส่วนบุคคลที่ระบบจัดเก็บไว้ทั้งหมด',
            'resolution_notes' => '',
        ]);

        $dsar = DataSubjectRequest::query()->firstOrFail();

        $response
            ->assertRedirect(route('compliance.dsars.show', $dsar))
            ->assertSessionHas('success');

        $this->assertStringStartsWith('DSAR-', $dsar->request_no);
        $this->assertSame('submitted', $dsar->status);
        $this->assertSame($adminUser->user_id, $dsar->assigned_to);

        $this->actingAs($staffUser)->put(route('compliance.dsars.update', $dsar), [
            'household_id' => $householdId,
            'requester_name' => 'สมหญิง เจ้าของข้อมูล',
            'requester_contact' => '0811111111',
            'request_type' => 'access',
            'status' => 'completed',
            'submitted_at' => '2026-04-01',
            'due_at' => '2026-04-25',
            'assigned_to' => $adminUser->user_id,
            'request_details' => 'ขอสำเนาข้อมูลส่วนบุคคลที่ระบบจัดเก็บไว้ทั้งหมด',
            'resolution_notes' => 'จัดเตรียมข้อมูลและส่งมอบให้เจ้าของข้อมูลแล้ว',
        ])->assertRedirect(route('compliance.dsars.show', $dsar));

        $dsar->refresh();

        $this->assertSame('completed', $dsar->status);
        $this->assertNotNull($dsar->closed_at);
        $this->assertSame('จัดเตรียมข้อมูลและส่งมอบให้เจ้าของข้อมูลแล้ว', $dsar->resolution_notes);

        $this->assertDatabaseHas('log_activity', [
            'module' => 'data_subject_requests',
            'entity_type' => 'data_subject_request',
            'entity_id' => (string) $dsar->data_subject_request_id,
        ]);
    }

    public function test_staff_can_create_and_update_security_incident(): void
    {
        ['staff' => $staffUser, 'admin' => $adminUser] = $this->seedComplianceFixtures();

        $response = $this->actingAs($staffUser)->post(route('compliance.incidents.store'), [
            'severity' => 'high',
            'status' => 'open',
            'assigned_to' => $adminUser->user_id,
            'occurred_at' => '2026-04-01 09:00',
            'detected_at' => '2026-04-01 10:00',
            'summary' => 'พบการเข้าถึงหน้ารายละเอียดครัวเรือนผิดปกติ',
            'affected_scope' => 'ข้อมูลสมาชิกครัวเรือนชุมชน 01',
            'affected_record_count' => 3,
            'notification_required' => '1',
            'authority_notified_at' => '',
            'subject_notified_at' => '',
            'impact_details' => 'มีความเสี่ยงเห็นข้อมูลส่วนบุคคลเกินความจำเป็น',
            'containment_actions' => 'ปิดการเข้าถึงชั่วคราวและตรวจสอบ log เพิ่มเติม',
        ]);

        $incident = SecurityIncident::query()->firstOrFail();

        $response
            ->assertRedirect(route('compliance.incidents.show', $incident))
            ->assertSessionHas('success');

        $this->assertStringStartsWith('INC-', $incident->incident_no);
        $this->assertSame('high', $incident->severity);
        $this->assertTrue((bool) $incident->notification_required);

        $this->actingAs($staffUser)->put(route('compliance.incidents.update', $incident), [
            'severity' => 'high',
            'status' => 'closed',
            'assigned_to' => $adminUser->user_id,
            'occurred_at' => '2026-04-01 09:00',
            'detected_at' => '2026-04-01 10:00',
            'summary' => 'พบการเข้าถึงหน้ารายละเอียดครัวเรือนผิดปกติ',
            'affected_scope' => 'ข้อมูลสมาชิกครัวเรือนชุมชน 01',
            'affected_record_count' => 3,
            'notification_required' => '1',
            'authority_notified_at' => '2026-04-01 13:00',
            'subject_notified_at' => '2026-04-01 14:00',
            'impact_details' => 'มีความเสี่ยงเห็นข้อมูลส่วนบุคคลเกินความจำเป็น',
            'containment_actions' => 'ปิดการเข้าถึงชั่วคราว ตรวจสอบ และแก้ไขสิทธิ์เรียบร้อย',
        ])->assertRedirect(route('compliance.incidents.show', $incident));

        $incident->refresh();

        $this->assertSame('closed', $incident->status);
        $this->assertNotNull($incident->closed_at);
        $this->assertNotNull($incident->authority_notified_at);
        $this->assertNotNull($incident->subject_notified_at);

        $this->assertDatabaseHas('log_activity', [
            'module' => 'security_incidents',
            'entity_type' => 'security_incident',
            'entity_id' => (string) $incident->security_incident_id,
        ]);
    }

    private function seedComplianceFixtures(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $adminStaffId = DB::table('staff')->insertGetId([
            'full_name' => 'แอดมินกำกับดูแล',
            'phone' => '0800000001',
            'position' => 'ผู้ดูแลระบบ',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่กำกับดูแล',
            'phone' => '0800000002',
            'position' => 'เจ้าหน้าที่',
        ]);

        $adminUser = UserAccount::create([
            'username' => 'admin-compliance',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'household_id' => null,
            'staff_id' => $adminStaffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-compliance',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $householdId = DB::table('household')->insertGetId([
            'account_no' => 'ACC4000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000001',
            'contact_person' => 'สมหญิง ตัวอย่าง',
            'register_date' => '2026-01-05',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => 100.00,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'admin' => $adminUser,
            'staff' => $staffUser,
            'householdId' => $householdId,
        ];
    }
}

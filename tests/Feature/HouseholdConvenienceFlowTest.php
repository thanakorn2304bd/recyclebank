<?php

namespace Tests\Feature;

use App\Models\DataSubjectRequest;
use App\Models\Household;
use App\Models\SecurityIncident;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HouseholdConvenienceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_quick_search_household_by_member_name(): void
    {
        ['staff' => $staffUser, 'household' => $household] = $this->seedFixtures();

        $this->actingAs($staffUser)
            ->getJson(route('households.quick-search', ['q' => 'สมชาย']))
            ->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('matches.0.account_no', $household->account_no)
            ->assertJsonPath('matches.0.house_no', $household->house_no);
    }

    public function test_staff_can_update_household_and_members_in_one_screen(): void
    {
        ['staff' => $staffUser, 'household' => $household, 'memberUser' => $memberUser] = $this->seedFixtures();

        $response = $this->actingAs($staffUser)->put(route('households.update', $household), [
            'account_no' => 'ACC9000002',
            'house_no' => '77/1',
            'village_no' => '5',
            'community_id' => '01',
            'phone' => '0899999999',
            'contact_person' => 'สมหญิง ปรับปรุง',
            'register_date' => '2026-02-15',
            'accumulated_months' => 8,
            'members' => [
                [
                    'full_name' => 'สมหญิง ปรับปรุง',
                    'id_card' => '1101700203451',
                    'relation' => 'หัวหน้าครัวเรือน',
                    'is_head' => '1',
                ],
                [
                    'full_name' => 'สมศรี ปรับปรุง',
                    'id_card' => '1101700209999',
                    'relation' => 'บุตร',
                    'is_head' => '0',
                ],
            ],
        ]);

        $response->assertRedirect(route('households.show', $household));

        $this->assertDatabaseHas('household', [
            'household_id' => $household->household_id,
            'account_no' => 'ACC9000002',
            'contact_person' => 'สมหญิง ปรับปรุง',
            'house_no' => '77/1',
            'accumulated_months' => 8,
        ]);

        $this->assertDatabaseHas('user_account', [
            'user_id' => $memberUser->user_id,
            'username' => 'ACC9000002',
        ]);

        $this->assertDatabaseCount('member', 2);
        $this->assertDatabaseHas('member', [
            'household_id' => $household->household_id,
            'full_name' => 'สมศรี ปรับปรุง',
            'relation' => 'บุตร',
            'is_head' => 0,
            'id_card_last4' => '9999',
        ]);
        $this->assertDatabaseMissing('member', [
            'household_id' => $household->household_id,
            'full_name' => 'สมชาย สมาชิกเดิม',
        ]);
    }

    public function test_staff_main_menu_shows_attention_items(): void
    {
        ['staff' => $staffUser, 'household' => $household] = $this->seedFixtures();

        Household::create([
            'account_no' => 'ACC9000003',
            'house_no' => '88',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0811111199',
            'contact_person' => 'ครัวเรือนรออนุมัติ',
            'register_date' => '2026-03-01',
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => $staffUser->user_id,
        ]);

        DataSubjectRequest::create([
            'request_no' => 'DSAR-TEST-001',
            'household_id' => $household->household_id,
            'requester_name' => 'ผู้ยื่นคำขอ',
            'requester_contact' => '0812340000',
            'request_type' => 'access',
            'status' => 'submitted',
            'submitted_at' => now()->subDays(5),
            'due_at' => now()->subDay()->toDateString(),
            'assigned_to' => $staffUser->user_id,
            'request_details' => 'ขอคัดสำเนาข้อมูล',
            'resolution_notes' => null,
            'closed_at' => null,
        ]);

        SecurityIncident::create([
            'incident_no' => 'INC-TEST-001',
            'severity' => 'high',
            'status' => 'open',
            'reported_by' => $staffUser->user_id,
            'assigned_to' => $staffUser->user_id,
            'occurred_at' => now()->subDay(),
            'detected_at' => now()->subHours(12),
            'summary' => 'ตรวจพบการเข้าถึงข้อมูลผิดปกติ',
            'affected_scope' => 'ข้อมูลสมาชิก',
            'affected_record_count' => 3,
            'notification_required' => true,
            'authority_notified_at' => null,
            'subject_notified_at' => null,
            'impact_details' => 'ต้องตรวจสอบเพิ่มเติม',
            'containment_actions' => 'จำกัดสิทธิ์ชั่วคราว',
            'closed_at' => null,
        ]);

        $this->actingAs($staffUser)
            ->get(route('main-menu'))
            ->assertOk()
            ->assertSeeText('งานที่ต้องติดตาม')
            ->assertSeeText('คำขอสมาชิกใหม่')
            ->assertSeeText('DSAR เปิดอยู่')
            ->assertSeeText('เหตุที่ต้องแจ้งเตือน')
            ->assertViewHas('attentionItems', function (array $attentionItems) {
                return collect($attentionItems)->pluck('count', 'label')->all() === [
                    'คำขอสมาชิกใหม่' => 1,
                    'DSAR เปิดอยู่' => 1,
                    'เหตุที่ต้องแจ้งเตือน' => 1,
                ];
            });
    }

    private function seedFixtures(): array
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
            'username' => 'staff-household-convenience',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $household = Household::create([
            'account_no' => 'ACC9000001',
            'house_no' => '77',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000077',
            'contact_person' => 'สมหญิง สมาชิกเดิม',
            'register_date' => '2026-02-01',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => 245,
            'created_by' => $staffUser->user_id,
        ]);

        DB::table('member')->insert([
            [
                'household_id' => $household->household_id,
                'full_name' => 'สมหญิง สมาชิกเดิม',
                'id_card' => '1101700203451',
                'id_card_last4' => '3451',
                'id_card_hash' => hash('sha256', '1101700203451'),
                'relation' => 'หัวหน้าครัวเรือน',
                'is_head' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'household_id' => $household->household_id,
                'full_name' => 'สมชาย สมาชิกเดิม',
                'id_card' => '1101700203452',
                'id_card_last4' => '3452',
                'id_card_hash' => hash('sha256', '1101700203452'),
                'relation' => 'คู่สมรส',
                'is_head' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $memberUser = UserAccount::create([
            'username' => $household->account_no,
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $household->household_id,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        return [
            'staff' => $staffUser,
            'household' => $household,
            'memberUser' => $memberUser,
        ];
    }
}

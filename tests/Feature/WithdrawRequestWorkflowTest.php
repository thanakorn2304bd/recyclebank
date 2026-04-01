<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Transaction;
use App\Models\UserAccount;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WithdrawRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_withdraw_request_and_print_form(): void
    {
        ['member' => $memberUser, 'household' => $household] = $this->seedFixtures();

        $this->actingAs($memberUser)
            ->post(route('withdraw-requests.store'), [
                'requested_for_date' => '2026-04-05',
                'requested_amount' => '120.50',
                'request_notes' => 'สะดวกเข้ายื่นช่วงเช้า',
            ])
            ->assertRedirect();

        $withdrawRequest = WithdrawRequest::query()->firstOrFail();

        $this->assertSame($household->household_id, $withdrawRequest->household_id);
        $this->assertSame($memberUser->user_id, $withdrawRequest->requested_by);
        $this->assertSame('pending', $withdrawRequest->status);
        $this->assertSame(120.50, (float) $withdrawRequest->requested_amount);

        $this->actingAs($memberUser)
            ->get(route('withdraw-requests.show', $withdrawRequest))
            ->assertOk()
            ->assertSeeText('รออนุมัติ')
            ->assertSeeText('นำไปยื่นที่เทศบาล')
            ->assertSeeText('สำเนาทะเบียนบ้านและบัตรประชาชน');

        $this->actingAs($memberUser)
            ->get(route('withdraw-requests.form', $withdrawRequest))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_staff_can_approve_pending_withdraw_request_and_create_real_transaction(): void
    {
        [
            'staff' => $staffUser,
            'household' => $household,
        ] = $this->seedFixtures();

        $withdrawRequest = WithdrawRequest::create([
            'request_no' => 'WR-TEST-0001',
            'household_id' => $household->household_id,
            'requested_by' => null,
            'requested_for_date' => '2026-04-05',
            'requested_amount' => 120.50,
            'request_notes' => 'ยื่นเอกสารครบแล้ว',
            'status' => 'pending',
        ]);

        $this->actingAs($staffUser)
            ->patch(route('withdraw-requests.review', $withdrawRequest), [
                'decision' => 'approved',
                'transaction_date' => '2026-04-06',
                'review_notes' => 'ตรวจเอกสารครบ',
            ])
            ->assertRedirect(route('withdraw-requests.show', $withdrawRequest))
            ->assertSessionHas('success');

        $withdrawRequest->refresh();
        $transaction = Transaction::query()->findOrFail($withdrawRequest->approved_transaction_id);

        $this->assertSame('approved', $withdrawRequest->status);
        $this->assertSame($staffUser->user_id, $withdrawRequest->reviewed_by);
        $this->assertNotNull($withdrawRequest->reviewed_at);
        $this->assertSame('ตรวจเอกสารครบ', $withdrawRequest->review_notes);

        $this->assertSame('withdraw', $transaction->transaction_type);
        $this->assertSame(120.50, (float) $transaction->total_amount);
        $this->assertSame($household->household_id, $transaction->household_id);
        $this->assertSame($staffUser->user_id, $transaction->recorded_by);
        $this->assertSame('2026-04-06', $transaction->transaction_date?->toDateString());

        $this->assertSame(
            379.50,
            (float) DB::table('household')->where('household_id', $household->household_id)->value('total_balance')
        );

        $this->assertDatabaseHas('log_activity', [
            'module' => 'withdraw_requests',
            'entity_type' => 'withdraw_request',
            'entity_id' => (string) $withdrawRequest->withdraw_request_id,
        ]);

        $this->assertDatabaseHas('log_activity', [
            'module' => 'transactions',
            'entity_type' => 'transaction',
            'entity_id' => (string) $transaction->transaction_id,
        ]);
    }

    public function test_staff_main_menu_shows_pending_withdraw_request_attention_item(): void
    {
        [
            'staff' => $staffUser,
            'household' => $household,
        ] = $this->seedFixtures();

        WithdrawRequest::create([
            'request_no' => 'WR-TEST-0002',
            'household_id' => $household->household_id,
            'requested_by' => null,
            'requested_for_date' => '2026-04-05',
            'requested_amount' => 75.00,
            'request_notes' => 'รอเอกสาร',
            'status' => 'pending',
        ]);

        $this->actingAs($staffUser)
            ->get(route('main-menu'))
            ->assertOk()
            ->assertSeeText('คำขอถอนรออนุมัติ')
            ->assertViewHas('attentionItems', function (array $attentionItems) {
                return collect($attentionItems)->contains(function (array $item) {
                    return $item['label'] === 'คำขอถอนรออนุมัติ'
                        && $item['count'] === 1;
                });
            });
    }

    private function seedFixtures(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่คำขอถอน',
            'phone' => '0812345678',
            'position' => 'เจ้าหน้าที่',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-withdraw-request',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $household = Household::create([
            'account_no' => 'ACC7000001',
            'house_no' => '99/1',
            'village_no' => '2',
            'community_id' => '01',
            'phone' => '0890000001',
            'contact_person' => 'สมหญิง ขอถอน',
            'register_date' => '2026-03-01',
            'active_status' => 'active',
            'accumulated_months' => 4,
            'total_balance' => 500.00,
            'created_by' => $staffUser->user_id,
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
            'member' => $memberUser,
            'household' => $household,
        ];
    }
}

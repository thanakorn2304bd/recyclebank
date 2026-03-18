<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WithdrawSlipTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_stream_withdraw_slip_pdf(): void
    {
        ['staff' => $staffUser, 'withdrawTransaction' => $withdrawTransaction] = $this->seedWithdrawFixtures();

        $this->actingAs($staffUser)
            ->get(route('transactions.receipt', $withdrawTransaction))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_staff_can_preview_withdraw_slip_pdf_without_saving(): void
    {
        ['staff' => $staffUser] = $this->seedWithdrawFixtures(createWithdraw: false);

        $this->actingAs($staffUser)
            ->get(route('withdraws.preview', [
                'community_id' => '01',
                'house_no' => '11',
                'transaction_date' => '2026-03-10',
                'amount' => '25.50',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertDatabaseCount('transaction', 0);
    }

    public function test_staff_can_save_withdraw_and_redirect_to_pdf_when_requested(): void
    {
        ['staff' => $staffUser, 'householdId' => $householdId] = $this->seedWithdrawFixtures(createWithdraw: false);

        $response = $this->actingAs($staffUser)->post(route('withdraws.store'), [
            'community_id' => '01',
            'house_no' => '11',
            'transaction_date' => '2026-03-10',
            'amount' => '25.50',
        ]);

        $transaction = Transaction::query()
            ->where('household_id', $householdId)
            ->where('transaction_type', 'withdraw')
            ->latest('transaction_id')
            ->firstOrFail();

        $response->assertRedirect(route('transactions.receipt', $transaction));

        $this->assertDatabaseHas('transaction', [
            'transaction_id' => $transaction->transaction_id,
            'total_amount' => '25.50',
            'transaction_type' => 'withdraw',
        ]);

        $this->assertSame(
            74.50,
            (float) DB::table('household')->where('household_id', $householdId)->value('total_balance')
        );
    }

    private function seedWithdrawFixtures(bool $createWithdraw = true): array
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

        $householdId = DB::table('household')->insertGetId([
            'account_no' => 'ACC0000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000001',
            'contact_person' => 'สมชาย ใจดี',
            'register_date' => '2026-01-05',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => 100.00,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-withdraw',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $result = [
            'staff' => $staffUser,
            'householdId' => $householdId,
        ];

        if ($createWithdraw) {
            $withdrawTransactionId = DB::table('transaction')->insertGetId([
                'household_id' => $householdId,
                'transaction_date' => '2026-03-03',
                'transaction_type' => 'withdraw',
                'total_weight' => 0.00,
                'total_amount' => 20.00,
                'recorded_by' => $staffUser->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $result['withdrawTransaction'] = Transaction::findOrFail($withdrawTransactionId);
        }

        return $result;
    }
}

<?php

namespace Tests\Feature;

use App\Models\LogActivity;
use App\Models\Transaction;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionReversalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_reverse_deposit_transaction_and_create_audit_log(): void
    {
        [
            'staff' => $staffUser,
            'householdId' => $householdId,
            'transaction' => $transaction,
            'materialId' => $materialId,
        ] = $this->seedTransactionReversalFixtures(
            transactionType: 'deposit',
            transactionAmount: 20.00,
            householdBalance: 120.00
        );

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->actingAs($staffUser)
            ->post(route('transactions.reverse', $transaction), [
                'reversal_date' => '2026-03-12',
                'reason' => 'บันทึกน้ำหนักผิด',
            ])
            ->assertRedirect(route('transactions.show', $transaction))
            ->assertSessionHas('success');

        $transaction->refresh();
        $reversal = Transaction::query()
            ->where('reversal_of_transaction_id', $transaction->transaction_id)
            ->firstOrFail();

        $this->assertSame($staffUser->user_id, $transaction->reversed_by);
        $this->assertNotNull($transaction->reversed_at);
        $this->assertSame('บันทึกน้ำหนักผิด', $transaction->reversal_reason);
        $this->assertTrue((bool) $reversal->is_reversal);
        $this->assertSame('deposit', $reversal->transaction_type);
        $this->assertSame(-5.0, (float) $reversal->total_weight);
        $this->assertSame(-20.0, (float) $reversal->total_amount);
        $this->assertSame('บันทึกน้ำหนักผิด', $reversal->reversal_reason);

        $this->assertDatabaseHas('transaction_detail', [
            'transaction_id' => $reversal->transaction_id,
            'material_id' => $materialId,
            'weight' => '-5.00',
            'amount' => '-20.00',
        ]);

        $this->assertSame(
            100.00,
            (float) DB::table('household')->where('household_id', $householdId)->value('total_balance')
        );

        $auditLog = LogActivity::query()
            ->where('module', 'transactions.reverse')
            ->where('entity_type', 'transaction')
            ->where('entity_id', (string) $transaction->transaction_id)
            ->firstOrFail();

        $this->assertSame('203.0.113.10', $auditLog->ip_address);
        $this->assertSame(
            $transaction->transaction_id,
            $auditLog->before_values['original']['transaction_id'] ?? null
        );
        $this->assertSame(
            $reversal->transaction_id,
            $auditLog->after_values['reversal']['transaction_id'] ?? null
        );
        $this->assertSame('บันทึกน้ำหนักผิด', $auditLog->metadata['reversal_reason'] ?? null);
    }

    public function test_staff_can_reverse_withdraw_transaction_and_restore_balance(): void
    {
        [
            'staff' => $staffUser,
            'householdId' => $householdId,
            'transaction' => $transaction,
        ] = $this->seedTransactionReversalFixtures(
            transactionType: 'withdraw',
            transactionAmount: 30.00,
            householdBalance: 70.00
        );

        $this->actingAs($staffUser)
            ->post(route('transactions.reverse', $transaction), [
                'reversal_date' => '2026-03-12',
                'reason' => 'บันทึกยอดถอนผิด',
            ])
            ->assertRedirect(route('transactions.show', $transaction));

        $reversal = Transaction::query()
            ->where('reversal_of_transaction_id', $transaction->transaction_id)
            ->firstOrFail();

        $this->assertTrue((bool) $reversal->is_reversal);
        $this->assertSame('withdraw', $reversal->transaction_type);
        $this->assertSame(-30.0, (float) $reversal->total_amount);
        $this->assertSame(0.0, (float) $reversal->total_weight);
        $this->assertSame(
            100.00,
            (float) DB::table('household')->where('household_id', $householdId)->value('total_balance')
        );
    }

    public function test_staff_cannot_reverse_deposit_transaction_when_current_balance_is_insufficient(): void
    {
        [
            'staff' => $staffUser,
            'householdId' => $householdId,
            'transaction' => $transaction,
        ] = $this->seedTransactionReversalFixtures(
            transactionType: 'deposit',
            transactionAmount: 40.00,
            householdBalance: 10.00
        );

        $this->actingAs($staffUser)
            ->post(route('transactions.reverse', $transaction), [
                'reversal_date' => '2026-03-12',
                'reason' => 'ยอดคงเหลือไม่พอ',
            ])
            ->assertSessionHasErrors('reason');

        $transaction->refresh();

        $this->assertNull($transaction->reversed_at);
        $this->assertDatabaseMissing('transaction', [
            'reversal_of_transaction_id' => $transaction->transaction_id,
        ]);
        $this->assertSame(
            10.00,
            (float) DB::table('household')->where('household_id', $householdId)->value('total_balance')
        );
    }

    private function seedTransactionReversalFixtures(
        string $transactionType,
        float $transactionAmount,
        float $householdBalance
    ): array {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่กลับรายการ',
            'phone' => '0812345678',
            'position' => 'เจ้าหน้าที่',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-reversal',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $householdId = DB::table('household')->insertGetId([
            'account_no' => 'ACC3000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000001',
            'contact_person' => 'สมชาย กลับรายการ',
            'register_date' => '2026-01-05',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => $householdBalance,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $transactionId = DB::table('transaction')->insertGetId([
            'household_id' => $householdId,
            'transaction_date' => '2026-03-10',
            'transaction_type' => $transactionType,
            'total_weight' => $transactionType === 'deposit' ? 5.00 : 0.00,
            'total_amount' => $transactionAmount,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialId = null;

        if ($transactionType === 'deposit') {
            $categoryId = DB::table('material_category')->insertGetId([
                'category_name' => 'พลาสติก',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $materialId = DB::table('material')->insertGetId([
                'category_id' => $categoryId,
                'material_name' => 'ขวดพลาสติก',
                'unit' => 'kg',
                'description' => '',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('transaction_detail')->insert([
                'transaction_id' => $transactionId,
                'material_id' => $materialId,
                'weight' => 5.00,
                'price_per_unit' => round($transactionAmount / 5, 2),
                'amount' => $transactionAmount,
            ]);
        }

        return [
            'staff' => $staffUser,
            'householdId' => $householdId,
            'transaction' => Transaction::findOrFail($transactionId),
            'materialId' => $materialId,
        ];
    }
}

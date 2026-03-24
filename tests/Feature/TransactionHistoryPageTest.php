<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Transaction;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionHistoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_their_transactions_in_history(): void
    {
        ['member' => $memberUser, 'memberHousehold' => $memberHousehold, 'otherHousehold' => $otherHousehold] = $this->seedTransactionHistoryFixtures();

        $response = $this->actingAs($memberUser)->get(route('transactions.index'));

        $response
            ->assertOk()
            ->assertSee($memberHousehold->account_no)
            ->assertDontSee($otherHousehold->account_no);
    }

    public function test_staff_can_filter_transaction_history_by_household_and_type(): void
    {
        ['staff' => $staffUser, 'memberHousehold' => $memberHousehold, 'otherHousehold' => $otherHousehold] = $this->seedTransactionHistoryFixtures();

        $response = $this->actingAs($staffUser)->get(route('transactions.index', [
            'household_id' => $otherHousehold->household_id,
            'type' => 'withdraw',
        ]));

        $response
            ->assertOk()
            ->assertSee($otherHousehold->account_no)
            ->assertSee('ถอน')
            ->assertViewHas('txs', function ($txs) use ($otherHousehold) {
                return $txs->count() === 1
                    && (int) $txs->first()->household_id === (int) $otherHousehold->household_id
                    && $txs->first()->transaction_type === 'withdraw';
            });
    }

    public function test_member_cannot_view_another_household_statement(): void
    {
        ['member' => $memberUser, 'otherHousehold' => $otherHousehold] = $this->seedTransactionHistoryFixtures();

        $this->actingAs($memberUser)
            ->get(route('transactions.household', $otherHousehold))
            ->assertForbidden();
    }

    public function test_staff_can_view_transaction_detail_page(): void
    {
        ['staff' => $staffUser, 'transaction' => $transaction] = $this->seedTransactionHistoryFixtures();

        $this->actingAs($staffUser)
            ->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee((string) $transaction->transaction_id)
            ->assertSee('รายละเอียด');
    }

    private function seedTransactionHistoryFixtures(): array
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
            'username' => 'staff-transactions',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $memberHousehold = Household::create([
            'account_no' => 'ACC1000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000001',
            'contact_person' => 'สมาชิกหนึ่ง',
            'register_date' => '2026-01-01',
            'active_status' => 'active',
            'accumulated_months' => 0,
            'total_balance' => 100,
            'created_by' => $staffUser->user_id,
        ]);

        $otherHousehold = Household::create([
            'account_no' => 'ACC1000002',
            'house_no' => '12',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000002',
            'contact_person' => 'สมาชิกสอง',
            'register_date' => '2026-01-02',
            'active_status' => 'active',
            'accumulated_months' => 0,
            'total_balance' => 50,
            'created_by' => $staffUser->user_id,
        ]);

        $memberUser = UserAccount::create([
            'username' => 'member-transactions',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $memberHousehold->household_id,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $memberTransactionId = DB::table('transaction')->insertGetId([
            'household_id' => $memberHousehold->household_id,
            'transaction_date' => '2026-03-10',
            'transaction_type' => 'deposit',
            'total_weight' => 5.00,
            'total_amount' => 20.00,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transaction')->insert([
            'household_id' => $otherHousehold->household_id,
            'transaction_date' => '2026-03-11',
            'transaction_type' => 'withdraw',
            'total_weight' => 0.00,
            'total_amount' => 10.00,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
            'transaction_id' => $memberTransactionId,
            'material_id' => $materialId,
            'weight' => 5.00,
            'price_per_unit' => 4.00,
            'amount' => 20.00,
        ]);

        return [
            'staff' => $staffUser,
            'member' => $memberUser,
            'memberHousehold' => $memberHousehold,
            'otherHousehold' => $otherHousehold,
            'transaction' => Transaction::findOrFail($memberTransactionId),
        ];
    }
}

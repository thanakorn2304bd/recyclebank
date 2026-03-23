<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DepositSummaryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_save_deposit_and_redirect_to_summary_page(): void
    {
        ['staff' => $staffUser, 'householdId' => $householdId, 'materialId' => $materialId] = $this->seedDepositFixtures();

        $response = $this->actingAs($staffUser)->post(route('deposits.store'), [
            'community_id' => '01',
            'house_no' => '11',
            'transaction_date' => '2026-03-10',
            'items' => [
                [
                    'material_id' => $materialId,
                    'weight' => '5.00',
                ],
            ],
        ]);

        $transaction = Transaction::query()
            ->where('household_id', $householdId)
            ->where('transaction_type', 'deposit')
            ->latest('transaction_id')
            ->firstOrFail();

        $response->assertRedirect(route('transactions.show', [
            'transaction' => $transaction,
            'source' => 'deposit',
        ]));

        $this->assertDatabaseHas('transaction', [
            'transaction_id' => $transaction->transaction_id,
            'transaction_type' => 'deposit',
            'total_weight' => '5.00',
            'total_amount' => '17.00',
        ]);

        $this->assertDatabaseHas('transaction_detail', [
            'transaction_id' => $transaction->transaction_id,
            'material_id' => $materialId,
            'weight' => '5.00',
            'price_per_unit' => '3.40',
            'amount' => '17.00',
        ]);

        $this->assertSame(
            117.00,
            (float) DB::table('household')->where('household_id', $householdId)->value('total_balance')
        );

        $this->actingAs($staffUser)
            ->get(route('transactions.show', [
                'transaction' => $transaction,
                'source' => 'deposit',
            ]))
            ->assertOk()
            ->assertSee('สรุปรายการฝาก/รับซื้อ')
            ->assertSee('พิมพ์ใบเสร็จ')
            ->assertSee('กลับหน้ารับฝาก');
    }

    private function seedDepositFixtures(): array
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
            'username' => 'staff-deposit',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
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

        $categoryId = DB::table('material_category')->insertGetId([
            'category_name' => 'กระดาษ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materialId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'กระดาษขาวดำ',
            'unit' => 'kg',
            'description' => 'วัสดุทดสอบ',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('material_price')->insert([
            'material_id' => $materialId,
            'price' => 3.40,
            'effective_date' => '2026-01-01',
            'expired_date' => null,
            'created_by' => $staffUser->user_id,
            'created_at' => now(),
        ]);

        return [
            'staff' => $staffUser,
            'householdId' => $householdId,
            'materialId' => $materialId,
        ];
    }
}

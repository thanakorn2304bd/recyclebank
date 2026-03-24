<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\UserAccount;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class ReceiptPdfPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_receipt_builds_carry_forward_pages_when_details_span_multiple_pages(): void
    {
        ['staff' => $staffUser, 'transaction' => $transaction] = $this->seedDepositReceiptFixtures(detailCount: 8);

        $pdf = Mockery::mock(DomPdfWrapper::class);
        $pdf->shouldReceive('setPaper')
            ->once()
            ->with('a5', 'landscape')
            ->andReturnSelf();
        $pdf->shouldReceive('stream')
            ->once()
            ->with('receipt_'.$transaction->transaction_id.'.pdf')
            ->andReturn(response('pdf', 200, ['content-type' => 'application/pdf']));

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.receipt_a5_landscape', Mockery::on(function (array $data) use ($transaction) {
                $this->assertSame($transaction->transaction_id, $data['tx']->transaction_id);
                $this->assertSame('กองทุนธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่', $data['orgName']);
                $this->assertSame('ใบนำฝาก', $data['title']);
                $this->assertSame('22', $data['houseNo']);
                $this->assertSame('1', $data['villageNo']);
                $this->assertSame('01', $data['community']);
                $this->assertSame('ACC0000002', $data['accountNo']);
                $this->assertSame('20/03/2026', $data['dateText']);
                $this->assertSame(7, $data['rowsPerPage']);

                $pages = $data['pages'];
                $this->assertCount(2, $pages);

                $firstPage = $pages->get(0);
                $this->assertFalse($firstPage['showCarryIn']);
                $this->assertCount(7, $firstPage['items']);
                $this->assertSame('ขวดพลาสติกใส', $firstPage['items']->first()['materialName']);
                $this->assertSame('1.00', $firstPage['items']->first()['weightText']);
                $this->assertSame('ยอดยกไป', $firstPage['footerLabel']);
                $this->assertSame(70.0, $firstPage['footerTotal']);
                $this->assertSame('70.00', $firstPage['footerTotalText']);
                $this->assertSame(0, $firstPage['blankRows']);

                $secondPage = $pages->get(1);
                $this->assertTrue($secondPage['showCarryIn']);
                $this->assertCount(1, $secondPage['items']);
                $this->assertSame(70.0, $secondPage['carryIn']);
                $this->assertSame('70.00', $secondPage['carryInText']);
                $this->assertSame('รวมเป็นเงิน', $secondPage['footerLabel']);
                $this->assertSame(80.0, $secondPage['footerTotal']);
                $this->assertSame('80.00', $secondPage['footerTotalText']);
                $this->assertSame(5, $secondPage['blankRows']);
                $this->assertNotEmpty($secondPage['amountText']);

                return true;
            }))
            ->andReturn($pdf);

        $this->actingAs($staffUser)
            ->get(route('transactions.receipt', $transaction))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function seedDepositReceiptFixtures(int $detailCount): array
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
            'username' => 'staff-receipt',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $householdId = DB::table('household')->insertGetId([
            'account_no' => 'ACC0000002',
            'house_no' => '22',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000002',
            'contact_person' => 'สายใจ ใจดี',
            'register_date' => '2026-01-05',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => 180.00,
            'created_by' => null,
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
            'material_name' => 'ขวดพลาสติกใส',
            'unit' => 'kg',
            'description' => 'วัสดุทดสอบใบเสร็จ',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $transactionId = DB::table('transaction')->insertGetId([
            'household_id' => $householdId,
            'transaction_date' => '2026-03-20',
            'transaction_type' => 'deposit',
            'total_weight' => $detailCount,
            'total_amount' => $detailCount * 10,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $details = [];
        for ($i = 0; $i < $detailCount; $i++) {
            $details[] = [
                'transaction_id' => $transactionId,
                'material_id' => $materialId,
                'weight' => 1.00,
                'price_per_unit' => 10.00,
                'amount' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('transaction_detail')->insert($details);

        return [
            'staff' => $staffUser,
            'transaction' => Transaction::findOrFail($transactionId),
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_their_own_report_data(): void
    {
        ['member' => $memberUser] = $this->seedReportFixtures();

        $response = $this->actingAs($memberUser)->get(route('reports.index'));

        $response
            ->assertOk()
            ->assertSee('ข้อมูลครัวเรือนของฉัน')
            ->assertSee('ACC0000001')
            ->assertSee('Plastic Bottle')
            ->assertDontSee('ACC0000002')
            ->assertDontSee('Aluminum Can');
    }

    public function test_staff_sees_systemwide_report_data(): void
    {
        ['staff' => $staffUser] = $this->seedReportFixtures();

        $response = $this->actingAs($staffUser)->get(route('reports.index'));

        $response
            ->assertOk()
            ->assertSee('สรุปตามชุมชน')
            ->assertSee('ACC0000001')
            ->assertSee('ACC0000002')
            ->assertSee('Plastic Bottle')
            ->assertSee('Aluminum Can');
    }

    public function test_staff_can_filter_report_by_community(): void
    {
        ['staff' => $staffUser] = $this->seedReportFixtures();

        $response = $this->actingAs($staffUser)->get(route('reports.index', [
            'community_id' => '01',
        ]));

        $response
            ->assertOk()
            ->assertSee('North Community')
            ->assertSee('ACC0000001')
            ->assertSee('Plastic Bottle')
            ->assertDontSee('ACC0000002')
            ->assertDontSee('Aluminum Can');
    }

    public function test_member_can_filter_report_by_material_category(): void
    {
        ['member' => $memberUser, 'categoryId' => $thisCategoryId] = $this->seedReportFixtures();

        $response = $this->actingAs($memberUser)->get(route('reports.index', [
            'category_id' => $thisCategoryId,
        ]));

        $response
            ->assertOk()
            ->assertSee('Plastic Bottle')
            ->assertDontSee('Aluminum Can');
    }

    public function test_staff_can_export_report_as_excel(): void
    {
        ['staff' => $staffUser] = $this->seedReportFixtures();

        $this->actingAs($staffUser)
            ->get(route('reports.export.excel'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_staff_can_export_report_as_pdf(): void
    {
        ['staff' => $staffUser] = $this->seedReportFixtures();

        $this->actingAs($staffUser)
            ->get(route('reports.export.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_member_without_household_is_redirected_from_report_index(): void
    {
        $memberUser = UserAccount::create([
            'username' => 'member-without-household-report',
            'password' => Hash::make('password'),
            'role' => 'member',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $this->actingAs($memberUser)
            ->get(route('reports.index'))
            ->assertRedirect(route('main-menu'))
            ->assertSessionHasErrors();
    }

    public function test_member_without_household_gets_not_found_on_report_export(): void
    {
        $memberUser = UserAccount::create([
            'username' => 'member-without-household-export',
            'password' => Hash::make('password'),
            'role' => 'member',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $this->actingAs($memberUser)
            ->get(route('reports.export.pdf'))
            ->assertNotFound();
    }

    private function seedReportFixtures(): array
    {
        DB::table('community')->insert([
            ['community_id' => '01', 'community_name' => 'North Community'],
            ['community_id' => '02', 'community_name' => 'South Community'],
        ]);

        $categoryId = DB::table('material_category')->insertGetId([
            'category_name' => 'Recyclable',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $plasticId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'Plastic Bottle',
            'unit' => 'kg',
            'description' => 'Clear plastic bottle',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $aluminumId = DB::table('material')->insertGetId([
            'category_id' => $categoryId,
            'material_name' => 'Aluminum Can',
            'unit' => 'kg',
            'description' => 'Used aluminum can',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $householdOneId = DB::table('household')->insertGetId([
            'account_no' => 'ACC0000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0810000001',
            'contact_person' => 'Member One',
            'register_date' => '2026-01-05',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => 80.00,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $householdTwoId = DB::table('household')->insertGetId([
            'account_no' => 'ACC0000002',
            'house_no' => '22',
            'village_no' => '2',
            'community_id' => '02',
            'phone' => '0810000002',
            'contact_person' => 'Member Two',
            'register_date' => '2026-01-08',
            'active_status' => 'active',
            'accumulated_months' => 2,
            'total_balance' => 200.00,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('member')->insert([
            [
                'household_id' => $householdOneId,
                'full_name' => 'Head One',
                'id_card' => '1111111111111',
                'is_head' => true,
                'relation' => 'Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'household_id' => $householdOneId,
                'full_name' => 'Child One',
                'id_card' => '1111111111112',
                'is_head' => false,
                'relation' => 'Child',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'household_id' => $householdTwoId,
                'full_name' => 'Head Two',
                'id_card' => '2222222222221',
                'is_head' => true,
                'relation' => 'Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-report',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $memberUser = UserAccount::create([
            'username' => 'member-report',
            'password' => Hash::make('password'),
            'role' => 'member',
            'household_id' => $householdOneId,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        UserAccount::create([
            'username' => 'member-two-report',
            'password' => Hash::make('password'),
            'role' => 'member',
            'household_id' => $householdTwoId,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $householdOneDepositId = DB::table('transaction')->insertGetId([
            'household_id' => $householdOneId,
            'transaction_date' => '2026-03-01',
            'transaction_type' => 'deposit',
            'total_weight' => 10.00,
            'total_amount' => 100.00,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transaction')->insert([
            'household_id' => $householdOneId,
            'transaction_date' => '2026-03-03',
            'transaction_type' => 'withdraw',
            'total_weight' => 0.00,
            'total_amount' => 20.00,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $householdTwoDepositId = DB::table('transaction')->insertGetId([
            'household_id' => $householdTwoId,
            'transaction_date' => '2026-03-02',
            'transaction_type' => 'deposit',
            'total_weight' => 20.00,
            'total_amount' => 200.00,
            'recorded_by' => $staffUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transaction_detail')->insert([
            [
                'transaction_id' => $householdOneDepositId,
                'material_id' => $plasticId,
                'weight' => 10.00,
                'price_per_unit' => 10.00,
                'amount' => 100.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_id' => $householdTwoDepositId,
                'material_id' => $aluminumId,
                'weight' => 20.00,
                'price_per_unit' => 10.00,
                'amount' => 200.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return [
            'staff' => $staffUser,
            'member' => $memberUser,
            'categoryId' => $categoryId,
        ];
    }
}

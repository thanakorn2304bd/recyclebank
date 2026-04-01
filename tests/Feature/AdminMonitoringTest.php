<?php

namespace Tests\Feature;

use App\Models\LogActivity;
use App\Models\Staff;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_user_accounts(): void
    {
        ['admin' => $adminUser, 'member' => $memberUser, 'staff' => $staffUser] = $this->seedAdminMonitoringFixtures();

        $response = $this->actingAs($adminUser)->get(route('admin.users.index'));

        $response
            ->assertOk()
            ->assertSee('บัญชีผู้ใช้ทั้งหมด')
            ->assertSee($adminUser->username)
            ->assertSee($staffUser->username)
            ->assertSee($memberUser->username)
            ->assertSee('ผู้ดูแลระบบ')
            ->assertSee('เจ้าหน้าที่')
            ->assertSee('สมาชิก');
    }

    public function test_admin_can_view_activity_logs(): void
    {
        ['admin' => $adminUser, 'staff' => $staffUser] = $this->seedAdminMonitoringFixtures();

        LogActivity::create([
            'user_id' => $staffUser->user_id,
            'module' => 'transactions',
            'action' => 'บันทึกฝาก/รับซื้อให้ ACC0000001',
            'timestamp' => now(),
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.activity-logs.index'));

        $response
            ->assertOk()
            ->assertSee('Activity Log')
            ->assertSee($staffUser->username)
            ->assertSee('บันทึกฝาก/รับซื้อให้ ACC0000001')
            ->assertSee('ธุรกรรม');
    }

    public function test_admin_can_create_staff_account_from_admin_users_page(): void
    {
        ['admin' => $adminUser] = $this->seedAdminMonitoringFixtures();

        $response = $this->actingAs($adminUser)->post(route('admin.users.store-staff'), [
            'full_name' => 'เจ้าหน้าที่ใหม่',
            'phone' => '0801234567',
            'position' => 'เจ้าหน้าที่ประจำจุดรับซื้อ',
            'username' => 'staff.new',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_status' => 'active',
        ]);

        $response
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $createdUser = UserAccount::query()
            ->where('username', 'staff.new')
            ->with('staff')
            ->first();

        $this->assertNotNull($createdUser);
        $this->assertSame('staff', $createdUser->role);
        $this->assertTrue((bool) $createdUser->is_active);
        $this->assertTrue((bool) $createdUser->force_password_reset);
        $this->assertNull($createdUser->household_id);
        $this->assertNotNull($createdUser->staff_id);
        $this->assertTrue(Hash::check('password123', $createdUser->password));
        $this->assertNotNull($createdUser->password_changed_at);
        $this->assertSame('เจ้าหน้าที่ใหม่', $createdUser->staff?->full_name);
        $this->assertSame('เจ้าหน้าที่ประจำจุดรับซื้อ', $createdUser->staff?->position);

        $this->assertDatabaseHas('log_activity', [
            'user_id' => $adminUser->user_id,
            'module' => 'admin.users',
            'action' => 'เพิ่มบัญชีเจ้าหน้าที่ staff.new (เจ้าหน้าที่ใหม่)',
        ]);
    }

    public function test_admin_can_view_staff_directory(): void
    {
        ['admin' => $adminUser] = $this->seedAdminMonitoringFixtures();

        $response = $this->actingAs($adminUser)->get(route('admin.staff.index'));

        $response
            ->assertOk()
            ->assertSee('ข้อมูลเจ้าหน้าที่')
            ->assertSee('เจ้าหน้าที่ระบบ')
            ->assertSee('แอดมินระบบ')
            ->assertSee('staff-monitor');
    }

    public function test_admin_can_view_staff_detail_page(): void
    {
        ['admin' => $adminUser] = $this->seedAdminMonitoringFixtures();

        $staff = Staff::query()->where('full_name', 'เจ้าหน้าที่ระบบ')->firstOrFail();

        $response = $this->actingAs($adminUser)->get(route('admin.staff.show', $staff));

        $response
            ->assertOk()
            ->assertSee('รายละเอียดเจ้าหน้าที่')
            ->assertSee('เจ้าหน้าที่ระบบ')
            ->assertSee('staff-monitor')
            ->assertSee('ข้อมูลพื้นฐาน')
            ->assertSee('Activity Log ล่าสุด');
    }

    public function test_staff_cannot_access_admin_only_pages(): void
    {
        ['staff' => $staffUser] = $this->seedAdminMonitoringFixtures();
        $staff = Staff::query()->where('full_name', 'เจ้าหน้าที่ระบบ')->firstOrFail();

        $this->actingAs($staffUser)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('admin.staff.index'))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('admin.staff.show', $staff))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('admin.activity-logs.index'))
            ->assertForbidden();
    }

    public function test_successful_login_creates_auth_activity_log(): void
    {
        ['staff' => $staffUser] = $this->seedAdminMonitoringFixtures();

        $response = $this->post(route('login'), [
            'username' => $staffUser->username,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('main-menu'));

        $this->assertDatabaseHas('log_activity', [
            'user_id' => $staffUser->user_id,
            'module' => 'auth',
            'action' => 'เข้าสู่ระบบ',
        ]);
    }

    private function seedAdminMonitoringFixtures(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $adminStaffId = DB::table('staff')->insertGetId([
            'full_name' => 'แอดมินระบบ',
            'phone' => '0800000001',
            'position' => 'ผู้ดูแลระบบ',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่ระบบ',
            'phone' => '0800000002',
            'position' => 'เจ้าหน้าที่',
        ]);

        $adminUser = UserAccount::create([
            'username' => 'admin-monitor',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'household_id' => null,
            'staff_id' => $adminStaffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-monitor',
            'password' => Hash::make('password123'),
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
            'contact_person' => 'สมหญิง ตัวอย่าง',
            'register_date' => '2026-01-05',
            'active_status' => 'active',
            'accumulated_months' => 3,
            'total_balance' => 100.00,
            'created_by' => $adminUser->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $memberUser = UserAccount::create([
            'username' => 'member-monitor',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $householdId,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        return [
            'admin' => $adminUser,
            'staff' => $staffUser,
            'member' => $memberUser,
            'household_id' => $householdId,
        ];
    }
}

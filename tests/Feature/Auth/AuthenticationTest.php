<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\RegistrationStatusController;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->createUserAccount();

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('main-menu'));
    }

    public function test_member_users_are_redirected_to_main_menu_after_login(): void
    {
        $user = $this->createUserAccount([
            'username' => 'member-auth-test',
            'role' => 'member',
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('main-menu'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = $this->createUserAccount();

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_can_not_authenticate_even_with_correct_password(): void
    {
        $user = $this->createUserAccount([
            'username' => 'inactive-auth-test',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_pending_member_users_are_redirected_to_tracking_page_on_login(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $householdId = DB::table('household')->insertGetId([
            'account_no' => 'ACC0000099',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0812345678',
            'contact_person' => 'สมชาย รออนุมัติ',
            'register_date' => now()->toDateString(),
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = $this->createUserAccount([
            'username' => 'ACC0000099',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $householdId,
            'is_active' => false,
        ]);

        $this->from(route('login'))
            ->post('/login', [
                'username' => $user->username,
                'password' => 'password123',
            ])
            ->assertRedirect(route('registration-status.show'))
            ->assertSessionHas(RegistrationStatusController::TRACKING_SESSION_KEY, $user->user_id)
            ->assertSessionHas('status', 'คำขอสมัครสมาชิกของบัญชี ACC0000099 อยู่ระหว่างรออนุมัติจากเจ้าหน้าที่ กรุณาติดตามผลได้จากหน้าสถานะคำขอสมัครสมาชิก');

        $this->assertGuest();
    }

    public function test_returned_member_users_are_redirected_to_tracking_page_on_login(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $householdId = DB::table('household')->insertGetId([
            'account_no' => 'ACC0000100',
            'house_no' => '12',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0811111111',
            'contact_person' => 'สมหญิง แก้เอกสาร',
            'register_date' => now()->toDateString(),
            'active_status' => 'inactive',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => null,
            'review_notes' => 'บัตรประชาชนไม่ชัดเจน',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = $this->createUserAccount([
            'username' => 'ACC0000100',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $householdId,
            'is_active' => false,
        ]);

        $this->from(route('login'))
            ->post('/login', [
                'username' => $user->username,
                'password' => 'password123',
            ])
            ->assertRedirect(route('registration-status.show'))
            ->assertSessionHas(RegistrationStatusController::TRACKING_SESSION_KEY, $user->user_id)
            ->assertSessionHas('status', 'คำขอสมัครสมาชิกของบัญชี ACC0000100 ถูกส่งกลับเพื่อแก้ไขเอกสาร กรุณาไปที่หน้าติดตามคำขอเพื่อดูหมายเหตุและอัปโหลดเอกสารใหม่');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = $this->createUserAccount();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_inactive_users_are_logged_out_on_the_next_protected_request(): void
    {
        $user = $this->createUserAccount([
            'username' => 'deactivated-during-session',
        ]);

        $this->actingAs($user);

        $user->forceFill([
            'is_active' => false,
        ])->save();

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    private function createUserAccount(array $overrides = []): UserAccount
    {
        return UserAccount::create(array_merge([
            'username' => 'staff-auth-test',
            'password' => 'password',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ], $overrides));
    }
}

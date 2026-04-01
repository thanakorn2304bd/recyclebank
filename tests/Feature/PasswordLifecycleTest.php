<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_force_password_reset_is_redirected_to_change_password_and_can_complete_it(): void
    {
        $user = $this->createStaffUser([
            'username' => 'password-reset-user',
            'password' => Hash::make('password123'),
            'force_password_reset' => true,
            'password_changed_at' => now()->subDay(),
        ]);

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'password123',
        ])->assertRedirect(route('account.password.edit'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('account.password.edit'));

        $this->actingAs($user)
            ->get(route('main-menu'))
            ->assertRedirect(route('account.password.edit'));

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'password123',
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])
            ->assertRedirect(route('main-menu'));

        $user->refresh();

        $this->assertFalse((bool) $user->force_password_reset);
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue(Hash::check('new-password123', $user->password));

        auth()->logout();

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'new-password123',
        ])->assertRedirect(route('main-menu'));
    }

    public function test_account_is_temporarily_locked_after_repeated_failed_password_attempts(): void
    {
        $user = $this->createStaffUser([
            'username' => 'locked-user',
            'password' => Hash::make('password123'),
        ]);

        foreach (range(1, 4) as $attempt) {
            $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('username');
        }

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors([
            'username' => 'บัญชีนี้ถูกล็อกชั่วคราวจากการกรอกรหัสผ่านผิดหลายครั้ง จนถึง '.now()->addMinutes(15)->format('d/m/Y H:i'),
        ]);

        $user->refresh();
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->isLocked());

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'password123',
        ])->assertSessionHasErrors('username');

        $this->travel(16)->minutes();

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'password123',
        ])->assertRedirect(route('main-menu'));

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_setting_household_credentials_marks_member_for_password_reset(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffUser = $this->createStaffUser([
            'username' => 'household-password-staff',
            'password' => Hash::make('password123'),
        ]);

        $household = Household::create([
            'account_no' => 'ACC3000001',
            'house_no' => '11',
            'village_no' => '1',
            'community_id' => '01',
            'phone' => '0812340000',
            'contact_person' => 'สมหญิง ทดสอบ',
            'register_date' => '2026-03-01',
            'active_status' => 'active',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => $staffUser->user_id,
        ]);

        $this->actingAs($staffUser)
            ->post(route('households.credentials.store', $household), [
                'password' => 'temporary123',
                'password_confirmation' => 'temporary123',
            ])
            ->assertRedirect(route('households.show', $household));

        $createdMember = UserAccount::query()
            ->where('household_id', $household->household_id)
            ->where('role', 'member')
            ->firstOrFail();

        $this->assertTrue((bool) $createdMember->force_password_reset);
        $this->assertNotNull($createdMember->password_changed_at);
        $this->assertTrue(Hash::check('temporary123', $createdMember->password));
    }

    private function createStaffUser(array $overrides = []): UserAccount
    {
        return UserAccount::create(array_merge([
            'username' => 'password-lifecycle-staff',
            'password' => Hash::make('password123'),
            'password_changed_at' => now()->subDays(2),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'force_password_reset' => false,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'is_active' => true,
        ], $overrides));
    }
}

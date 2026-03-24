<?php

namespace Tests\Feature\Auth;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_users_can_logout(): void
    {
        $user = $this->createUserAccount();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
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

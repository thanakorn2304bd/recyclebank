<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_dashboard_page(): void
    {
        $staffUser = UserAccount::create([
            'username' => 'staff-dashboard',
            'password' => 'password123',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $this->actingAs($staffUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ศูนย์ควบคุมการใช้งาน')
            ->assertSee('จัดการระบบ');
    }
}

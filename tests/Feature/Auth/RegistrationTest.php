<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $response = $this->get('/register');

        $response
            ->assertStatus(200)
            ->assertSee('สมัครสมาชิก');
    }

    public function test_new_households_can_register_and_stay_pending_until_approved(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $response = $this->post('/register', [
            'community_id' => '01',
            'house_no' => '11/2',
            'village_no' => '2',
            'contact_person' => 'สมชาย ใจดี',
            'phone' => '0812345678',
            'members' => [
                [
                    'full_name' => 'สมชาย ใจดี',
                    'id_card' => '1234567890123',
                    'relation' => 'หัวหน้าครัวเรือน',
                    'is_head' => '1',
                ],
            ],
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();

        $household = DB::table('household')->first();

        $this->assertNotNull($household);
        $this->assertSame('pending', $household->active_status);

        $this->assertDatabaseHas('user_account', [
            'username' => $household->account_no,
            'household_id' => $household->household_id,
            'role' => 'member',
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('member', [
            'household_id' => $household->household_id,
            'full_name' => 'สมชาย ใจดี',
            'id_card' => '1234567890123',
            'relation' => 'หัวหน้าครัวเรือน',
            'is_head' => 1,
        ]);
    }
}

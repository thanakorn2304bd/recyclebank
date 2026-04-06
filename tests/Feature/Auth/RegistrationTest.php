<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesRegistrationDocumentUploads;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use CreatesRegistrationDocumentUploads;
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
            ->assertSee('สมัครสมาชิก')
            ->assertSee('ขั้นตอนที่ 1 จาก 2')
            ->assertSee('ประกาศคุ้มครองข้อมูลส่วนบุคคล');
    }

    public function test_step_one_registration_redirects_to_documents_step(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $response = $this->post('/register', [
            ...$this->validRegistrationPayload(),
        ]);

        $redirectLocation = $response->headers->get('Location');
        parse_str((string) parse_url((string) $redirectLocation, PHP_URL_QUERY), $query);

        $response
            ->assertSessionHas('auth.pending_household_registration.token');

        $this->assertNotNull($redirectLocation);
        $this->assertStringStartsWith(route('register.documents'), (string) $redirectLocation);
        $this->assertArrayHasKey('draft', $query);
        $this->assertIsString($query['draft']);
        $this->assertNotSame('', $query['draft']);

        $this->assertDatabaseCount('household', 0);
    }

    public function test_new_households_can_register_with_documents_and_stay_pending_until_approved(): void
    {
        Storage::fake('local');

        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $this->assertStringStartsWith(
            route('register.documents'),
            (string) $this->post('/register', [...$this->validRegistrationPayload()])->headers->get('Location')
        );

        $response = $this->post(route('register.complete'), [
            'member_household_copies' => [
                $this->fakeRegistrationPng('household-member-1.png'),
                $this->fakeRegistrationPng('household-member-2.png'),
            ],
            'member_national_id_copies' => [
                $this->fakeRegistrationPdf('national-id-member-1.pdf', 'National ID member 1'),
                $this->fakeRegistrationPdf('national-id-member-2.pdf', 'National ID member 2'),
            ],
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
            'id_card_last4' => '0123',
            'relation' => 'หัวหน้าครัวเรือน',
            'is_head' => 1,
        ]);

        $this->assertDatabaseHas('member', [
            'household_id' => $household->household_id,
            'full_name' => 'สมหญิง ใจดี',
            'id_card' => '9876543210123',
            'id_card_last4' => '0123',
            'relation' => 'คู่สมรส',
            'is_head' => 0,
        ]);

        $privacyNoticeId = DB::table('privacy_notice_version')->value('privacy_notice_version_id');

        $this->assertDatabaseHas('privacy_consent', [
            'user_id' => DB::table('user_account')->value('user_id'),
            'household_id' => $household->household_id,
            'privacy_notice_version_id' => $privacyNoticeId,
            'consent_type' => 'registration',
        ]);

        $documents = DB::table('household_registration_document')
            ->where('household_id', $household->household_id)
            ->get();

        $this->assertCount(4, $documents);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'household_copy',
            'member_position' => 1,
            'member_full_name' => 'สมชาย ใจดี',
            'member_relation' => 'หัวหน้าครัวเรือน',
            'member_id_card_last4' => '0123',
        ]);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'household_copy',
            'member_position' => 2,
            'member_full_name' => 'สมหญิง ใจดี',
            'member_relation' => 'คู่สมรส',
            'member_id_card_last4' => '0123',
        ]);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'national_id_copy',
            'member_position' => 1,
            'member_full_name' => 'สมชาย ใจดี',
            'member_relation' => 'หัวหน้าครัวเรือน',
            'member_id_card_last4' => '0123',
        ]);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'national_id_copy',
            'member_position' => 2,
            'member_full_name' => 'สมหญิง ใจดี',
            'member_relation' => 'คู่สมรส',
            'member_id_card_last4' => '0123',
        ]);

        foreach ($documents as $document) {
            Storage::disk('local')->assertExists($document->stored_path);
            $this->assertSame('application/pdf', $document->mime_type);
            $this->assertStringEndsWith('.pdf', $document->stored_path);
            $this->assertStringEndsWith('.pdf', $document->original_name);
            $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($document->stored_path));
        }
    }

    public function test_registration_accepts_images_by_real_file_type_even_when_filename_uses_jpg_extension(): void
    {
        Storage::fake('local');

        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $this->assertStringStartsWith(
            route('register.documents'),
            (string) $this->post('/register', [...$this->validRegistrationPayload()])->headers->get('Location')
        );

        $this->post(route('register.complete'), [
            'member_household_copies' => [
                $this->fakeRegistrationPng('household-member-1.jpg'),
                $this->fakeRegistrationPng('household-member-2.jpg'),
            ],
            'member_national_id_copies' => [
                $this->fakeRegistrationPng('national-id-member-1.jpg'),
                $this->fakeRegistrationPng('national-id-member-2.jpg'),
            ],
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $household = DB::table('household')->first();

        $this->assertNotNull($household);
        $this->assertDatabaseCount('household_registration_document', 4);

        $storedPaths = DB::table('household_registration_document')
            ->where('household_id', $household->household_id)
            ->pluck('stored_path')
            ->all();

        foreach ($storedPaths as $storedPath) {
            Storage::disk('local')->assertExists($storedPath);
            $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($storedPath));
        }
    }

    public function test_registration_requires_accepting_privacy_notice(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $response = $this->from('/register')->post('/register', [
            ...collect($this->validRegistrationPayload())
                ->except('accepted_privacy_notice')
                ->all(),
        ]);

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors('accepted_privacy_notice');

        $this->assertDatabaseCount('household', 0);
    }

    public function test_document_step_requires_pending_registration_data(): void
    {
        $this->get(route('register.documents'))
            ->assertRedirect(route('register'));
    }

    public function test_document_upload_requires_both_required_files(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $this->assertStringStartsWith(
            route('register.documents'),
            (string) $this->post('/register', [...$this->validRegistrationPayload()])->headers->get('Location')
        );

        $response = $this->from(route('register.documents'))->post(route('register.complete'), [
            'member_household_copies' => [
                $this->fakeRegistrationPng('household-member-1.png'),
            ],
            'member_national_id_copies' => [
                $this->fakeRegistrationPng('national-id-member-1.png'),
            ],
        ]);

        $response
            ->assertRedirect(route('register.documents'))
            ->assertSessionHasErrors('member_household_copies.1')
            ->assertSessionHasErrors('member_national_id_copies.1');

        $this->assertDatabaseCount('household', 0);
    }

    public function test_document_step_can_be_opened_via_draft_token_when_session_is_missing(): void
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $response = $this->post('/register', [
            ...$this->validRegistrationPayload(),
        ]);

        $redirectLocation = $response->headers->get('Location');

        $this->assertNotNull($redirectLocation);

        $this->app['session']->flush();

        $this->get((string) $redirectLocation)
            ->assertOk()
            ->assertSee('ขั้นตอนที่ 2 จาก 2')
            ->assertSee('ส่งเอกสารยืนยันตัวตน');
    }

    public function test_registration_can_complete_via_draft_token_when_session_is_missing(): void
    {
        Storage::fake('local');

        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $response = $this->post('/register', [
            ...$this->validRegistrationPayload(),
        ]);

        $redirectLocation = $response->headers->get('Location');
        parse_str((string) parse_url((string) $redirectLocation, PHP_URL_QUERY), $query);

        $this->assertArrayHasKey('draft', $query);

        $this->app['session']->flush();

        $this->post(route('register.complete'), [
            'draft_token' => $query['draft'],
            'member_household_copies' => [
                $this->fakeRegistrationPng('household-member-1.png'),
                $this->fakeRegistrationPng('household-member-2.png'),
            ],
            'member_national_id_copies' => [
                $this->fakeRegistrationPdf('national-id-member-1.pdf', 'National ID member 1'),
                $this->fakeRegistrationPdf('national-id-member-2.pdf', 'National ID member 2'),
            ],
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('household', 1);
        $this->assertDatabaseCount('household_registration_document', 4);
    }

    private function validRegistrationPayload(): array
    {
        return [
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
                [
                    'full_name' => 'สมหญิง ใจดี',
                    'id_card' => '9876543210123',
                    'relation' => 'คู่สมรส',
                    'is_head' => '0',
                ],
            ],
            'accepted_privacy_notice' => '1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Household;
use App\Models\HouseholdRegistrationDocument;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesRegistrationDocumentUploads;
use Tests\TestCase;

class RegistrationStatusTest extends TestCase
{
    use CreatesRegistrationDocumentUploads;
    use RefreshDatabase;

    public function test_registration_status_screen_redirects_to_login_when_no_tracking_session_exists(): void
    {
        $this->get(route('registration-status.show'))
            ->assertRedirect(route('login'));
    }

    public function test_pending_member_account_can_access_registration_tracking_page(): void
    {
        ['memberAccount' => $memberAccount, 'household' => $household] = $this->seedTrackedHousehold();

        $this->withSession([
            \App\Http\Controllers\Auth\RegistrationStatusController::TRACKING_SESSION_KEY => $memberAccount->user_id,
        ])->get(route('registration-status.show'))
            ->assertOk()
            ->assertSee($household->account_no)
            ->assertSee('คำขอสมัครสมาชิกอยู่ระหว่างรออนุมัติ')
            ->assertSee('ผลการพิจารณาล่าสุด')
            ->assertDontSee('ส่งเอกสารแก้ไข');
    }

    public function test_returned_member_account_can_resubmit_documents_and_move_back_to_pending(): void
    {
        Storage::fake('local');

        ['memberAccount' => $memberAccount, 'household' => $household] = $this->seedTrackedHousehold('rejected');

        $oldPaths = [];

        foreach ([
            ['document_type' => 'household_copy', 'member_position' => 1, 'original_name' => 'old-household-1.jpg', 'stored_path' => 'registration-documents/'.$household->account_no.'/old-household-1.jpg'],
            ['document_type' => 'national_id_copy', 'member_position' => 1, 'original_name' => 'old-id-1.jpg', 'stored_path' => 'registration-documents/'.$household->account_no.'/old-id-1.jpg'],
            ['document_type' => 'household_copy', 'member_position' => 2, 'original_name' => 'old-household-2.jpg', 'stored_path' => 'registration-documents/'.$household->account_no.'/old-household-2.jpg'],
            ['document_type' => 'national_id_copy', 'member_position' => 2, 'original_name' => 'old-id-2.jpg', 'stored_path' => 'registration-documents/'.$household->account_no.'/old-id-2.jpg'],
        ] as $document) {
            Storage::disk('local')->put($document['stored_path'], 'old-file');
            $oldPaths[] = $document['stored_path'];

            HouseholdRegistrationDocument::create([
                'household_id' => $household->household_id,
                'document_type' => $document['document_type'],
                'member_position' => $document['member_position'],
                'member_full_name' => $document['member_position'] === 1 ? 'สมชาย ใจดี' : 'สมหญิง ใจดี',
                'member_relation' => $document['member_position'] === 1 ? 'หัวหน้าครัวเรือน' : 'คู่สมรส',
                'member_id_card_last4' => '0123',
                'original_name' => $document['original_name'],
                'stored_path' => $document['stored_path'],
                'mime_type' => 'image/jpeg',
                'file_size' => 1024,
                'created_at' => now(),
            ]);
        }

        $this->withSession([
            \App\Http\Controllers\Auth\RegistrationStatusController::TRACKING_SESSION_KEY => $memberAccount->user_id,
        ])->from(route('registration-status.show'))
            ->patch(route('registration-status.documents.update'), [
                'member_household_copies' => [
                    $this->fakeRegistrationPng('new-household-1.png'),
                    $this->fakeRegistrationPng('new-household-2.png'),
                ],
                'member_national_id_copies' => [
                    $this->fakeRegistrationPdf('new-id-1.pdf', 'National ID 1'),
                    $this->fakeRegistrationPdf('new-id-2.pdf', 'National ID 2'),
                ],
            ])
            ->assertRedirect(route('registration-status.show'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('household', [
            'household_id' => $household->household_id,
            'active_status' => 'pending',
            'review_notes' => 'โปรดแนบเอกสารใหม่ให้ชัดเจน',
        ]);

        $this->assertDatabaseHas('user_account', [
            'user_id' => $memberAccount->user_id,
            'is_active' => 0,
        ]);

        $this->assertDatabaseCount('household_registration_document', 4);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'household_copy',
            'member_position' => 1,
            'original_name' => 'new-household-1.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'household_copy',
            'member_position' => 2,
            'original_name' => 'new-household-2.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'national_id_copy',
            'member_position' => 1,
            'original_name' => 'new-id-1.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertDatabaseHas('household_registration_document', [
            'household_id' => $household->household_id,
            'document_type' => 'national_id_copy',
            'member_position' => 2,
            'original_name' => 'new-id-2.pdf',
            'mime_type' => 'application/pdf',
        ]);

        foreach ($oldPaths as $oldPath) {
            Storage::disk('local')->assertMissing($oldPath);
        }

        $newPaths = DB::table('household_registration_document')
            ->where('household_id', $household->household_id)
            ->pluck('stored_path')
            ->all();

        foreach ($newPaths as $newPath) {
            Storage::disk('local')->assertExists($newPath);
            $this->assertStringEndsWith('.pdf', $newPath);
            $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($newPath));
        }
    }

    private function seedTrackedHousehold(string $status = 'pending'): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-registration-status',
            'password' => 'password123',
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $household = Household::create([
            'account_no' => '2026010578',
            'house_no' => '11',
            'village_no' => '2',
            'community_id' => '01',
            'phone' => '0812345678',
            'contact_person' => 'สมชาย ใจดี',
            'register_date' => '2026-04-01',
            'active_status' => $status,
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => null,
            'reviewed_by' => in_array($status, ['inactive', 'rejected']) ? $staffUser->user_id : null,
            'reviewed_at' => in_array($status, ['inactive', 'rejected']) ? now() : null,
            'review_notes' => in_array($status, ['inactive', 'rejected']) ? 'โปรดแนบเอกสารใหม่ให้ชัดเจน' : null,
        ]);

        $household->members()->createMany([
            [
                'full_name' => 'สมชาย ใจดี',
                'id_card' => '1234567890123',
                'id_card_last4' => '0123',
                'relation' => 'หัวหน้าครัวเรือน',
                'is_head' => true,
            ],
            [
                'full_name' => 'สมหญิง ใจดี',
                'id_card' => '9876543210123',
                'id_card_last4' => '0123',
                'relation' => 'คู่สมรส',
                'is_head' => false,
            ],
        ]);

        $memberAccount = UserAccount::create([
            'username' => $household->account_no,
            'password' => 'password123',
            'role' => 'member',
            'household_id' => $household->household_id,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => false,
        ]);

        return [
            'staffUser' => $staffUser,
            'household' => $household,
            'memberAccount' => $memberAccount,
        ];
    }
}

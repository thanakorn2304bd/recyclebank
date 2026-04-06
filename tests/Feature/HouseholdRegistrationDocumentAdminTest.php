<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdRegistrationDocument;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HouseholdRegistrationDocumentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_registration_documents_on_dedicated_household_documents_page(): void
    {
        Storage::fake('local');

        ['staff' => $staffUser, 'household' => $household] = $this->seedHouseholdWithDocuments();

        $this->actingAs($staffUser)
            ->get(route('households.show', $household))
            ->assertOk()
            ->assertSee(route('households.documents.index', $household), false);

        $this->actingAs($staffUser)
            ->get(route('households.documents.index', $household))
            ->assertOk()
            ->assertSee('เอกสารสมาชิกครัวเรือน')
            ->assertSee('สมาชิกในครัวเรือน')
            ->assertSee('ได้รับเอกสารแล้ว')
            ->assertSee('ดูไฟล์')
            ->assertSee('ดาวน์โหลด')
            ->assertDontSee('household-copy-1.jpg')
            ->assertDontSee('national-id-2.pdf')
            ->assertSee(route('households.documents.show', [$household, $household->registrationDocuments->first()]), false);
    }

    public function test_staff_can_open_registration_document_file(): void
    {
        Storage::fake('local');

        ['staff' => $staffUser, 'household' => $household, 'document' => $document] = $this->seedHouseholdWithDocuments();

        $this->actingAs($staffUser)
            ->get(route('households.documents.show', [$household, $document]))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_member_cannot_open_household_registration_document(): void
    {
        Storage::fake('local');

        ['member' => $memberUser, 'household' => $household, 'document' => $document] = $this->seedHouseholdWithDocuments();

        $this->actingAs($memberUser)
            ->get(route('households.documents.show', [$household, $document]))
            ->assertForbidden();
    }

    public function test_registration_document_section_only_shows_members_from_initial_registration_documents(): void
    {
        Storage::fake('local');

        ['staff' => $staffUser, 'household' => $household] = $this->seedHouseholdWithDocuments();

        $household->members()->create([
            'full_name' => 'สมาชิกเพิ่มภายหลัง',
            'id_card' => '1111222233334',
            'id_card_last4' => '3334',
            'relation' => 'บุตร',
            'is_head' => false,
        ]);

        $this->actingAs($staffUser)
            ->get(route('households.documents.index', $household))
            ->assertOk()
            ->assertSee('สมาชิกในครัวเรือน')
            ->assertDontSee('สมาชิกคนที่ 3: สมาชิกเพิ่มภายหลัง');
    }

    private function seedHouseholdWithDocuments(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่ตรวจเอกสาร',
            'phone' => '0812345678',
            'position' => 'เจ้าหน้าที่',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-documents',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $household = Household::create([
            'account_no' => '2026010666',
            'house_no' => '9/1',
            'village_no' => '2',
            'community_id' => '01',
            'phone' => '0812340000',
            'contact_person' => 'สมชาย เอกสารครบ',
            'register_date' => '2026-04-06',
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => $staffUser->user_id,
        ]);

        $household->members()->createMany([
            [
                'full_name' => 'สมชาย เอกสารครบ',
                'id_card' => '1234567890123',
                'id_card_last4' => '0123',
                'relation' => 'หัวหน้าครัวเรือน',
                'is_head' => true,
            ],
            [
                'full_name' => 'สมหญิง เอกสารครบ',
                'id_card' => '9876543210123',
                'id_card_last4' => '0123',
                'relation' => 'คู่สมรส',
                'is_head' => false,
            ],
        ]);

        $memberUser = UserAccount::create([
            'username' => $household->account_no,
            'password' => Hash::make('password123'),
            'role' => 'member',
            'household_id' => $household->household_id,
            'staff_id' => null,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $documents = collect([
            ['document_type' => 'household_copy', 'member_position' => 1, 'original_name' => 'household-copy-1.jpg', 'stored_path' => 'registration-documents/'.$household->account_no.'/household-copy-1.jpg', 'mime_type' => 'image/jpeg'],
            ['document_type' => 'national_id_copy', 'member_position' => 1, 'original_name' => 'national-id-1.pdf', 'stored_path' => 'registration-documents/'.$household->account_no.'/national-id-1.pdf', 'mime_type' => 'application/pdf'],
            ['document_type' => 'household_copy', 'member_position' => 2, 'original_name' => 'household-copy-2.jpg', 'stored_path' => 'registration-documents/'.$household->account_no.'/household-copy-2.jpg', 'mime_type' => 'image/jpeg'],
            ['document_type' => 'national_id_copy', 'member_position' => 2, 'original_name' => 'national-id-2.pdf', 'stored_path' => 'registration-documents/'.$household->account_no.'/national-id-2.pdf', 'mime_type' => 'application/pdf'],
        ])->map(function (array $document) use ($household) {
            Storage::disk('local')->put($document['stored_path'], 'mock-file-content');

            return HouseholdRegistrationDocument::create([
                'household_id' => $household->household_id,
                'document_type' => $document['document_type'],
                'member_position' => $document['member_position'],
                'member_full_name' => $document['member_position'] === 1 ? 'สมชาย เอกสารครบ' : 'สมหญิง เอกสารครบ',
                'member_relation' => $document['member_position'] === 1 ? 'หัวหน้าครัวเรือน' : 'คู่สมรส',
                'member_id_card_last4' => '0123',
                'original_name' => $document['original_name'],
                'stored_path' => $document['stored_path'],
                'mime_type' => $document['mime_type'],
                'file_size' => 2048,
                'created_at' => now(),
            ]);
        });

        return [
            'staff' => $staffUser,
            'member' => $memberUser,
            'household' => $household->fresh(['registrationDocuments', 'members']),
            'document' => $documents->first(),
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdMemberAdditionRequest;
use App\Models\HouseholdMemberAdditionRequestDocument;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesRegistrationDocumentUploads;
use Tests\TestCase;

class HouseholdMemberAdditionRequestTest extends TestCase
{
    use CreatesRegistrationDocumentUploads;
    use RefreshDatabase;

    public function test_member_can_submit_household_member_addition_request_with_documents(): void
    {
        Storage::fake('local');

        ['member' => $memberUser, 'household' => $household] = $this->seedActiveHousehold();

        $this->actingAs($memberUser)
            ->post(route('households.member-additions.store', $household), [
                'members' => [
                    [
                        'full_name' => 'เด็กชาย สมาชิกใหม่',
                        'id_card' => '1111111111111',
                        'relation' => 'บุตร',
                    ],
                    [
                        'full_name' => 'นางสาว สมาชิกใหม่',
                        'id_card' => '2222222222222',
                        'relation' => 'ญาติ',
                    ],
                ],
                'member_household_copies' => [
                    $this->fakeRegistrationPng('addition-household-1.png'),
                    $this->fakeRegistrationPng('addition-household-2.png'),
                ],
                'member_national_id_copies' => [
                    $this->fakeRegistrationPdf('addition-id-1.pdf', 'Addition ID 1'),
                    $this->fakeRegistrationPdf('addition-id-2.pdf', 'Addition ID 2'),
                ],
            ])
            ->assertRedirect(route('households.show', $household))
            ->assertSessionHas('success');

        $memberAdditionRequest = HouseholdMemberAdditionRequest::query()->first();

        $this->assertNotNull($memberAdditionRequest);
        $this->assertSame('pending', $memberAdditionRequest->status);
        $this->assertSame($household->household_id, $memberAdditionRequest->household_id);
        $this->assertSame($memberUser->user_id, $memberAdditionRequest->requested_by);
        $this->assertDatabaseCount('household_member_addition_request_member', 2);
        $this->assertDatabaseCount('household_member_addition_request_document', 4);
        $this->assertDatabaseCount('member', 2);

        $this->assertDatabaseHas('household_member_addition_request_member', [
            'member_addition_request_id' => $memberAdditionRequest->member_addition_request_id,
            'full_name' => 'เด็กชาย สมาชิกใหม่',
            'id_card' => '1111111111111',
            'id_card_last4' => '1111',
            'relation' => 'บุตร',
        ]);

        $documents = HouseholdMemberAdditionRequestDocument::query()
            ->where('member_addition_request_id', $memberAdditionRequest->member_addition_request_id)
            ->get();

        foreach ($documents as $document) {
            Storage::disk('local')->assertExists($document->stored_path);
            $this->assertSame('application/pdf', $document->mime_type);
            $this->assertStringEndsWith('.pdf', $document->stored_path);
            $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($document->stored_path));
        }
    }

    public function test_member_can_submit_member_addition_request_when_real_image_type_differs_from_filename_extension(): void
    {
        Storage::fake('local');

        ['member' => $memberUser, 'household' => $household] = $this->seedActiveHousehold();

        $this->actingAs($memberUser)
            ->post(route('households.member-additions.store', $household), [
                'members' => [
                    [
                        'full_name' => 'เด็กหญิง ไฟล์แปลกนิดหน่อย',
                        'id_card' => '3333333333333',
                        'relation' => 'บุตร',
                    ],
                ],
                'member_household_copies' => [
                    $this->fakeRegistrationPng('addition-household-1.jpg'),
                ],
                'member_national_id_copies' => [
                    $this->fakeRegistrationPng('addition-id-1.jpg'),
                ],
            ])
            ->assertRedirect(route('households.show', $household))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('household_member_addition_request', 1);
        $this->assertDatabaseCount('household_member_addition_request_document', 2);

        $storedPaths = HouseholdMemberAdditionRequestDocument::query()
            ->pluck('stored_path')
            ->all();

        foreach ($storedPaths as $storedPath) {
            Storage::disk('local')->assertExists($storedPath);
            $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($storedPath));
        }
    }

    public function test_member_cannot_submit_member_addition_request_while_pending_request_exists(): void
    {
        Storage::fake('local');

        ['member' => $memberUser, 'household' => $household] = $this->seedActiveHousehold();

        HouseholdMemberAdditionRequest::create([
            'household_id' => $household->household_id,
            'requested_by' => $memberUser->user_id,
            'status' => 'pending',
        ]);

        $this->actingAs($memberUser)
            ->from(route('households.member-additions.create', $household))
            ->post(route('households.member-additions.store', $household), [
                'members' => [
                    [
                        'full_name' => 'เด็กหญิง รอคิว',
                        'id_card' => '3333333333333',
                        'relation' => 'บุตร',
                    ],
                ],
                'member_household_copies' => [
                    $this->fakeRegistrationPng('blocked-household.png'),
                ],
                'member_national_id_copies' => [
                    $this->fakeRegistrationPdf('blocked-id.pdf', 'Blocked ID'),
                ],
            ])
            ->assertRedirect(route('households.member-additions.create', $household))
            ->assertSessionHasErrors('members');

        $this->assertDatabaseCount('household_member_addition_request', 1);
        $this->assertDatabaseCount('household_member_addition_request_member', 0);
    }

    public function test_staff_can_view_member_addition_request_documents_on_dedicated_documents_page(): void
    {
        Storage::fake('local');

        [
            'staff' => $staffUser,
            'household' => $household,
            'memberAdditionRequest' => $memberAdditionRequest,
            'document' => $document,
        ] = $this->seedHouseholdWithPendingMemberAdditionRequest();

        $this->actingAs($staffUser)
            ->get(route('households.show', $household))
            ->assertOk()
            ->assertSee('คำขอเพิ่มสมาชิกในครัวเรือน')
            ->assertSee(route('households.documents.index', $household), false);

        $this->actingAs($staffUser)
            ->get(route('households.documents.index', $household))
            ->assertOk()
            ->assertSee('สมาชิกที่รอดำเนินการ')
            ->assertSee('เด็กชาย สมาชิกใหม่')
            ->assertSee(
                route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $document]),
                false
            );

        $this->actingAs($staffUser)
            ->get(route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $document]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_staff_can_approve_member_addition_request_and_add_members_to_household(): void
    {
        [
            'staff' => $staffUser,
            'household' => $household,
            'memberAdditionRequest' => $memberAdditionRequest,
        ] = $this->seedHouseholdWithPendingMemberAdditionRequest(false);

        $this->actingAs($staffUser)
            ->patch(route('households.member-additions.review', [$household, $memberAdditionRequest]), [
                'decision' => 'approved',
                'review_notes' => 'ตรวจสอบเอกสารครบถ้วน',
            ])
            ->assertRedirect(route('households.show', $household))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('household_member_addition_request', [
            'member_addition_request_id' => $memberAdditionRequest->member_addition_request_id,
            'status' => 'approved',
            'reviewed_by' => $staffUser->user_id,
            'review_notes' => 'ตรวจสอบเอกสารครบถ้วน',
        ]);

        $this->assertDatabaseCount('member', 4);
        $this->assertDatabaseHas('member', [
            'household_id' => $household->household_id,
            'full_name' => 'เด็กชาย สมาชิกใหม่',
            'id_card' => '1111111111111',
            'id_card_last4' => '1111',
            'relation' => 'บุตร',
            'is_head' => 0,
        ]);
        $this->assertDatabaseHas('member', [
            'household_id' => $household->household_id,
            'full_name' => 'นางสาว สมาชิกใหม่',
            'id_card' => '2222222222222',
            'id_card_last4' => '2222',
            'relation' => 'ญาติ',
            'is_head' => 0,
        ]);
    }

    public function test_staff_can_still_see_documents_from_older_member_addition_requests_when_new_request_exists(): void
    {
        Storage::fake('local');

        ['staff' => $staffUser, 'member' => $memberUser, 'household' => $household] = $this->seedActiveHousehold();

        $approvedRequest = HouseholdMemberAdditionRequest::create([
            'household_id' => $household->household_id,
            'requested_by' => $memberUser->user_id,
            'status' => 'approved',
            'review_notes' => 'อนุมัติคำขอเดิมแล้ว',
            'reviewed_by' => $staffUser->user_id,
            'reviewed_at' => now()->subHour(),
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHour(),
        ]);

        $approvedRequest->requestedMembers()->create([
            'full_name' => 'สมาชิกเดิมจากคำขอเก่า',
            'id_card' => '3333333333333',
            'id_card_last4' => '3333',
            'id_card_hash' => hash('sha256', '3333333333333'),
            'relation' => 'บุตร',
        ]);

        $household->members()->create([
            'full_name' => 'สมาชิกเดิมจากคำขอเก่า',
            'id_card' => '3333333333333',
            'id_card_last4' => '3333',
            'id_card_hash' => hash('sha256', '3333333333333'),
            'relation' => 'บุตร',
            'is_head' => false,
        ]);

        $approvedDocument = HouseholdMemberAdditionRequestDocument::create([
            'member_addition_request_id' => $approvedRequest->member_addition_request_id,
            'document_type' => 'household_copy',
            'member_position' => 1,
            'member_full_name' => 'สมาชิกเดิมจากคำขอเก่า',
            'member_relation' => 'บุตร',
            'member_id_card_last4' => '3333',
            'original_name' => 'approved-request-household.pdf',
            'stored_path' => 'household-member-addition-documents/'.$household->account_no.'/request-'.$approvedRequest->member_addition_request_id.'/approved-request-household.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
        ]);

        Storage::disk('local')->put($approvedDocument->stored_path, '%PDF-approved-request-document');

        $pendingRequest = HouseholdMemberAdditionRequest::create([
            'household_id' => $household->household_id,
            'requested_by' => $memberUser->user_id,
            'status' => 'pending',
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        $pendingRequest->requestedMembers()->create([
            'full_name' => 'สมาชิกใหม่ล่าสุด',
            'id_card' => '4444444444444',
            'id_card_last4' => '4444',
            'id_card_hash' => hash('sha256', '4444444444444'),
            'relation' => 'ญาติ',
        ]);

        $pendingDocument = HouseholdMemberAdditionRequestDocument::create([
            'member_addition_request_id' => $pendingRequest->member_addition_request_id,
            'document_type' => 'household_copy',
            'member_position' => 1,
            'member_full_name' => 'สมาชิกใหม่ล่าสุด',
            'member_relation' => 'ญาติ',
            'member_id_card_last4' => '4444',
            'original_name' => 'pending-request-household.pdf',
            'stored_path' => 'household-member-addition-documents/'.$household->account_no.'/request-'.$pendingRequest->member_addition_request_id.'/pending-request-household.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
        ]);

        Storage::disk('local')->put($pendingDocument->stored_path, '%PDF-pending-request-document');

        $this->actingAs($staffUser)
            ->get(route('households.documents.index', $household))
            ->assertOk()
            ->assertSee('เอกสารสมาชิกครัวเรือน')
            ->assertSee('สมาชิกเดิมจากคำขอเก่า')
            ->assertSee('คำขอเพิ่มสมาชิก #'.$pendingRequest->member_addition_request_id)
            ->assertDontSee('คำขอเพิ่มสมาชิก #'.$approvedRequest->member_addition_request_id)
            ->assertSee(
                route('households.member-additions.documents.show', [$household, $approvedRequest, $approvedDocument]),
                false
            )
            ->assertSee(
                route('households.member-additions.documents.show', [$household, $pendingRequest, $pendingDocument]),
                false
            );
    }

    private function seedActiveHousehold(): array
    {
        DB::table('community')->insert([
            'community_id' => '01',
            'community_name' => 'North Community',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'full_name' => 'เจ้าหน้าที่สมาชิก',
            'phone' => '0812345678',
            'position' => 'เจ้าหน้าที่',
        ]);

        $staffUser = UserAccount::create([
            'username' => 'staff-member-addition',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'household_id' => null,
            'staff_id' => $staffId,
            'created_at' => now(),
            'last_login' => null,
            'is_active' => true,
        ]);

        $household = Household::create([
            'account_no' => '2026010888',
            'house_no' => '88/1',
            'village_no' => '3',
            'community_id' => '01',
            'phone' => '0811111111',
            'contact_person' => 'สมชาย ครัวเรือน',
            'register_date' => '2026-04-06',
            'active_status' => 'active',
            'accumulated_months' => 0,
            'total_balance' => 0,
            'created_by' => $staffUser->user_id,
            'reviewed_by' => $staffUser->user_id,
            'reviewed_at' => now(),
            'review_notes' => 'อนุมัติใช้งานแล้ว',
        ]);

        $household->members()->createMany([
            [
                'full_name' => 'สมชาย ครัวเรือน',
                'id_card' => '1234567890123',
                'id_card_last4' => '0123',
                'id_card_hash' => hash('sha256', '1234567890123'),
                'relation' => 'หัวหน้าครัวเรือน',
                'is_head' => true,
            ],
            [
                'full_name' => 'สมหญิง ครัวเรือน',
                'id_card' => '9876543210123',
                'id_card_last4' => '0123',
                'id_card_hash' => hash('sha256', '9876543210123'),
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

        return [
            'staff' => $staffUser,
            'member' => $memberUser,
            'household' => $household->fresh(['members']),
        ];
    }

    private function seedHouseholdWithPendingMemberAdditionRequest(bool $withDocuments = true): array
    {
        Storage::fake('local');

        ['staff' => $staffUser, 'member' => $memberUser, 'household' => $household] = $this->seedActiveHousehold();

        $memberAdditionRequest = HouseholdMemberAdditionRequest::create([
            'household_id' => $household->household_id,
            'requested_by' => $memberUser->user_id,
            'status' => 'pending',
        ]);

        $memberAdditionRequest->requestedMembers()->createMany([
            [
                'full_name' => 'เด็กชาย สมาชิกใหม่',
                'id_card' => '1111111111111',
                'id_card_last4' => '1111',
                'id_card_hash' => hash('sha256', '1111111111111'),
                'relation' => 'บุตร',
            ],
            [
                'full_name' => 'นางสาว สมาชิกใหม่',
                'id_card' => '2222222222222',
                'id_card_last4' => '2222',
                'id_card_hash' => hash('sha256', '2222222222222'),
                'relation' => 'ญาติ',
            ],
        ]);

        $document = null;

        if ($withDocuments) {
            $document = HouseholdMemberAdditionRequestDocument::create([
                'member_addition_request_id' => $memberAdditionRequest->member_addition_request_id,
                'document_type' => 'household_copy',
                'member_position' => 1,
                'member_full_name' => 'เด็กชาย สมาชิกใหม่',
                'member_relation' => 'บุตร',
                'member_id_card_last4' => '1111',
                'original_name' => 'member-addition-household-1.pdf',
                'stored_path' => 'household-member-addition-documents/'.$household->account_no.'/request-'.$memberAdditionRequest->member_addition_request_id.'/member-addition-household-1.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048,
            ]);

            Storage::disk('local')->put($document->stored_path, '%PDF-fake-member-addition-document');

            HouseholdMemberAdditionRequestDocument::create([
                'member_addition_request_id' => $memberAdditionRequest->member_addition_request_id,
                'document_type' => 'national_id_copy',
                'member_position' => 1,
                'member_full_name' => 'เด็กชาย สมาชิกใหม่',
                'member_relation' => 'บุตร',
                'member_id_card_last4' => '1111',
                'original_name' => 'member-addition-id-1.pdf',
                'stored_path' => 'household-member-addition-documents/'.$household->account_no.'/request-'.$memberAdditionRequest->member_addition_request_id.'/member-addition-id-1.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048,
            ]);

            HouseholdMemberAdditionRequestDocument::create([
                'member_addition_request_id' => $memberAdditionRequest->member_addition_request_id,
                'document_type' => 'household_copy',
                'member_position' => 2,
                'member_full_name' => 'นางสาว สมาชิกใหม่',
                'member_relation' => 'ญาติ',
                'member_id_card_last4' => '2222',
                'original_name' => 'member-addition-household-2.pdf',
                'stored_path' => 'household-member-addition-documents/'.$household->account_no.'/request-'.$memberAdditionRequest->member_addition_request_id.'/member-addition-household-2.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048,
            ]);

            HouseholdMemberAdditionRequestDocument::create([
                'member_addition_request_id' => $memberAdditionRequest->member_addition_request_id,
                'document_type' => 'national_id_copy',
                'member_position' => 2,
                'member_full_name' => 'นางสาว สมาชิกใหม่',
                'member_relation' => 'ญาติ',
                'member_id_card_last4' => '2222',
                'original_name' => 'member-addition-id-2.pdf',
                'stored_path' => 'household-member-addition-documents/'.$household->account_no.'/request-'.$memberAdditionRequest->member_addition_request_id.'/member-addition-id-2.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048,
            ]);
        }

        return [
            'staff' => $staffUser,
            'member' => $memberUser,
            'household' => $household->fresh(['members']),
            'memberAdditionRequest' => $memberAdditionRequest->fresh(['requestedMembers', 'documents']),
            'document' => $document,
        ];
    }
}

<?php

namespace App\Support\Households;

use App\Models\Household;
use App\Models\HouseholdMemberAdditionRequest;
use App\Models\HouseholdMemberAdditionRequestDocument;
use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class HouseholdMemberAdditionService
{

    public function documentRequirements(): array
    {
        return [
            'member_household_copies' => [
                'document_type' => 'household_copy',
                'label' => 'สำเนาทะเบียนบ้านของสมาชิก',
                'description' => 'อัปโหลดสำเนาทะเบียนบ้านของสมาชิกใหม่ทุกคนเพื่อยืนยันว่ามีชื่ออยู่ในทะเบียนบ้าน',
            ],
            'member_national_id_copies' => [
                'document_type' => 'national_id_copy',
                'label' => 'สำเนาบัตรประจำตัวประชาชนของสมาชิก',
                'description' => 'อัปโหลดสำเนาบัตรประชาชนของสมาชิกใหม่ทุกคนเพื่อให้เจ้าหน้าที่ตรวจสอบข้อมูลก่อนอนุมัติ',
            ],
        ];
    }

    public function normalizedMembers(iterable $members): array
    {
        return collect($members)
            ->map(function ($member, int $index) {
                $fullName = trim((string) data_get($member, 'full_name', ''));
                $relation = trim((string) data_get($member, 'relation', ''));
                $idCardLast4 = trim((string) data_get($member, 'id_card_last4', ''));

                return [
                    'position' => $index + 1,
                    'full_name' => $fullName,
                    'relation' => $relation,
                    'id_card_last4' => $idCardLast4,
                    'display_name' => $fullName !== '' ? $fullName : 'สมาชิกคนที่ '.($index + 1),
                    'display_meta' => collect([
                        $relation !== '' ? $relation : null,
                        $idCardLast4 !== '' ? 'เลขบัตรลงท้าย '.$idCardLast4 : null,
                    ])->filter()->implode(' | '),
                ];
            })
            ->values()
            ->all();
    }

    public function openRequestFor(Household $household): ?HouseholdMemberAdditionRequest
    {
        return $household->memberAdditionRequests()
            ->where('status', 'pending')
            ->latest('member_addition_request_id')
            ->first();
    }

    public function latestRequestFor(Household $household): ?HouseholdMemberAdditionRequest
    {
        return $household->memberAdditionRequests()
            ->latest('member_addition_request_id')
            ->first();
    }

    public function documentLookup(HouseholdMemberAdditionRequest $memberAdditionRequest): Collection
    {
        $memberAdditionRequest->loadMissing('documents');

        return $memberAdditionRequest->documents
            ->sortBy([
                ['member_position', 'asc'],
                ['document_type', 'asc'],
            ])
            ->keyBy(fn (HouseholdMemberAdditionRequestDocument $document) => $document->member_position.'-'.$document->document_type);
    }

    public function createRequest(
        Household $household,
        array $members,
        array $documentUploads,
        int $requestedBy
    ): HouseholdMemberAdditionRequest {
        $this->ensureNoPendingRequest($household);
        $this->ensureMembersCanBeAdded($household, $members);

        $storedDocuments = [];

        try {
            $memberAdditionRequest = DB::transaction(function () use (
                $household,
                $members,
                $documentUploads,
                $requestedBy,
                &$storedDocuments
            ): HouseholdMemberAdditionRequest {
                $memberAdditionRequest = HouseholdMemberAdditionRequest::create([
                    'household_id' => $household->household_id,
                    'requested_by' => $requestedBy,
                    'status' => 'pending',
                ]);

                $memberAdditionRequest->requestedMembers()->createMany(
                    collect($members)
                        ->map(fn (array $member) => [
                            'full_name' => $member['full_name'],
                            'id_card' => $member['id_card'],
                            'id_card_last4' => $member['id_card_last4'],
                            'id_card_hash' => $member['id_card_hash'],
                            'relation' => $member['relation'],
                        ])
                        ->all()
                );

                $storedDocuments = $this->storeUploadedDocuments(
                    $household,
                    $memberAdditionRequest,
                    $members,
                    $documentUploads
                );

                $memberAdditionRequest->documents()->createMany($storedDocuments);

                return $memberAdditionRequest;
            });
        } catch (Throwable $throwable) {
            $this->deleteStoredFiles($storedDocuments);

            throw $throwable;
        }

        return $memberAdditionRequest->fresh([
            'requestedMembers',
            'documents',
            'requestedByUser',
            'reviewedByUser.staff',
        ]);
    }

    public function review(
        HouseholdMemberAdditionRequest $memberAdditionRequest,
        string $decision,
        ?string $reviewNotes,
        int $reviewedBy
    ): array {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'decision' => 'ผลการพิจารณาคำขอเพิ่มสมาชิกไม่ถูกต้อง',
            ]);
        }

        if ($memberAdditionRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'decision' => 'คำขอนี้ถูกพิจารณาไปแล้ว ไม่สามารถบันทึกผลซ้ำได้',
            ]);
        }

        $memberAdditionRequest->loadMissing('household.members', 'requestedMembers');

        $before = $this->reviewSnapshot($memberAdditionRequest);

        DB::transaction(function () use ($memberAdditionRequest, $decision, $reviewNotes, $reviewedBy): void {
            if ($decision === 'approved') {
                $this->ensureMembersCanBeAdded(
                    $memberAdditionRequest->household,
                    $memberAdditionRequest->requestedMembers
                        ->map(fn ($member) => [
                            'full_name' => (string) $member->full_name,
                            'id_card' => (string) $member->id_card,
                            'id_card_last4' => (string) ($member->id_card_last4 ?? ''),
                            'id_card_hash' => (string) ($member->id_card_hash ?? ''),
                            'relation' => (string) $member->relation,
                        ])
                        ->all()
                );

                $memberAdditionRequest->household->members()->createMany(
                    $memberAdditionRequest->requestedMembers
                        ->map(fn ($member) => [
                            'full_name' => $member->full_name,
                            'id_card' => $member->id_card,
                            'id_card_last4' => $member->id_card_last4,
                            'id_card_hash' => $member->id_card_hash,
                            'relation' => $member->relation,
                            'is_head' => false,
                        ])
                        ->all()
                );
            }

            $memberAdditionRequest->forceFill([
                'status' => $decision,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
                'review_notes' => $reviewNotes,
            ])->save();
        });

        $memberAdditionRequest = $memberAdditionRequest->fresh([
            'requestedMembers',
            'documents',
            'requestedByUser',
            'reviewedByUser.staff',
            'household.members',
        ]);

        return [
            'memberAdditionRequest' => $memberAdditionRequest,
            'before' => $before,
            'after' => $this->reviewSnapshot($memberAdditionRequest),
            'added_member_count' => $decision === 'approved'
                ? $memberAdditionRequest->requestedMembers->count()
                : 0,
        ];
    }

    public function deleteStoredFiles(array $storedDocuments): void
    {
        $paths = collect($storedDocuments)
            ->map(fn ($document) => $document['stored_path'] ?? null)
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::delete($paths);
        }
    }

    private function storeUploadedDocuments(
        Household $household,
        HouseholdMemberAdditionRequest $memberAdditionRequest,
        array $members,
        array $documentUploads
    ): array {
        $storedDocuments = [];
        $baseDirectory = 'household-member-addition-documents/'.$household->account_no.'/request-'.$memberAdditionRequest->member_addition_request_id;
        $timestamp = now()->format('YmdHis');
        $normalizedMembers = $this->normalizedMembers($members);

        foreach ($normalizedMembers as $index => $member) {
            $memberHouseholdCopy = data_get($documentUploads, 'member_household_copies.'.$index);

            if ($memberHouseholdCopy instanceof UploadedFile) {
                $storedDocuments[] = $this->storeUploadedDocument(
                    $memberHouseholdCopy,
                    $baseDirectory,
                    $timestamp,
                    'household_copy',
                    $member,
                    $index + 1,
                );
            }

            $memberNationalIdCopy = data_get($documentUploads, 'member_national_id_copies.'.$index);

            if (! $memberNationalIdCopy instanceof UploadedFile) {
                continue;
            }

            $storedDocuments[] = $this->storeUploadedDocument(
                $memberNationalIdCopy,
                $baseDirectory,
                $timestamp,
                'national_id_copy',
                $member,
                $index + 1,
            );
        }

        return $storedDocuments;
    }

    private function storeUploadedDocument(
        UploadedFile $uploadedFile,
        string $baseDirectory,
        string $timestamp,
        string $documentType,
        array $member,
        int $memberPosition,
    ): array {
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension() ?: 'bin');
        $storedPath = $baseDirectory.'/'.$this->storedFileName($documentType, $memberPosition, $timestamp, $extension);

        if (! Storage::put($storedPath, file_get_contents($uploadedFile->getRealPath()))) {
            throw new \RuntimeException('ไม่สามารถบันทึกไฟล์เอกสารคำขอเพิ่มสมาชิกได้');
        }

        return [
            'document_type' => $documentType,
            'member_position' => $memberPosition,
            'member_full_name' => $member['full_name'] !== '' ? $member['full_name'] : null,
            'member_relation' => $member['relation'] !== '' ? $member['relation'] : null,
            'member_id_card_last4' => $member['id_card_last4'] !== '' ? $member['id_card_last4'] : null,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $uploadedFile->getMimeType() ?: $uploadedFile->getClientMimeType(),
            'file_size' => $uploadedFile->getSize(),
        ];
    }

    private function storedFileName(string $documentType, int $memberPosition, string $timestamp, string $extension): string
    {
        $prefix = $documentType === 'household_copy'
            ? 'household-copy-member-'
            : 'national-id-copy-member-';

        return $prefix.$memberPosition.'-'.$timestamp.'.'.$extension;
    }

    private function ensureNoPendingRequest(Household $household): void
    {
        if ($this->openRequestFor($household)) {
            throw ValidationException::withMessages([
                'members' => 'ขณะนี้ครัวเรือนนี้มีคำขอเพิ่มสมาชิกที่อยู่ระหว่างรอเจ้าหน้าที่พิจารณาอยู่แล้ว',
            ]);
        }
    }

    private function ensureMembersCanBeAdded(Household $household, array $members): void
    {
        $household->loadMissing('members');

        $existingIds = $household->members
            ->map(fn ($member) => Member::normalizeIdCard((string) $member->id_card))
            ->filter()
            ->values()
            ->all();

        $duplicateNames = collect($members)
            ->filter(fn ($member) => in_array((string) ($member['id_card'] ?? ''), $existingIds, true))
            ->pluck('full_name')
            ->filter()
            ->values()
            ->all();

        if ($duplicateNames !== []) {
            throw ValidationException::withMessages([
                'members' => 'ไม่สามารถเพิ่มสมาชิกซ้ำในครัวเรือนได้ พบเลขบัตรประชาชนที่มีอยู่แล้วของ '.implode(', ', $duplicateNames),
            ]);
        }
    }

    private function reviewSnapshot(HouseholdMemberAdditionRequest $memberAdditionRequest): array
    {
        return [
            'member_addition_request_id' => (int) $memberAdditionRequest->member_addition_request_id,
            'household_id' => (int) $memberAdditionRequest->household_id,
            'status' => (string) $memberAdditionRequest->status,
            'reviewed_by' => $memberAdditionRequest->reviewed_by !== null ? (int) $memberAdditionRequest->reviewed_by : null,
            'reviewed_at' => $memberAdditionRequest->reviewed_at?->format('Y-m-d H:i:s'),
            'review_notes' => $memberAdditionRequest->review_notes,
        ];
    }
}

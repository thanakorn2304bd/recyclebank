<?php

namespace App\Support\Households;

use App\Models\Household;
use App\Models\HouseholdRegistrationDocument;
use App\Support\Storage\DocumentStorage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class RegistrationDocumentService
{
    public function __construct(private readonly DocumentStorage $documentStorage) {}

    public function documentRequirements(): array
    {
        return [
            'member_household_copies' => [
                'document_type' => 'household_copy',
                'label' => 'สำเนาทะเบียนบ้านของสมาชิก',
                'description' => 'อัปโหลดสำเนาทะเบียนบ้านแยกตามสมาชิกทุกคนเพื่อยืนยันว่ามีชื่ออยู่ในทะเบียนบ้าน',
            ],
            'member_national_id_copies' => [
                'document_type' => 'national_id_copy',
                'label' => 'สำเนาบัตรประจำตัวประชาชนของสมาชิก',
                'description' => 'อัปโหลดสำเนาบัตรประจำตัวประชาชนแยกตามสมาชิกทุกคนที่บันทึกไว้',
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

    public function documentMembers(Household $household): array
    {
        $household->loadMissing('registrationDocuments');

        $documentMembers = $household->registrationDocuments
            ->sortBy([
                ['member_position', 'asc'],
                ['document_type', 'asc'],
            ])
            ->groupBy('member_position')
            ->map(function (Collection $documents, int|string $position) {
                /** @var HouseholdRegistrationDocument|null $primaryDocument */
                $primaryDocument = $documents->firstWhere('document_type', 'household_copy')
                    ?? $documents->firstWhere('document_type', 'national_id_copy')
                    ?? $documents->first();

                $memberPosition = (int) $position;
                $fullName = trim((string) ($primaryDocument?->member_full_name ?? ''));
                $relation = trim((string) ($primaryDocument?->member_relation ?? ''));
                $idCardLast4 = trim((string) ($primaryDocument?->member_id_card_last4 ?? ''));

                return [
                    'position' => $memberPosition,
                    'full_name' => $fullName,
                    'relation' => $relation,
                    'id_card_last4' => $idCardLast4,
                    'display_name' => $fullName !== '' ? $fullName : 'สมาชิกคนที่ '.$memberPosition,
                    'display_meta' => collect([
                        $relation !== '' ? $relation : null,
                        $idCardLast4 !== '' ? 'เลขบัตรลงท้าย '.$idCardLast4 : null,
                    ])->filter()->implode(' | '),
                ];
            })
            ->values()
            ->all();

        if ($documentMembers !== []) {
            return $documentMembers;
        }

        return $this->normalizedMembers($household->members);
    }

    public function storeUploadedDocuments(Request $request, string $accountNo, iterable $members): array
    {
        $storedDocuments = [];
        $baseDirectory = 'registration-documents/'.$accountNo;
        $timestamp = now()->format('YmdHis');

        foreach ($this->normalizedMembers($members) as $index => $member) {
            $memberHouseholdCopy = $request->file('member_household_copies.'.$index);

            if ($memberHouseholdCopy !== null) {
                $storedDocuments[] = $this->storeUploadedDocument(
                    $memberHouseholdCopy,
                    'household_copy',
                    $member,
                    $index + 1,
                    $baseDirectory,
                    $timestamp,
                );
            }

            $memberNationalIdCopy = $request->file('member_national_id_copies.'.$index);

            if ($memberNationalIdCopy === null) {
                continue;
            }

            $storedDocuments[] = $this->storeUploadedDocument(
                $memberNationalIdCopy,
                'national_id_copy',
                $member,
                $index + 1,
                $baseDirectory,
                $timestamp,
            );
        }

        return $storedDocuments;
    }

    public function attachStoredDocuments(Household $household, array $storedDocuments): void
    {
        foreach ($storedDocuments as $document) {
            HouseholdRegistrationDocument::create([
                'household_id' => $household->household_id,
                'document_type' => $document['document_type'],
                'member_position' => $document['member_position'],
                'member_full_name' => $document['member_full_name'],
                'member_relation' => $document['member_relation'],
                'member_id_card_last4' => $document['member_id_card_last4'],
                'original_name' => $document['original_name'],
                'stored_path' => $document['stored_path'],
                'mime_type' => $document['mime_type'],
                'file_size' => $document['file_size'],
                'created_at' => now(),
            ]);
        }
    }

    public function storedPathsForHousehold(Household $household): array
    {
        return $household->registrationDocuments()
            ->pluck('stored_path')
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();
    }

    public function deleteDocumentRecords(Household $household): void
    {
        $household->registrationDocuments()->delete();
    }

    public function deleteStoredFiles(array $storedDocuments): void
    {
        $paths = collect($storedDocuments)
            ->map(fn ($document) => $document['stored_path'] ?? null)
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();

        if ($paths !== []) {
            $this->documentStorage->delete($paths);
        }
    }

    public function documentLookup(Household $household): Collection
    {
        $household->loadMissing('registrationDocuments');

        return $household->registrationDocuments
            ->sortBy([
                ['member_position', 'asc'],
                ['document_type', 'asc'],
            ])
            ->keyBy(fn (HouseholdRegistrationDocument $document) => $document->member_position.'-'.$document->document_type);
    }

    private function storeUploadedDocument(
        UploadedFile $uploadedFile,
        string $documentType,
        array $member,
        int $memberPosition,
        string $baseDirectory,
        string $timestamp,
    ): array {
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension() ?: 'bin');
        $pathname = $baseDirectory.'/'.$this->storedFileName($documentType, $memberPosition, $timestamp, $extension);
        $mimeType = $uploadedFile->getMimeType() ?: $uploadedFile->getClientMimeType();

        $storedPath = $this->documentStorage->put(
            $pathname,
            file_get_contents($uploadedFile->getRealPath()),
            $mimeType,
        );

        return [
            'document_type' => $documentType,
            'member_position' => $memberPosition,
            'member_full_name' => $member['full_name'] !== '' ? $member['full_name'] : null,
            'member_relation' => $member['relation'] !== '' ? $member['relation'] : null,
            'member_id_card_last4' => $member['id_card_last4'] !== '' ? $member['id_card_last4'] : null,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $mimeType,
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
}

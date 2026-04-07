<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickSearchHouseholdRequest;
use App\Http\Requests\ReviewHouseholdRequest;
use App\Http\Requests\StoreHouseholdCredentialsRequest;
use App\Http\Requests\StoreHouseholdRequest;
use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\Community;
use App\Models\Household;
use App\Models\HouseholdRegistrationDocument;
use App\Models\Member;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Households\HouseholdMemberAdditionService;
use App\Support\Households\HouseholdService;
use App\Support\Households\HouseholdViewDataFactory;
use App\Support\Households\RegistrationDocumentService;
use App\Support\Transactions\HouseholdTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HouseholdController extends Controller
{
    public function index(Request $request, HouseholdService $householdService)
    {
        if ($this->isMember()) {
            $memberHouseholdId = $this->memberHouseholdId();

            if ($memberHouseholdId) {
                return redirect()->route('households.show', ['household' => $memberHouseholdId]);
            }

            return redirect()->route('main-menu')
                ->withErrors('ไม่พบบัญชีครัวเรือนของผู้ใช้นี้');
        }

        $q = $request->string('q')->toString();
        $communityId = $request->string('community_id')->toString();
        $status = $request->string('status')->toString();
        $memberAddition = $request->string('member_addition')->toString();

        $householdsQuery = Household::query()
            ->with('community')
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($sub) use ($q) {
                    $sub->where('account_no', 'like', "%{$q}%")
                        ->orWhere('house_no', 'like', "%{$q}%")
                        ->orWhere('contact_person', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('full_name', 'like', "%{$q}%"));
                });
            })
            ->when($communityId, fn ($qb) => $qb->where('community_id', $communityId))
            ->when($status, fn ($qb) => $qb->where('active_status', $status))
            ->when($memberAddition === 'pending', function ($qb) {
                $qb->whereHas('memberAdditionRequests', fn ($requestQuery) => $requestQuery->where('status', 'pending'));
            });

        $households = $householdsQuery
            ->orderBy('created_at')
            ->orderBy('household_id')
            ->paginate(15)
            ->withQueryString();

        $communities = Community::orderBy('community_id')->get();
        [
            'activeCount' => $activeCount,
            'pendingCount' => $pendingCount,
            'inactiveCount' => $inactiveCount,
        ] = $householdService->indexViewMetrics($households);
        $isPrivileged = ! $this->isMember();

        return view('households.index', compact(
            'households',
            'communities',
            'q',
            'communityId',
            'status',
            'memberAddition',
            'activeCount',
            'pendingCount',
            'inactiveCount',
            'isPrivileged'
        ));
    }

    public function create(Request $request, HouseholdViewDataFactory $householdViewDataFactory)
    {
        $communities = Community::orderBy('community_id')->get();
        $oldMembers = $householdViewDataFactory->oldMembers($request->old('members', []));

        return view('households.create', compact('communities', 'oldMembers'));
    }

    public function store(
        StoreHouseholdRequest $request,
        CurrentUserIdResolver $currentUserIdResolver,
        HouseholdService $householdService
    ) {
        $data = $request->householdAttributes();
        $members = $request->members();

        $data['total_balance'] = 0.00;
        $data['created_by'] = $currentUserIdResolver->resolve($request);
        $household = $householdService->create($data, $members);

        ActivityLogger::forCurrentUser(
            'households',
            "สร้างครัวเรือน {$household->account_no} ({$household->contact_person})"
        );

        return redirect()->route('households.credentials.create', $household)
            ->with('success', 'บันทึกข้อมูลครัวเรือนแล้ว กรุณาตั้งรหัสผ่านสำหรับเข้าใช้งาน');
    }

    public function edit(
        Request $request,
        Household $household,
        HouseholdViewDataFactory $householdViewDataFactory
    ) {
        $communities = Community::orderBy('community_id')->get();
        $household->load([
            'members' => fn ($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
        ]);
        $oldMembers = $request->old('members')
            ? $householdViewDataFactory->oldMembers($request->old('members', []))
            : $householdViewDataFactory->membersForEdit($household);

        return view('households.edit', compact('household', 'communities', 'oldMembers'));
    }

    public function show(
        Household $household,
        HouseholdService $householdService,
        HouseholdMemberAdditionService $householdMemberAdditionService
    ) {
        $this->authorize('view', $household);
        $household = $householdService->loadDetails($household);
        $memberAccount = $householdService->memberAccountFor($household);
        $isPrivileged = ! $this->isMember();
        $pendingMemberAdditionRequest = $household->memberAdditionRequests->firstWhere('status', 'pending');
        $latestMemberAdditionRequest = $household->memberAdditionRequests->first();
        $memberAdditionRequestForView = $pendingMemberAdditionRequest ?? $latestMemberAdditionRequest;
        $memberAdditionRequestMembers = $memberAdditionRequestForView
            ? $householdMemberAdditionService->normalizedMembers($memberAdditionRequestForView->requestedMembers)
            : [];

        return view('households.show', compact(
            'household',
            'memberAccount',
            'isPrivileged',
            'pendingMemberAdditionRequest',
            'latestMemberAdditionRequest',
            'memberAdditionRequestForView',
            'memberAdditionRequestMembers'
        ));
    }

    public function documentsIndex(
        Household $household,
        HouseholdService $householdService,
        RegistrationDocumentService $registrationDocumentService,
        HouseholdMemberAdditionService $householdMemberAdditionService
    ) {
        $this->authorize('view', $household);

        $household = $householdService->loadDetails($household);
        $documentPageData = $this->buildHouseholdDocumentPageData(
            $household,
            $registrationDocumentService,
            $householdMemberAdditionService
        );

        return view('households.documents.index', array_merge(
            compact('household'),
            $documentPageData
        ));
    }

    public function showRegistrationDocument(
        Request $request,
        Household $household,
        HouseholdRegistrationDocument $registrationDocument
    ): StreamedResponse {
        $this->authorize('view', $household);

        abort_unless((int) $registrationDocument->household_id === (int) $household->household_id, 404);
        abort_unless(Storage::exists($registrationDocument->stored_path), 404);

        $downloadName = $registrationDocument->original_name ?: basename((string) $registrationDocument->stored_path);

        if ($request->boolean('download')) {
            return Storage::download($registrationDocument->stored_path, $downloadName);
        }

        return Storage::response($registrationDocument->stored_path, $downloadName, [
            'Content-Type' => $registrationDocument->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function update(
        UpdateHouseholdRequest $request,
        Household $household,
        HouseholdService $householdService
    ) {
        $before = $this->householdSnapshot($household);
        $data = $request->householdAttributes();
        $members = $request->members();
        $householdService->update($household, $data, $members);
        $updatedHousehold = $household->fresh(['members']);

        ActivityLogger::forCurrentUser(
            'households',
            "แก้ไขข้อมูลครัวเรือน {$household->account_no} ({$household->contact_person})",
            [
                'entity_type' => 'household',
                'entity_id' => (string) $household->household_id,
                'before' => $before,
                'after' => $this->householdSnapshot($updatedHousehold),
            ]
        );

        return redirect()->route('households.show', $household)
            ->with('success', 'แก้ไขครัวเรือนเรียบร้อย');
    }

    public function quickSearch(
        QuickSearchHouseholdRequest $request,
        HouseholdTransactionService $householdTransactionService
    ) {
        $q = $request->validated('q');
        $matches = $householdTransactionService->search($q);

        if ($matches->isEmpty()) {
            return response()->json([
                'found' => false,
                'matches' => [],
                'message' => "ไม่พบครัวเรือนที่ตรงกับคำค้น \"{$q}\"",
            ]);
        }

        return response()->json([
            'found' => true,
            'matches' => $matches
                ->map(fn (Household $household) => $householdTransactionService->lookupPayload($household)['household'])
                ->values()
                ->all(),
            'message' => null,
        ]);
    }

    public function review(
        ReviewHouseholdRequest $request,
        Household $household,
        CurrentUserIdResolver $currentUserIdResolver,
        HouseholdService $householdService
    ) {
        $payload = $request->payload();
        $reviewedBy = $currentUserIdResolver->resolve($request);
        [
            'household' => $reviewedHousehold,
            'before' => $before,
            'after' => $after,
        ] = $householdService->review(
            $household,
            $payload['status'],
            $payload['review_notes'],
            $reviewedBy
        );

        $statusLabel = match ($payload['status']) {
            'active' => 'อนุมัติใช้งาน',
            'rejected' => 'ไม่อนุมัติ',
            default => 'กำหนดเป็นปิดใช้งาน',
        };

        ActivityLogger::forCurrentUser(
            'households.review',
            "{$statusLabel} ครัวเรือน {$reviewedHousehold->account_no} ({$reviewedHousehold->contact_person})",
            [
                'entity_type' => 'household',
                'entity_id' => (string) $reviewedHousehold->household_id,
                'before' => $before,
                'after' => $after,
                'metadata' => [
                    'review_notes' => $payload['review_notes'],
                ],
            ]
        );

        return redirect()
            ->route('households.show', $reviewedHousehold)
            ->with('success', match ($payload['status']) {
                'active' => 'อนุมัติครัวเรือนเรียบร้อย',
                'rejected' => 'บันทึกผลไม่อนุมัติครัวเรือนเรียบร้อย',
                default => 'อัปเดตสถานะครัวเรือนเรียบร้อย',
            });
    }

    public function createCredentials(
        Household $household,
        HouseholdService $householdService,
        HouseholdViewDataFactory $householdViewDataFactory
    ) {
        $memberAccount = $householdService->syncExistingMemberAccount($household);
        $viewData = $householdViewDataFactory->credentialsPage($household, $memberAccount);

        return view('households.credentials', array_merge(
            compact('household', 'memberAccount'),
            $viewData
        ));
    }

    public function storeCredentials(
        StoreHouseholdCredentialsRequest $request,
        Household $household,
        HouseholdService $householdService
    ) {
        $data = $request->validated();
        $memberAccount = $householdService->storeCredentials($household, $data['password']);

        ActivityLogger::forCurrentUser(
            'households',
            "ตั้งรหัสผ่านบัญชีครัวเรือน {$memberAccount->username}"
        );

        return redirect()->route('households.show', $household)
            ->with('success', 'ตั้งหรือรีเซ็ตรหัสผ่านครัวเรือนเรียบร้อย ชื่อผู้ใช้คือ '.$memberAccount->username.' และระบบจะให้ผู้ใช้เปลี่ยนรหัสเมื่อเข้าใช้ครั้งถัดไป');
    }

    public function destroy(Household $household, HouseholdService $householdService)
    {
        if ($message = $householdService->deletionBlockMessage($household)) {
            return back()->withErrors($message);
        }

        $accountNo = $household->account_no;
        $contactPerson = $household->contact_person;

        $household->delete();

        ActivityLogger::forCurrentUser(
            'households',
            "ลบครัวเรือน {$accountNo} ({$contactPerson})"
        );

        return redirect()->route('households.index')
            ->with('success', 'ลบครัวเรือนเรียบร้อย');
    }

    private function isMember(): bool
    {
        return Auth::check() && Auth::user()->role === 'member';
    }

    private function memberHouseholdId(): ?int
    {
        $householdId = Auth::user()?->household_id;

        return $householdId ? (int) $householdId : null;
    }

    private function householdSnapshot(Household $household): array
    {
        $household->loadMissing('members');

        return [
            'account_no' => (string) $household->account_no,
            'house_no' => (string) $household->house_no,
            'village_no' => $household->village_no,
            'community_id' => (string) $household->community_id,
            'phone' => $household->phone,
            'contact_person' => (string) $household->contact_person,
            'register_date' => $household->register_date?->format('Y-m-d'),
            'accumulated_months' => (int) $household->accumulated_months,
            'members' => $household->members
                ->map(fn (Member $member) => [
                    'full_name' => (string) $member->full_name,
                    'id_card_last4' => $member->id_card_last4 ?: Member::extractIdCardLast4($member->id_card),
                    'id_card_hash' => $member->id_card_hash ?: Member::hashIdCard($member->id_card),
                    'relation' => $member->relation,
                    'is_head' => (bool) $member->is_head,
                ])
                ->values()
                ->all(),
        ];
    }

    private function buildHouseholdDocumentPageData(
        Household $household,
        RegistrationDocumentService $registrationDocumentService,
        HouseholdMemberAdditionService $householdMemberAdditionService
    ): array {
        $confirmedDocumentEntries = $this->buildConfirmedDocumentEntries($household);
        $currentMemberDocumentCards = $this->buildCurrentMemberDocumentCards(
            $household,
            $registrationDocumentService,
            $confirmedDocumentEntries
        );
        $openRequestDocumentGroups = $this->buildOpenRequestDocumentGroups(
            $household,
            $householdMemberAdditionService
        );

        return [
            'documentSummary' => [
                'member_count' => $household->members->count(),
                'document_count' => $household->registrationDocuments->count()
                    + $household->memberAdditionRequests->sum(fn ($request) => $request->documents->count()),
                'open_request_count' => count($openRequestDocumentGroups),
                'open_request_member_count' => collect($openRequestDocumentGroups)
                    ->sum(fn (array $group) => count($group['members'])),
            ],
            'currentMemberDocumentCards' => $currentMemberDocumentCards,
            'openRequestDocumentGroups' => $openRequestDocumentGroups,
        ];
    }

    private function buildConfirmedDocumentEntries(Household $household): Collection
    {
        $entries = collect();

        foreach ($household->registrationDocuments as $document) {
            $entries->push([
                'document_type' => $document->document_type,
                'member_name' => $document->member_full_name,
                'member_last4' => $document->member_id_card_last4,
                'member_relation' => $document->member_relation,
                'source_label' => 'เอกสารสมัครสมาชิกครั้งแรก',
                'uploaded_at' => $document->created_at?->format('d/m/Y H:i'),
                'created_at' => $document->created_at,
                'document' => $document,
                'view_url' => route('households.documents.show', [$household, $document]),
                'download_url' => route('households.documents.show', [$household, $document, 'download' => 1]),
            ]);
        }

        foreach ($household->memberAdditionRequests->where('status', 'approved') as $memberAdditionRequest) {
            foreach ($memberAdditionRequest->documents as $document) {
                $entries->push([
                    'document_type' => $document->document_type,
                    'member_name' => $document->member_full_name,
                    'member_last4' => $document->member_id_card_last4,
                    'member_relation' => $document->member_relation,
                    'source_label' => 'เพิ่มสมาชิกภายหลัง',
                    'uploaded_at' => $document->created_at?->format('d/m/Y H:i'),
                    'created_at' => $document->created_at,
                    'document' => $document,
                    'view_url' => route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $document]),
                    'download_url' => route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $document, 'download' => 1]),
                ]);
            }
        }

        return $entries;
    }

    private function buildCurrentMemberDocumentCards(
        Household $household,
        RegistrationDocumentService $registrationDocumentService,
        Collection $confirmedDocumentEntries
    ): array {
        $documentRequirements = $registrationDocumentService->documentRequirements();

        return $household->members
            ->values()
            ->filter(function (Member $member) use ($confirmedDocumentEntries) {
                foreach ($confirmedDocumentEntries as $entry) {
                    $score = $this->scoreDocumentMatch(
                        $member,
                        $entry['member_name'] ?? null,
                        $entry['member_last4'] ?? null,
                        $entry['member_relation'] ?? null
                    );
                    if ($score >= 3) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->map(function (Member $member, int $index) use ($documentRequirements, $confirmedDocumentEntries) {
                $householdCopy = $this->pickBestDocumentEntryForMember($member, $confirmedDocumentEntries, 'household_copy');
                $nationalIdCopy = $this->pickBestDocumentEntryForMember($member, $confirmedDocumentEntries, 'national_id_copy');

                return [
                    'title' => 'สมาชิกคนที่ '.($index + 1).': '.$member->full_name,
                    'meta' => collect([
                        $member->relation ?: null,
                        $member->masked_id_card,
                    ])->filter()->implode(' | '),
                    'is_head' => (bool) $member->is_head,
                    'documents' => [
                        [
                            'label' => $documentRequirements['member_household_copies']['label'] ?? 'สำเนาทะเบียนบ้าน',
                            'document' => $householdCopy['document'] ?? null,
                            'uploaded_at' => $householdCopy['uploaded_at'] ?? null,
                            'source_label' => $householdCopy['source_label'] ?? null,
                            'view_url' => $householdCopy['view_url'] ?? null,
                            'download_url' => $householdCopy['download_url'] ?? null,
                        ],
                        [
                            'label' => $documentRequirements['member_national_id_copies']['label'] ?? 'สำเนาบัตรประชาชน',
                            'document' => $nationalIdCopy['document'] ?? null,
                            'uploaded_at' => $nationalIdCopy['uploaded_at'] ?? null,
                            'source_label' => $nationalIdCopy['source_label'] ?? null,
                            'view_url' => $nationalIdCopy['view_url'] ?? null,
                            'download_url' => $nationalIdCopy['download_url'] ?? null,
                        ],
                    ],
                ];
            })
            ->all();
    }

    private function buildOpenRequestDocumentGroups(
        Household $household,
        HouseholdMemberAdditionService $householdMemberAdditionService
    ): array {
        $memberAdditionStatusMap = [
            'pending' => ['label' => 'รอตรวจสอบ', 'class' => 'text-bg-warning'],
            'rejected' => ['label' => 'ตีกลับ', 'class' => 'text-bg-danger'],
        ];

        return $household->memberAdditionRequests
            ->filter(fn ($request) => in_array($request->status, ['pending', 'rejected'], true))
            ->values()
            ->map(function ($memberAdditionRequest) use ($household, $householdMemberAdditionService, $memberAdditionStatusMap) {
                $statusUi = $memberAdditionStatusMap[$memberAdditionRequest->status] ?? $memberAdditionStatusMap['pending'];
                $documentLookup = $householdMemberAdditionService->documentLookup($memberAdditionRequest);
                $documentRequirements = $householdMemberAdditionService->documentRequirements();

                return [
                    'title' => 'คำขอเพิ่มสมาชิก #'.$memberAdditionRequest->member_addition_request_id,
                    'subtitle' => 'ยื่นเมื่อ '.($memberAdditionRequest->created_at?->format('d/m/Y H:i') ?? '-'),
                    'badge_label' => $statusUi['label'],
                    'badge_class' => $statusUi['class'],
                    'review_notes' => $memberAdditionRequest->review_notes,
                    'members' => collect($householdMemberAdditionService->normalizedMembers($memberAdditionRequest->requestedMembers))
                        ->map(function (array $member) use (
                            $household,
                            $memberAdditionRequest,
                            $documentLookup,
                            $documentRequirements
                        ) {
                            $householdCopy = $documentLookup->get($member['position'].'-household_copy');
                            $nationalIdCopy = $documentLookup->get($member['position'].'-national_id_copy');

                            return [
                                'title' => $member['display_name'],
                                'meta' => $member['display_meta'],
                                'documents' => [
                                    [
                                        'label' => $documentRequirements['member_household_copies']['label'] ?? 'สำเนาทะเบียนบ้าน',
                                        'document' => $householdCopy,
                                        'uploaded_at' => $householdCopy?->created_at?->format('d/m/Y H:i'),
                                        'source_label' => 'คำขอนี้',
                                        'view_url' => $householdCopy
                                            ? route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $householdCopy])
                                            : null,
                                        'download_url' => $householdCopy
                                            ? route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $householdCopy, 'download' => 1])
                                            : null,
                                    ],
                                    [
                                        'label' => $documentRequirements['member_national_id_copies']['label'] ?? 'สำเนาบัตรประชาชน',
                                        'document' => $nationalIdCopy,
                                        'uploaded_at' => $nationalIdCopy?->created_at?->format('d/m/Y H:i'),
                                        'source_label' => 'คำขอนี้',
                                        'view_url' => $nationalIdCopy
                                            ? route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $nationalIdCopy])
                                            : null,
                                        'download_url' => $nationalIdCopy
                                            ? route('households.member-additions.documents.show', [$household, $memberAdditionRequest, $nationalIdCopy, 'download' => 1])
                                            : null,
                                    ],
                                ],
                            ];
                        })
                        ->all(),
                ];
            })
            ->all();
    }

    private function pickBestDocumentEntryForMember(
        Member $member,
        Collection $confirmedDocumentEntries,
        string $documentType
    ): ?array {
        $bestCandidate = null;

        foreach ($confirmedDocumentEntries as $entry) {
            if (($entry['document_type'] ?? null) !== $documentType) {
                continue;
            }

            $score = $this->scoreDocumentMatch(
                $member,
                $entry['member_name'] ?? null,
                $entry['member_last4'] ?? null,
                $entry['member_relation'] ?? null
            );

            if ($score < 3) {
                continue;
            }

            $timestamp = $entry['created_at']?->getTimestamp() ?? 0;

            if ($bestCandidate === null
                || $score > $bestCandidate['score']
                || ($score === $bestCandidate['score'] && $timestamp > $bestCandidate['timestamp'])) {
                $bestCandidate = [
                    'score' => $score,
                    'timestamp' => $timestamp,
                    'entry' => $entry,
                ];
            }
        }

        return $bestCandidate['entry'] ?? null;
    }

    private function scoreDocumentMatch(
        Member $member,
        ?string $documentMemberName,
        ?string $documentMemberLast4,
        ?string $documentMemberRelation
    ): int {
        $score = 0;
        $memberLast4 = $member->id_card_last4 ?: Member::extractIdCardLast4($member->id_card);

        if ($documentMemberLast4 !== null
            && $documentMemberLast4 !== ''
            && $memberLast4 !== null
            && $documentMemberLast4 === $memberLast4) {
            $score += 3;
        }

        if ($this->normalizeDocumentMatchValue($documentMemberName) !== ''
            && $this->normalizeDocumentMatchValue($documentMemberName) === $this->normalizeDocumentMatchValue($member->full_name)) {
            $score += 2;
        }

        if ($this->normalizeDocumentMatchValue($documentMemberRelation) !== ''
            && $this->normalizeDocumentMatchValue($documentMemberRelation) === $this->normalizeDocumentMatchValue($member->relation)) {
            $score += 1;
        }

        return $score;
    }

    private function normalizeDocumentMatchValue(?string $value): string
    {
        return Str::lower(preg_replace('/\s+/u', ' ', trim((string) ($value ?? ''))) ?? '');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickSearchHouseholdRequest;
use App\Http\Requests\ReviewHouseholdRequest;
use App\Http\Requests\StoreHouseholdCredentialsRequest;
use App\Http\Requests\StoreHouseholdRequest;
use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\Community;
use App\Models\Household;
use App\Models\Member;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Households\HouseholdService;
use App\Support\Households\HouseholdViewDataFactory;
use App\Support\Transactions\HouseholdTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->when($status, fn ($qb) => $qb->where('active_status', $status));

        $households = $householdsQuery
            ->orderBy('account_no')
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

    public function show(Household $household, HouseholdService $householdService)
    {
        $this->authorize('view', $household);
        $household = $householdService->loadDetails($household);
        $memberAccount = $householdService->memberAccountFor($household);
        $isPrivileged = ! $this->isMember();

        return view('households.show', compact('household', 'memberAccount', 'isPrivileged'));
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

        $statusLabel = $payload['status'] === 'active' ? 'อนุมัติใช้งาน' : 'กำหนดเป็นปิดใช้งาน';

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
            ->with('success', $payload['status'] === 'active'
                ? 'อนุมัติครัวเรือนเรียบร้อย'
                : 'อัปเดตสถานะครัวเรือนเรียบร้อย');
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
}

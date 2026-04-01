<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseholdCredentialsRequest;
use App\Http\Requests\StoreHouseholdRequest;
use App\Http\Requests\ReviewHouseholdRequest;
use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\Community;
use App\Models\Household;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Households\HouseholdService;
use App\Support\Households\HouseholdViewDataFactory;
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
                        ->orWhere('phone', 'like', "%{$q}%");
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

    public function edit(Household $household)
    {
        $communities = Community::orderBy('community_id')->get();

        return view('households.edit', compact('household', 'communities'));
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
        $before = $household->only([
            'account_no',
            'house_no',
            'village_no',
            'community_id',
            'phone',
            'contact_person',
            'register_date',
            'accumulated_months',
        ]);
        $data = $request->householdAttributes();
        $householdService->update($household, $data);

        ActivityLogger::forCurrentUser(
            'households',
            "แก้ไขข้อมูลครัวเรือน {$household->account_no} ({$household->contact_person})",
            [
                'entity_type' => 'household',
                'entity_id' => (string) $household->household_id,
                'before' => $before,
                'after' => $household->fresh()->only([
                    'account_no',
                    'house_no',
                    'village_no',
                    'community_id',
                    'phone',
                    'contact_person',
                    'register_date',
                    'accumulated_months',
                ]),
            ]
        );

        return redirect()->route('households.index')
            ->with('success', 'แก้ไขครัวเรือนเรียบร้อย');
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
            ->with('success', 'ตั้งรหัสผ่านครัวเรือนเรียบร้อย ชื่อผู้ใช้คือ '.$memberAccount->username);
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
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewWithdrawRequestRequest;
use App\Http\Requests\StoreWithdrawRequestRequest;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\WithdrawRequest;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserHouseholdResolver;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Transactions\WithdrawService;
use App\Support\WithdrawRequests\WithdrawRequestPdfViewDataFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WithdrawRequestController extends Controller
{
    public function index(Request $request, CurrentUserHouseholdResolver $currentUserHouseholdResolver): View
    {
        $isPrivileged = in_array((string) $request->user()?->role, ['admin', 'staff'], true);
        $q = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());
        $memberHouseholdId = $currentUserHouseholdResolver->householdId($request);

        $withdrawRequestsQuery = WithdrawRequest::query()
            ->with([
                'household.community',
                'requestedByUser',
                'reviewedByUser.staff',
                'approvedTransaction',
            ])
            ->when(! $isPrivileged, function ($query) use ($memberHouseholdId) {
                if ($memberHouseholdId) {
                    $query->where('household_id', $memberHouseholdId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('request_no', 'like', "%{$q}%")
                        ->orWhere('request_notes', 'like', "%{$q}%")
                        ->orWhereHas('household', function ($householdQuery) use ($q) {
                            $householdQuery->where('account_no', 'like', "%{$q}%")
                                ->orWhere('contact_person', 'like', "%{$q}%")
                                ->orWhere('house_no', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "%{$q}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status));

        $summary = [
            'total' => (clone $withdrawRequestsQuery)->count(),
            'pending' => (clone $withdrawRequestsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $withdrawRequestsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $withdrawRequestsQuery)->where('status', 'rejected')->count(),
        ];

        $withdrawRequests = $withdrawRequestsQuery
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->orderByDesc('withdraw_request_id')
            ->paginate(20)
            ->withQueryString();

        $memberHousehold = ! $isPrivileged && $memberHouseholdId
            ? Household::query()->find($memberHouseholdId)
            : null;

        return view('withdraw_requests.index', [
            'withdrawRequests' => $withdrawRequests,
            'summary' => $summary,
            'q' => $q,
            'status' => $status,
            'isPrivileged' => $isPrivileged,
            'memberHousehold' => $memberHousehold,
        ]);
    }

    public function create(Request $request, CurrentUserHouseholdResolver $currentUserHouseholdResolver): View
    {
        abort_unless($request->user()?->role === 'member', 403);

        $household = $this->memberHousehold($request, $currentUserHouseholdResolver);

        return view('withdraw_requests.create', [
            'household' => $household,
        ]);
    }

    public function store(
        StoreWithdrawRequestRequest $request,
        CurrentUserHouseholdResolver $currentUserHouseholdResolver,
        CurrentUserIdResolver $currentUserIdResolver,
        WithdrawService $withdrawService
    ): RedirectResponse {
        abort_unless($request->user()?->role === 'member', 403);

        $household = $this->memberHousehold($request, $currentUserHouseholdResolver);
        $this->ensureHouseholdCanRequestWithdraw($household);
        $payload = $request->payload();
        $withdrawService->ensureSufficientBalance((float) $household->total_balance, $payload['requested_amount']);

        $withdrawRequest = WithdrawRequest::create([
            'request_no' => $this->nextRequestNo(),
            'household_id' => $household->household_id,
            'requested_by' => $currentUserIdResolver->resolve($request),
            'requested_for_date' => $payload['requested_for_date'],
            'requested_amount' => $payload['requested_amount'],
            'request_notes' => $payload['request_notes'],
            'status' => 'pending',
        ]);

        ActivityLogger::forCurrentUser(
            'withdraw_requests',
            "ยื่นคำขอถอน {$withdrawRequest->request_no} เป็นเงิน ".number_format((float) $withdrawRequest->requested_amount, 2).' บาท',
            [
                'entity_type' => 'withdraw_request',
                'entity_id' => (string) $withdrawRequest->withdraw_request_id,
                'after' => $this->snapshot($withdrawRequest->fresh()),
            ]
        );

        return redirect()
            ->route('withdraw-requests.show', $withdrawRequest)
            ->with('success', 'ยื่นคำขอถอนเรียบร้อยแล้ว กรุณาพิมพ์แบบฟอร์มและนำไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน');
    }

    public function show(Request $request, WithdrawRequest $withdrawRequest): View
    {
        $this->authorize('view', $withdrawRequest);

        $withdrawRequest->load([
            'household.community',
            'requestedByUser',
            'reviewedByUser.staff',
            'approvedTransaction.recordedByUser.staff',
        ]);

        return view('withdraw_requests.show', [
            'withdrawRequest' => $withdrawRequest,
            'isPrivileged' => in_array((string) $request->user()?->role, ['admin', 'staff'], true),
        ]);
    }

    public function form(
        Request $request,
        WithdrawRequest $withdrawRequest,
        WithdrawRequestPdfViewDataFactory $withdrawRequestPdfViewDataFactory
    ) {
        $this->authorize('view', $withdrawRequest);

        $withdrawRequest->load([
            'household.community',
        ]);

        $pdf = Pdf::loadView(
            'pdf.withdraw_slip_a5_landscape',
            $withdrawRequestPdfViewDataFactory->make($withdrawRequest)
        )->setPaper('a5', 'landscape');

        return $pdf->stream('withdraw-request_'.$withdrawRequest->request_no.'.pdf');
    }

    public function review(
        ReviewWithdrawRequestRequest $request,
        WithdrawRequest $withdrawRequest,
        CurrentUserIdResolver $currentUserIdResolver,
        WithdrawService $withdrawService
    ): RedirectResponse {
        $this->authorize('view', $withdrawRequest);
        $payload = $request->payload();

        if ($withdrawRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'decision' => 'คำขอนี้ถูกพิจารณาไปแล้ว ไม่สามารถดำเนินการซ้ำได้',
            ]);
        }

        $reviewedBy = $currentUserIdResolver->resolve($request);
        $before = $this->snapshot($withdrawRequest);
        $result = DB::transaction(function () use ($payload, $reviewedBy, $withdrawRequest, $withdrawService) {
            $lockedRequest = WithdrawRequest::query()
                ->with('household')
                ->lockForUpdate()
                ->findOrFail($withdrawRequest->withdraw_request_id);

            if ($lockedRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'คำขอนี้ถูกพิจารณาไปแล้ว ไม่สามารถดำเนินการซ้ำได้',
                ]);
            }

            $lockedHousehold = Household::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequest->household_id);

            $this->ensureHouseholdCanRequestWithdraw($lockedHousehold);

            $transaction = null;

            if ($payload['decision'] === 'approved') {
                $transaction = $withdrawService->record(
                    (int) $lockedRequest->household_id,
                    (string) $payload['transaction_date'],
                    (float) $lockedRequest->requested_amount,
                    $reviewedBy
                );
            }

            $lockedRequest->forceFill([
                'status' => $payload['decision'],
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
                'review_notes' => $payload['review_notes'],
                'approved_transaction_id' => $transaction?->transaction_id,
            ])->save();

            return [
                'withdrawRequest' => $lockedRequest->fresh([
                    'household.community',
                    'requestedByUser',
                    'reviewedByUser.staff',
                    'approvedTransaction.recordedByUser.staff',
                ]),
                'transaction' => $transaction,
            ];
        });

        /** @var WithdrawRequest $reviewedRequest */
        $reviewedRequest = $result['withdrawRequest'];
        /** @var Transaction|null $transaction */
        $transaction = $result['transaction'];

        ActivityLogger::forCurrentUser(
            'withdraw_requests',
            ($reviewedRequest->status === 'approved' ? 'อนุมัติ' : 'ไม่อนุมัติ')."คำขอถอน {$reviewedRequest->request_no}",
            [
                'entity_type' => 'withdraw_request',
                'entity_id' => (string) $reviewedRequest->withdraw_request_id,
                'before' => $before,
                'after' => $this->snapshot($reviewedRequest),
                'metadata' => [
                    'approved_transaction_id' => $transaction?->transaction_id,
                    'decision' => $reviewedRequest->status,
                    'transaction_date' => $payload['transaction_date'],
                ],
            ]
        );

        if ($transaction) {
            ActivityLogger::forCurrentUser(
                'transactions',
                "อนุมัติคำขอถอน {$reviewedRequest->request_no} และสร้างรายการถอน #{$transaction->transaction_id}",
                [
                    'entity_type' => 'transaction',
                    'entity_id' => (string) $transaction->transaction_id,
                    'after' => [
                        'transaction_id' => (int) $transaction->transaction_id,
                        'transaction_type' => (string) $transaction->transaction_type,
                        'household_id' => (int) $transaction->household_id,
                        'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
                        'total_amount' => (float) $transaction->total_amount,
                        'approved_from_withdraw_request_id' => (int) $reviewedRequest->withdraw_request_id,
                    ],
                ]
            );
        }

        $successMessage = $transaction
            ? "อนุมัติคำขอเรียบร้อยแล้ว และสร้างรายการถอน #{$transaction->transaction_id} สำเร็จ"
            : 'บันทึกผลการพิจารณาคำขอถอนเรียบร้อยแล้ว';

        return redirect()
            ->route('withdraw-requests.show', $reviewedRequest)
            ->with('success', $successMessage);
    }

    private function memberHousehold(Request $request, CurrentUserHouseholdResolver $currentUserHouseholdResolver): Household
    {
        $householdId = $currentUserHouseholdResolver->householdId($request);

        abort_unless($householdId, 404, 'ไม่พบครัวเรือนของบัญชีสมาชิกนี้');

        $household = Household::query()
            ->with('community')
            ->findOrFail($householdId);

        $this->authorize('view', $household);

        return $household;
    }

    private function ensureHouseholdCanRequestWithdraw(Household $household): void
    {
        if ($household->active_status === 'active') {
            return;
        }

        throw ValidationException::withMessages([
            'requested_amount' => 'บัญชีครัวเรือนนี้ยังไม่พร้อมสำหรับการยื่นคำขอถอน',
        ]);
    }

    private function nextRequestNo(): string
    {
        return 'WR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }

    private function snapshot(WithdrawRequest $withdrawRequest): array
    {
        return [
            'request_no' => (string) $withdrawRequest->request_no,
            'household_id' => (int) $withdrawRequest->household_id,
            'requested_by' => $withdrawRequest->requested_by !== null ? (int) $withdrawRequest->requested_by : null,
            'requested_for_date' => $withdrawRequest->requested_for_date?->format('Y-m-d'),
            'requested_amount' => (float) $withdrawRequest->requested_amount,
            'status' => (string) $withdrawRequest->status,
            'reviewed_by' => $withdrawRequest->reviewed_by !== null ? (int) $withdrawRequest->reviewed_by : null,
            'reviewed_at' => $withdrawRequest->reviewed_at?->format('Y-m-d H:i:s'),
            'approved_transaction_id' => $withdrawRequest->approved_transaction_id !== null
                ? (int) $withdrawRequest->approved_transaction_id
                : null,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveWithdrawRequest;
use App\Models\WithdrawRequest;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Transactions\HouseholdTransactionService;
use App\Support\Transactions\WithdrawService;
use App\Support\WithdrawRequests\WithdrawRequestPdfViewDataFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class WithdrawController extends Controller
{
    public function create()
    {
        return view('withdraws.create');
    }

    public function preview(
        SaveWithdrawRequest $request,
        HouseholdTransactionService $householdTransactionService,
        WithdrawService $withdrawService,
        WithdrawRequestPdfViewDataFactory $withdrawRequestPdfViewDataFactory
    ) {
        [
            'household' => $household,
            'date' => $date,
            'amount' => $amount,
        ] = $this->withdrawPayload($request, $householdTransactionService, $withdrawService);

        $previewRequest = new WithdrawRequest([
            'household_id' => $household->household_id,
            'requested_for_date' => $date,
            'requested_amount' => $amount,
            'status' => 'pending',
        ]);

        $previewRequest->setRelation('household', $household);

        $pdf = Pdf::loadView(
            'pdf.withdraw_slip_a5_landscape',
            $withdrawRequestPdfViewDataFactory->make($previewRequest, $request->user())
        )->setPaper('a5', 'landscape');

        return $pdf->stream('withdraw-slip-preview.pdf');
    }

    public function store(
        SaveWithdrawRequest $request,
        CurrentUserIdResolver $currentUserIdResolver,
        HouseholdTransactionService $householdTransactionService,
        WithdrawService $withdrawService
    ): RedirectResponse {
        [
            'household' => $household,
            'date' => $date,
            'amount' => $amount,
        ] = $this->withdrawPayload($request, $householdTransactionService, $withdrawService);
        $requestedBy = $currentUserIdResolver->resolve($request);

        $withdrawRequest = WithdrawRequest::create([
            'request_no' => $this->nextRequestNo(),
            'household_id' => $household->household_id,
            'requested_by' => $requestedBy,
            'requested_for_date' => $date,
            'requested_amount' => $amount,
            'request_notes' => null,
            'status' => 'pending',
        ]);

        ActivityLogger::forCurrentUser(
            'withdraw_requests',
            "บันทึกคำขอถอน {$withdrawRequest->request_no} ให้ {$household->account_no} ({$household->contact_person}) เป็นเงิน "
            .number_format($amount, 2).' บาท',
            [
                'entity_type' => 'withdraw_request',
                'entity_id' => (string) $withdrawRequest->withdraw_request_id,
                'after' => $this->snapshot($withdrawRequest),
            ]
        );

        return redirect()
            ->route('withdraw-requests.index', ['status' => 'pending'])
            ->with(
                'success',
                "บันทึกคำขอถอน {$withdrawRequest->request_no} เป็นรออนุมัติเรียบร้อยแล้ว สามารถพิมพ์แบบฟอร์มจากหน้าคำขอถอนได้"
            );
    }

    private function withdrawPayload(
        SaveWithdrawRequest $request,
        HouseholdTransactionService $householdTransactionService,
        WithdrawService $withdrawService
    ): array {
        $data = $request->validated();
        $household = $householdTransactionService->findForTransaction(
            $data['community_id'],
            $data['house_no'],
            [
                'household_id',
                'account_no',
                'contact_person',
                'community_id',
                'house_no',
                'active_status',
                'total_balance',
            ]
        );

        $householdTransactionService->ensureActive($household);
        $date = $data['transaction_date'];
        $amount = $withdrawService->normalizeAmount($data['amount']);
        $withdrawService->ensureSufficientBalance((float) $household->total_balance, $amount);

        return [
            'household' => $household,
            'date' => $date,
            'amount' => $amount,
        ];
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
        ];
    }
}

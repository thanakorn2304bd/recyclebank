<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveWithdrawRequest;
use App\Models\Transaction;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Transactions\HouseholdTransactionService;
use App\Support\Transactions\WithdrawService;
use Barryvdh\DomPDF\Facade\Pdf;

class WithdrawController extends Controller
{
    public function create()
    {
        return view('withdraws.create');
    }

    public function preview(
        SaveWithdrawRequest $request,
        CurrentUserIdResolver $currentUserIdResolver,
        HouseholdTransactionService $householdTransactionService,
        WithdrawService $withdrawService
    ) {
        [
            'household' => $household,
            'date' => $date,
            'amount' => $amount,
        ] = $this->withdrawPayload($request, $householdTransactionService, $withdrawService);
        $recordedBy = $currentUserIdResolver->resolve($request);

        $previewTransaction = new Transaction([
            'household_id' => $household->household_id,
            'transaction_date' => $date,
            'transaction_type' => 'withdraw',
            'total_weight' => 0.00,
            'total_amount' => $amount,
            'recorded_by' => $recordedBy,
        ]);

        $previewTransaction->setRelation('household', $household);

        if ($user = $request->user()) {
            $user->loadMissing('staff');
            $previewTransaction->setRelation('recordedByUser', $user);
        }

        $pdf = Pdf::loadView('pdf.withdraw_slip_a5_landscape', [
            'tx' => $previewTransaction,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream('withdraw-slip-preview.pdf');
    }

    public function store(
        SaveWithdrawRequest $request,
        CurrentUserIdResolver $currentUserIdResolver,
        HouseholdTransactionService $householdTransactionService,
        WithdrawService $withdrawService
    ) {
        [
            'household' => $household,
            'date' => $date,
            'amount' => $amount,
        ] = $this->withdrawPayload($request, $householdTransactionService, $withdrawService);
        $householdId = (int) $household->household_id;
        $recordedBy = $currentUserIdResolver->resolve($request);
        $transaction = $withdrawService->record($householdId, $date, $amount, $recordedBy);

        ActivityLogger::forCurrentUser(
            'transactions',
            "บันทึกถอนให้ {$household->account_no} ({$household->contact_person}) เป็นเงิน "
            .number_format($amount, 2).' บาท'
        );

        return redirect()->route('transactions.receipt', $transaction);
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
}

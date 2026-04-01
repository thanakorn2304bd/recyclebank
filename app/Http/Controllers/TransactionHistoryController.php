<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReverseTransactionRequest;
use App\Http\Requests\TransactionDateRangeRequest;
use App\Http\Requests\TransactionHistoryIndexRequest;
use App\Models\Household;
use App\Models\Transaction;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserHouseholdResolver;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Transactions\TransactionHistoryService;
use App\Support\Transactions\TransactionReversalService;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    // 1) รายการทั้งหมด + filter
    public function index(
        TransactionHistoryIndexRequest $request,
        CurrentUserHouseholdResolver $currentUserHouseholdResolver,
        TransactionHistoryService $transactionHistoryService
    ) {
        $filters = $request->filters();
        $isMember = $currentUserHouseholdResolver->isMember($request);
        $memberHouseholdId = $currentUserHouseholdResolver->householdId($request);
        [
            'txs' => $txs,
            'households' => $households,
            'householdId' => $householdId,
        ] = $transactionHistoryService->indexData($filters, $isMember, $memberHouseholdId);
        [
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ] = $filters;
        [
            'depositCount' => $depositCount,
            'withdrawCount' => $withdrawCount,
            'pageAmount' => $pageAmount,
            'pageWeight' => $pageWeight,
        ] = $transactionHistoryService->indexViewMetrics($txs);
        $isPrivileged = ! $isMember;

        return view('transactions.index', compact(
            'txs',
            'households',
            'type',
            'from',
            'to',
            'householdId',
            'depositCount',
            'withdrawCount',
            'pageAmount',
            'pageWeight',
            'isPrivileged'
        ));
    }

    // 2) รายการตามครัวเรือน (statement)
    public function household(
        Household $household,
        TransactionDateRangeRequest $request,
        TransactionHistoryService $transactionHistoryService,
        CurrentUserHouseholdResolver $currentUserHouseholdResolver
    ) {
        $this->authorize('view', $household);
        ['from' => $from, 'to' => $to] = $request->range();
        $txs = $transactionHistoryService->householdTransactions($household, $from, $to);
        [
            'depositCount' => $depositCount,
            'withdrawCount' => $withdrawCount,
        ] = $transactionHistoryService->householdViewMetrics($txs);
        $isPrivileged = ! $currentUserHouseholdResolver->isMember($request);

        return view('transactions.household', compact(
            'household',
            'txs',
            'from',
            'to',
            'depositCount',
            'withdrawCount',
            'isPrivileged'
        ));
    }

    // 3) รายละเอียด (ใบเสร็จ)
    public function show(
        Request $request,
        Transaction $transaction,
        TransactionHistoryService $transactionHistoryService
    ) {
        $this->authorize('view', $transaction);
        $transaction = $transactionHistoryService->loadDetails($transaction);
        ['isDepositSummary' => $isDepositSummary] = $transactionHistoryService->detailViewData(
            $transaction,
            (string) $request->query('source', '')
        );
        $isPrivileged = in_array((string) $request->user()?->role, ['admin', 'staff'], true);
        $canReverse = $isPrivileged
            && ! $transaction->is_reversal
            && $transaction->reversed_at === null;

        return view('transactions.show', compact(
            'transaction',
            'isDepositSummary',
            'isPrivileged',
            'canReverse'
        ));
    }

    public function reverse(
        ReverseTransactionRequest $request,
        Transaction $transaction,
        CurrentUserIdResolver $currentUserIdResolver,
        TransactionHistoryService $transactionHistoryService,
        TransactionReversalService $transactionReversalService
    ) {
        $this->authorize('view', $transaction);
        $payload = $request->payload();
        $recordedBy = $currentUserIdResolver->resolve($request);
        [
            'original' => $original,
            'reversal' => $reversal,
            'before' => $before,
            'after' => $after,
        ] = $transactionReversalService->reverse(
            $transaction,
            $payload['reversal_date'],
            $payload['reason'],
            $recordedBy
        );

        ActivityLogger::forCurrentUser(
            'transactions.reverse',
            "กลับรายการธุรกรรม #{$original->transaction_id} ด้วยรายการชดเชย #{$reversal->transaction_id}",
            [
                'entity_type' => 'transaction',
                'entity_id' => (string) $original->transaction_id,
                'before' => [
                    'original' => $before,
                ],
                'after' => $after,
                'metadata' => [
                    'reversal_reason' => $payload['reason'],
                    'reversal_date' => $payload['reversal_date'],
                ],
            ]
        );

        $transaction = $transactionHistoryService->loadDetails($original);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', "กลับรายการสำเร็จ ระบบสร้างรายการชดเชย #{$reversal->transaction_id} แล้ว");
    }
}

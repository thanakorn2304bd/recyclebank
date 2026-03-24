<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionDateRangeRequest;
use App\Http\Requests\TransactionHistoryIndexRequest;
use App\Models\Household;
use App\Models\Transaction;
use App\Support\Auth\CurrentUserHouseholdResolver;
use App\Support\Transactions\TransactionHistoryService;

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

        return view('transactions.index', compact('txs', 'households', 'type', 'from', 'to', 'householdId'));
    }

    // 2) รายการตามครัวเรือน (statement)
    public function household(
        Household $household,
        TransactionDateRangeRequest $request,
        TransactionHistoryService $transactionHistoryService
    ) {
        $this->authorize('view', $household);
        ['from' => $from, 'to' => $to] = $request->range();
        $txs = $transactionHistoryService->householdTransactions($household, $from, $to);

        return view('transactions.household', compact('household', 'txs', 'from', 'to'));
    }

    // 3) รายละเอียด (ใบเสร็จ)
    public function show(Transaction $transaction, TransactionHistoryService $transactionHistoryService)
    {
        $this->authorize('view', $transaction);
        $transaction = $transactionHistoryService->loadDetails($transaction);

        return view('transactions.show', compact('transaction'));
    }
}

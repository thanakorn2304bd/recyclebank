<?php

namespace App\Support\Transactions;

use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionHistoryService
{
    public function indexData(array $filters, bool $isMember, ?int $memberHouseholdId): array
    {
        $householdId = $isMember ? $memberHouseholdId : $filters['household_id'];

        $households = Household::query()
            ->when($isMember, function ($query) use ($memberHouseholdId) {
                if ($memberHouseholdId) {
                    $query->where('household_id', $memberHouseholdId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('account_no')
            ->get(['household_id', 'account_no', 'contact_person']);

        $transactions = Transaction::query()
            ->with(['household', 'reversalTransaction'])
            ->when($isMember, function ($query) use ($memberHouseholdId) {
                if ($memberHouseholdId) {
                    $query->where('household_id', $memberHouseholdId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($filters['type'] !== '', fn ($query) => $query->where('transaction_type', $filters['type']))
            ->when($filters['from'], fn ($query) => $query->whereDate('transaction_date', '>=', $filters['from']))
            ->when($filters['to'], fn ($query) => $query->whereDate('transaction_date', '<=', $filters['to']))
            ->when($householdId, fn ($query) => $query->where('household_id', $householdId))
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->paginate(20)
            ->withQueryString();

        return [
            'txs' => $transactions,
            'households' => $households,
            'householdId' => $householdId,
        ];
    }

    public function householdTransactions(Household $household, ?string $from, ?string $to): LengthAwarePaginator
    {
        return Transaction::query()
            ->with('reversalTransaction')
            ->where('household_id', $household->household_id)
            ->when($from, fn ($query) => $query->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->paginate(25)
            ->withQueryString();
    }

    public function loadDetails(Transaction $transaction): Transaction
    {
        $transaction->load([
            'household',
            'details.material',
            'recordedByUser.staff',
            'reversedByUser.staff',
            'reversalOf.recordedByUser.staff',
            'reversalOf.reversedByUser.staff',
            'reversalTransaction.recordedByUser.staff',
            'reversalTransaction.reversedByUser.staff',
        ]);

        return $transaction;
    }

    public function detailViewData(Transaction $transaction, string $source): array
    {
        return [
            'isDepositSummary' => $transaction->transaction_type === 'deposit' && $source === 'deposit',
        ];
    }

    public function indexViewMetrics(LengthAwarePaginator $transactions): array
    {
        $pageTransactions = $transactions->getCollection();

        return [
            'depositCount' => $pageTransactions->where('transaction_type', 'deposit')->count(),
            'withdrawCount' => $pageTransactions->where('transaction_type', 'withdraw')->count(),
            'pageAmount' => (float) $pageTransactions->sum('total_amount'),
            'pageWeight' => (float) $pageTransactions->sum('total_weight'),
        ];
    }

    public function householdViewMetrics(LengthAwarePaginator $transactions): array
    {
        $pageTransactions = $transactions->getCollection();

        return [
            'depositCount' => $pageTransactions->where('transaction_type', 'deposit')->count(),
            'withdrawCount' => $pageTransactions->where('transaction_type', 'withdraw')->count(),
        ];
    }
}

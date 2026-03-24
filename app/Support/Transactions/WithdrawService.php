<?php

namespace App\Support\Transactions;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawService
{
    public function normalizeAmount(string|float|int $amount): float
    {
        return round((float) $amount, 2);
    }

    public function ensureSufficientBalance(float $balance, float $amount): void
    {
        if ($amount <= $balance) {
            return;
        }

        throw ValidationException::withMessages([
            'amount' => 'ยอดเงินไม่พอ (คงเหลือ '.number_format($balance, 2).')',
        ]);
    }

    public function record(int $householdId, string $date, float $amount, int $recordedBy): Transaction
    {
        return DB::transaction(function () use ($householdId, $date, $amount, $recordedBy) {
            $balance = (float) DB::table('household')
                ->where('household_id', $householdId)
                ->lockForUpdate()
                ->value('total_balance');

            $this->ensureSufficientBalance($balance, $amount);

            $transaction = Transaction::create([
                'household_id' => $householdId,
                'transaction_date' => $date,
                'transaction_type' => 'withdraw',
                'total_weight' => 0.00,
                'total_amount' => $amount,
                'recorded_by' => $recordedBy,
            ]);

            DB::table('household')
                ->where('household_id', $householdId)
                ->update(['total_balance' => DB::raw('total_balance - '.$amount)]);

            return $transaction;
        });
    }
}

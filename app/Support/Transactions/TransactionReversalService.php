<?php

namespace App\Support\Transactions;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionReversalService
{
    public function reverse(Transaction $transaction, string $reversalDate, string $reason, int $recordedBy): array
    {
        return DB::transaction(function () use ($transaction, $reversalDate, $reason, $recordedBy) {
            $original = Transaction::query()
                ->with(['details.material', 'household'])
                ->lockForUpdate()
                ->findOrFail($transaction->transaction_id);

            $originalDate = $original->transaction_date?->toDateString();

            if ($originalDate && $reversalDate < $originalDate) {
                throw ValidationException::withMessages([
                    'reversal_date' => 'วันที่กลับรายการต้องไม่น้อยกว่าวันที่ทำรายการเดิม',
                ]);
            }

            if ($original->is_reversal) {
                throw ValidationException::withMessages([
                    'reason' => 'ไม่สามารถกลับรายการซ้อนบนรายการชดเชยได้',
                ]);
            }

            if ($original->reversed_at !== null) {
                throw ValidationException::withMessages([
                    'reason' => 'รายการนี้ถูกกลับรายการไปแล้ว',
                ]);
            }

            $currentBalance = (float) DB::table('household')
                ->where('household_id', $original->household_id)
                ->lockForUpdate()
                ->value('total_balance');

            $originalAmount = round((float) $original->total_amount, 2);

            if ($original->transaction_type === 'deposit' && $currentBalance < $originalAmount) {
                throw ValidationException::withMessages([
                    'reason' => 'ยอดคงเหลือปัจจุบันไม่พอสำหรับกลับรายการฝากนี้',
                ]);
            }

            $before = $this->transactionSnapshot($original, $currentBalance);
            $reversal = Transaction::create([
                'household_id' => $original->household_id,
                'transaction_date' => $reversalDate,
                'transaction_type' => $original->transaction_type,
                'total_weight' => $original->transaction_type === 'deposit'
                    ? round(-1 * (float) $original->total_weight, 2)
                    : 0.00,
                'total_amount' => round(-1 * $originalAmount, 2),
                'recorded_by' => $recordedBy,
                'is_reversal' => true,
                'reversal_of_transaction_id' => $original->transaction_id,
                'reversal_reason' => $reason,
            ]);

            if ($original->transaction_type === 'deposit') {
                $original->details->each(function (TransactionDetail $detail) use ($reversal) {
                    TransactionDetail::create([
                        'transaction_id' => $reversal->transaction_id,
                        'material_id' => $detail->material_id,
                        'weight' => round(-1 * (float) $detail->weight, 2),
                        'price_per_unit' => (float) $detail->price_per_unit,
                        'amount' => round(-1 * (float) $detail->amount, 2),
                    ]);
                });
            }

            $original->update([
                'reversed_by' => $recordedBy,
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            $delta = $this->householdBalanceDelta($reversal);

            DB::table('household')
                ->where('household_id', $original->household_id)
                ->update([
                    'total_balance' => DB::raw('total_balance + ('.number_format($delta, 2, '.', '').')'),
                ]);

            $balanceAfter = round($currentBalance + $delta, 2);
            $original = $original->fresh([
                'household',
                'details.material',
                'recordedByUser.staff',
                'reversalTransaction.recordedByUser.staff',
                'reversedByUser.staff',
            ]);
            $reversal = $reversal->fresh([
                'household',
                'details.material',
                'recordedByUser.staff',
                'reversalOf',
            ]);

            return [
                'original' => $original,
                'reversal' => $reversal,
                'before' => $before,
                'after' => [
                    'original' => $this->transactionSnapshot($original, $balanceAfter),
                    'reversal' => $this->transactionSnapshot($reversal, $balanceAfter),
                ],
            ];
        });
    }

    private function householdBalanceDelta(Transaction $transaction): float
    {
        $amount = round((float) $transaction->total_amount, 2);

        return $transaction->transaction_type === 'deposit'
            ? $amount
            : -1 * $amount;
    }

    private function transactionSnapshot(Transaction $transaction, float $householdBalance): array
    {
        return [
            'transaction_id' => (int) $transaction->transaction_id,
            'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
            'transaction_type' => (string) $transaction->transaction_type,
            'total_weight' => round((float) $transaction->total_weight, 2),
            'total_amount' => round((float) $transaction->total_amount, 2),
            'is_reversal' => (bool) $transaction->is_reversal,
            'reversal_of_transaction_id' => $transaction->reversal_of_transaction_id !== null
                ? (int) $transaction->reversal_of_transaction_id
                : null,
            'reversed_by' => $transaction->reversed_by !== null ? (int) $transaction->reversed_by : null,
            'reversed_at' => $transaction->reversed_at?->format('Y-m-d H:i:s'),
            'household_balance' => $householdBalance,
            'details' => $transaction->details
                ->map(fn (TransactionDetail $detail) => [
                    'material_id' => (int) $detail->material_id,
                    'weight' => round((float) $detail->weight, 2),
                    'price_per_unit' => round((float) $detail->price_per_unit, 2),
                    'amount' => round((float) $detail->amount, 2),
                ])
                ->values()
                ->all(),
        ];
    }
}

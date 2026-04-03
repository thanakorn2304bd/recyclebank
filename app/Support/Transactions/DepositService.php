<?php

namespace App\Support\Transactions;

use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepositService
{
    private const MAX_DECIMAL_VALUE = 99999999.99;

    public function materialsForCreateForm(): Collection
    {
        return Material::with('category')
            ->where('is_active', 1)
            ->orderBy('material_name')
            ->get(['material_id', 'material_name', 'unit', 'category_id']);
    }

    public function currentPricesForToday(): array
    {
        $today = now()->toDateString();

        return MaterialPrice::query()
            ->select('material_id', 'price')
            ->where(function ($query) use ($today) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', $today);
            })
            ->where('effective_date', '<=', $today)
            ->orderByDesc('effective_date')
            ->orderByDesc('price_id')
            ->get()
            ->groupBy('material_id')
            ->map(fn ($rows) => (float) $rows->first()->price)
            ->toArray();
    }

    public function record(int $householdId, string $date, array $items, int $recordedBy): array
    {
        return DB::transaction(function () use ($householdId, $date, $items, $recordedBy) {
            $totalWeight = 0.0;
            $totalAmount = 0.0;

            $transaction = Transaction::create([
                'household_id' => $householdId,
                'transaction_date' => $date,
                'transaction_type' => 'deposit',
                'total_weight' => 0.00,
                'total_amount' => 0.00,
                'recorded_by' => $recordedBy,
            ]);

            foreach ($items as $item) {
                $materialId = (int) $item['material_id'];
                $weight = (float) $item['weight'];
                $pricePerUnit = $this->pricePerUnitOnDate($materialId, $date);
                $amount = round($weight * $pricePerUnit, 2);

                $this->ensureDecimalFits($weight, 'น้ำหนักของวัสดุ');
                $this->ensureDecimalFits($amount, 'จำนวนเงินของวัสดุ');

                TransactionDetail::create([
                    'transaction_id' => $transaction->transaction_id,
                    'material_id' => $materialId,
                    'weight' => $weight,
                    'price_per_unit' => $pricePerUnit,
                    'amount' => $amount,
                ]);

                $totalWeight += $weight;
                $totalAmount += $amount;
            }

            $totalWeight = round($totalWeight, 2);
            $totalAmount = round($totalAmount, 2);

            $this->ensureDecimalFits($totalWeight, 'น้ำหนักรวม');
            $this->ensureDecimalFits($totalAmount, 'ยอดรวม');

            $transaction->update([
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
            ]);

            DB::table('household')
                ->where('household_id', $householdId)
                ->update(['total_balance' => DB::raw('total_balance + '.$totalAmount)]);

            return [
                'transaction' => $transaction,
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
            ];
        });
    }

    private function pricePerUnitOnDate(int $materialId, string $onDate): float
    {
        $price = MaterialPrice::query()
            ->where('material_id', $materialId)
            ->where('effective_date', '<=', $onDate)
            ->where(function ($query) use ($onDate) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', $onDate);
            })
            ->orderByDesc('effective_date')
            ->orderByDesc('price_id')
            ->value('price');

        $pricePerUnit = (float) ($price ?? 0);

        if ($pricePerUnit > 0) {
            return $pricePerUnit;
        }

        $materialName = Material::query()
            ->where('material_id', $materialId)
            ->value('material_name') ?? ('#'.$materialId);

        throw ValidationException::withMessages([
            'items' => "ไม่พบราคาวัสดุ {$materialName} ณ วันที่ {$onDate}",
        ]);
    }

    private function ensureDecimalFits(float $value, string $label): void
    {
        if (abs($value) <= self::MAX_DECIMAL_VALUE) {
            return;
        }

        throw ValidationException::withMessages([
            'items' => "{$label} เกินขนาดที่ระบบรองรับ (สูงสุด ".number_format(self::MAX_DECIMAL_VALUE, 2).')',
        ]);
    }
}

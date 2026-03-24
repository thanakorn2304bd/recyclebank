<?php

namespace App\Support\MaterialPrices;

use App\Models\Material;
use App\Models\MaterialPrice;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MaterialPriceService
{
    public function create(array $data, int $createdBy): void
    {
        DB::transaction(function () use ($data, $createdBy) {
            $effectiveDate = $data['effective_date'];
            $expiredDate = $data['expired_date'] ?? null;

            if ($expiredDate === null) {
                $this->closePreviousOpenEndedPriceIfNeeded($data['material_id'], $effectiveDate);
            }

            if ($overlapMessage = $this->pricePeriodOverlapMessage($data['material_id'], $effectiveDate, $expiredDate)) {
                throw ValidationException::withMessages([
                    'effective_date' => $overlapMessage,
                ]);
            }

            MaterialPrice::create([
                'material_id' => $data['material_id'],
                'price' => $data['price'],
                'effective_date' => $effectiveDate,
                'expired_date' => $expiredDate,
                'created_by' => $createdBy,
                'created_at' => now(),
            ]);
        });
    }

    public function planBulkUpdate(array $rows): array
    {
        $rowCollection = collect($rows);

        $materialIds = $rowCollection->keys()
            ->map(fn ($materialId) => (int) $materialId)
            ->filter()
            ->values();

        $materials = Material::query()
            ->whereIn('material_id', $materialIds)
            ->get(['material_id', 'material_name'])
            ->keyBy('material_id');

        $priceIds = $rowCollection->pluck('price_id')
            ->filter(fn ($priceId) => filled($priceId))
            ->map(fn ($priceId) => (int) $priceId)
            ->values();

        $existingPrices = MaterialPrice::query()
            ->whereIn('price_id', $priceIds)
            ->get()
            ->keyBy('price_id');

        $updates = [];
        $creates = [];
        $errors = [];

        foreach ($rowCollection as $materialId => $row) {
            $materialId = (int) $materialId;
            $material = $materials->get($materialId);

            if (! $material) {
                continue;
            }

            if ($this->isBlankRow($row)) {
                continue;
            }

            $existingPrice = ($row['price_id'] ?? null)
                ? $existingPrices->get((int) $row['price_id'])
                : null;

            if (($row['price_id'] ?? null) && (! $existingPrice || (int) $existingPrice->material_id !== $materialId)) {
                $errors["rows.$materialId.price"] = 'ไม่พบรายการราคาที่ต้องการแก้ไข';

                continue;
            }

            $rowErrors = $this->validateBulkRow($row, $materialId);

            if ($rowErrors !== []) {
                $errors = [...$errors, ...$rowErrors];

                continue;
            }

            $normalizedPrice = number_format((float) $row['price'], 2, '.', '');
            $normalizedExpiredDate = $row['expired_date'] !== '' ? $row['expired_date'] : null;

            if ($overlapMessage = $this->pricePeriodOverlapMessage(
                $materialId,
                $row['effective_date'],
                $normalizedExpiredDate,
                $row['price_id'] ?? null
            )) {
                $errors["rows.$materialId.effective_date"] = $overlapMessage;

                continue;
            }

            if ($existingPrice) {
                $hasChanged = number_format((float) $existingPrice->price, 2, '.', '') !== $normalizedPrice
                    || $existingPrice->effective_date?->format('Y-m-d') !== $row['effective_date']
                    || $existingPrice->expired_date?->format('Y-m-d') !== $normalizedExpiredDate;

                if ($hasChanged) {
                    $updates[] = [
                        'model' => $existingPrice,
                        'material_name' => $material->material_name,
                        'price' => $normalizedPrice,
                        'effective_date' => $row['effective_date'],
                        'expired_date' => $normalizedExpiredDate,
                    ];
                }

                continue;
            }

            $creates[] = [
                'material_id' => $materialId,
                'material_name' => $material->material_name,
                'price' => $normalizedPrice,
                'effective_date' => $row['effective_date'],
                'expired_date' => $normalizedExpiredDate,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'updates' => $updates,
            'creates' => $creates,
        ];
    }

    public function applyBulkUpdate(array $updates, array $creates, int $createdBy): void
    {
        DB::transaction(function () use ($updates, $creates, $createdBy) {
            foreach ($updates as $update) {
                $update['model']->update([
                    'price' => $update['price'],
                    'effective_date' => $update['effective_date'],
                    'expired_date' => $update['expired_date'],
                ]);
            }

            foreach ($creates as $create) {
                MaterialPrice::create([
                    'material_id' => $create['material_id'],
                    'price' => $create['price'],
                    'effective_date' => $create['effective_date'],
                    'expired_date' => $create['expired_date'],
                    'created_by' => $createdBy,
                    'created_at' => now(),
                ]);
            }
        });
    }

    public function touchedMaterialNames(array $updates, array $creates): Collection
    {
        return collect([...$updates, ...$creates])
            ->pluck('material_name')
            ->filter()
            ->values();
    }

    private function validateBulkRow(array $row, int $materialId): array
    {
        $validator = Validator::make([
            'price' => $row['price'],
            'effective_date' => $row['effective_date'],
            'expired_date' => $row['expired_date'],
        ], [
            'price' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'expired_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
        ], [
            'price.required' => 'กรุณากรอกราคา',
            'price.numeric' => 'ราคาต้องเป็นตัวเลข',
            'price.min' => 'ราคาต้องไม่น้อยกว่า 0',
            'effective_date.required' => 'กรุณาเลือกวันที่เริ่มใช้',
            'effective_date.date' => 'วันที่เริ่มใช้ไม่ถูกต้อง',
            'expired_date.date' => 'วันที่สิ้นสุดไม่ถูกต้อง',
            'expired_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มใช้',
        ]);

        if (! $validator->fails()) {
            return [];
        }

        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            $errors["rows.$materialId.$field"] = $messages[0];
        }

        return $errors;
    }

    private function isBlankRow(array $row): bool
    {
        return ($row['price_id'] ?? null) === null
            && ($row['price'] ?? '') === ''
            && ($row['effective_date'] ?? '') === ''
            && ($row['expired_date'] ?? '') === '';
    }

    private function pricePeriodOverlapMessage(
        int $materialId,
        string $effectiveDate,
        ?string $expiredDate,
        ?int $ignorePriceId = null
    ): ?string {
        $overlapQuery = MaterialPrice::query()
            ->where('material_id', $materialId)
            ->when($ignorePriceId, fn ($query) => $query->where('price_id', '!=', $ignorePriceId))
            ->when($expiredDate !== null, fn ($query) => $query->whereDate('effective_date', '<=', $expiredDate))
            ->where(function ($query) use ($effectiveDate) {
                $query->whereNull('expired_date')
                    ->orWhereDate('expired_date', '>=', $effectiveDate);
            });

        if (! $overlapQuery->exists()) {
            return null;
        }

        return 'ช่วงวันที่ราคาซ้อนทับกับรายการเดิมของวัสดุนี้ กรุณาปรับวันที่เริ่มใช้หรือวันหมดอายุ';
    }

    private function closePreviousOpenEndedPriceIfNeeded(int $materialId, string $effectiveDate): void
    {
        $previousPrice = MaterialPrice::query()
            ->where('material_id', $materialId)
            ->whereNull('expired_date')
            ->orderByDesc('effective_date')
            ->orderByDesc('price_id')
            ->first();

        $previousEffectiveDate = $previousPrice?->effective_date?->format('Y-m-d');

        if (! $previousPrice || $previousEffectiveDate === null || $previousEffectiveDate >= $effectiveDate) {
            return;
        }

        $previousPrice->expired_date = Carbon::parse($effectiveDate)->subDay()->toDateString();
        $previousPrice->save();
    }
}

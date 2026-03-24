<?php

namespace App\Support\MaterialPrices;

use Illuminate\Support\Collection;

class MaterialPriceEditorViewDataFactory
{
    public function buildRows(Collection $materials): Collection
    {
        return $materials->map(function ($material) {
            $materialKey = (string) $material->material_id;
            $initialPrice = $material->current_price_value !== null
                ? number_format((float) $material->current_price_value, 2, '.', '')
                : '';
            $initialEffectiveDate = $material->current_effective_date ?? '';
            $initialExpiredDate = $material->current_expired_date ?? '';

            return [
                'material_id' => (int) $material->material_id,
                'material_key' => $materialKey,
                'material_name' => $material->material_name,
                'unit' => $material->unit,
                'category_name' => $material->category_name,
                'is_active' => (bool) $material->is_active,
                'current_price_id' => $material->current_price_id,
                'has_current_price' => $material->current_price_id !== null,
                'initial_price' => $initialPrice,
                'initial_effective_date' => $initialEffectiveDate,
                'initial_expired_date' => $initialExpiredDate,
                'row_price' => old("rows.$materialKey.price", $initialPrice),
                'row_effective_date' => old("rows.$materialKey.effective_date", $initialEffectiveDate),
                'row_expired_date' => old("rows.$materialKey.expired_date", $initialExpiredDate),
            ];
        })->values();
    }
}

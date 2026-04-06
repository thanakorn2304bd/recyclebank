<?php

namespace App\Support\MaterialPrices;

use Illuminate\Support\Collection;

class MaterialPriceEditorViewDataFactory
{
    public function buildRows(Collection $materials): Collection
    {
        return $materials->map(function ($material) {
            $materialKey = (string) $material->material_id;
            $selectedMonthPrice = $material->selected_month_price_value !== null
                ? number_format((float) $material->selected_month_price_value, 2, '.', '')
                : '';
            $carryForwardPrice = $material->carry_forward_price_value !== null
                ? number_format((float) $material->carry_forward_price_value, 2, '.', '')
                : '';
            $initialPrice = $selectedMonthPrice !== '' ? $selectedMonthPrice : $carryForwardPrice;
            $hasSelectedMonthPrice = $material->selected_month_price_id !== null;
            $hasCarryForwardPrice = $material->carry_forward_price_value !== null;

            if ($hasSelectedMonthPrice) {
                $statusLabel = 'มีชุดราคาเดือนนี้แล้ว';
                $statusVariant = 'current';
                $sourceLabel = 'กำลังใช้ราคาที่เผยแพร่ไว้สำหรับเดือนนี้';
                $sourceMeta = $material->selected_month_effective_date
                    ? 'เริ่มใช้ '.$this->formatDate($material->selected_month_effective_date)
                    : 'บันทึกไว้แล้วในเดือนที่เลือก';
            } elseif ($hasCarryForwardPrice) {
                $statusLabel = 'พร้อมคัดลอกจากเดือนก่อน';
                $statusVariant = 'carry';
                $sourceLabel = 'หากไม่แก้ ระบบจะยกราคาจากเดือนก่อนมาสร้างชุดใหม่ให้';
                $sourceMeta = $material->carry_forward_effective_date
                    ? 'อ้างอิงราคาล่าสุดที่เริ่มใช้ '.$this->formatDate($material->carry_forward_effective_date)
                    : 'มีราคาตั้งต้นจากเดือนก่อน';
            } else {
                $statusLabel = 'ยังไม่มีราคาตั้งต้น';
                $statusVariant = 'missing';
                $sourceLabel = 'วัสดุนี้ยังไม่มีราคาจากเดือนก่อน';
                $sourceMeta = 'กรอกราคาเพื่อเริ่มชุดราคาเดือนนี้';
            }

            return [
                'material_id' => (int) $material->material_id,
                'material_key' => $materialKey,
                'material_name' => $material->material_name,
                'unit' => $material->unit,
                'category_name' => $material->category_name,
                'is_active' => (bool) $material->is_active,
                'current_price_id' => $material->selected_month_price_id,
                'has_current_price' => $hasSelectedMonthPrice,
                'has_carry_forward_price' => $hasCarryForwardPrice,
                'status_label' => $statusLabel,
                'status_variant' => $statusVariant,
                'source_label' => $sourceLabel,
                'source_meta' => $sourceMeta,
                'selected_month_price' => $selectedMonthPrice,
                'carry_forward_price' => $carryForwardPrice,
                'initial_price' => $initialPrice,
                'row_price' => old("rows.$materialKey.price", $initialPrice),
            ];
        })->values();
    }

    private function formatDate(?string $date): string
    {
        if (! is_string($date) || $date === '') {
            return '-';
        }

        [$year, $month, $day] = explode('-', $date);

        return sprintf('%s/%s/%s', $day, $month, $year);
    }
}

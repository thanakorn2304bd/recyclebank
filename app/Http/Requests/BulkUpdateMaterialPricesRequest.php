<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkUpdateMaterialPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:material_category,category_id'],
            'material_id' => ['nullable', 'integer', 'exists:material,material_id'],
            'target_month' => ['required', 'date_format:Y-m'],
            'rows' => ['nullable', 'array'],
            'rows.*' => ['array'],
            'rows.*.price_id' => ['nullable'],
            'rows.*.price' => ['nullable'],
        ];
    }

    public function rows(): array
    {
        return collect($this->input('rows', []))
            ->mapWithKeys(function ($row, $materialId) {
                return [
                    (int) $materialId => [
                        'price_id' => filled($row['price_id'] ?? null) ? (int) $row['price_id'] : null,
                        'price' => trim((string) ($row['price'] ?? '')),
                    ],
                ];
            })
            ->all();
    }

    public function targetMonth(): string
    {
        return (string) $this->validated('target_month');
    }

    public function editorFilters(): array
    {
        $validated = $this->validated();

        return array_filter([
            'target_month' => trim((string) ($validated['target_month'] ?? '')),
            'q' => trim((string) ($validated['q'] ?? '')),
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'material_id' => isset($validated['material_id']) ? (int) $validated['material_id'] : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function prepareForValidation(): void
    {
        $normalizedRows = collect($this->input('rows', []))
            ->map(function ($row) {
                return [
                    'price_id' => filled($row['price_id'] ?? null) ? trim((string) $row['price_id']) : null,
                    'price' => filled($row['price'] ?? null) ? trim((string) $row['price']) : '',
                ];
            })
            ->all();

        $this->merge([
            'target_month' => $this->filled('target_month') ? trim((string) $this->input('target_month')) : null,
            'q' => $this->filled('q') ? trim((string) $this->input('q')) : null,
            'category_id' => $this->filled('category_id') ? $this->input('category_id') : null,
            'material_id' => $this->filled('material_id') ? $this->input('material_id') : null,
            'rows' => $normalizedRows,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $targetMonth = $this->input('target_month');

            if (! is_string($targetMonth) || preg_match('/^\d{4}-\d{2}$/', $targetMonth) !== 1) {
                return;
            }

            try {
                $requestedMonth = CarbonImmutable::createFromFormat('Y-m', $targetMonth)->startOfMonth();
            } catch (\Throwable) {
                return;
            }

            if ($requestedMonth->lessThan($this->minimumTargetMonth())) {
                $validator->errors()->add('target_month', 'ไม่สามารถเผยแพร่ชุดราคาย้อนหลังไปเดือนก่อนหน้าได้');
            }
        });
    }

    private function minimumTargetMonth(): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfMonth();
    }
}

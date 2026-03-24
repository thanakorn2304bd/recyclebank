<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'rows' => ['nullable', 'array'],
            'rows.*' => ['array'],
            'rows.*.price_id' => ['nullable'],
            'rows.*.price' => ['nullable'],
            'rows.*.effective_date' => ['nullable'],
            'rows.*.expired_date' => ['nullable'],
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
                        'effective_date' => trim((string) ($row['effective_date'] ?? '')),
                        'expired_date' => trim((string) ($row['expired_date'] ?? '')),
                    ],
                ];
            })
            ->all();
    }

    public function editorFilters(): array
    {
        $validated = $this->validated();

        return array_filter([
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
                    'effective_date' => filled($row['effective_date'] ?? null) ? trim((string) $row['effective_date']) : '',
                    'expired_date' => filled($row['expired_date'] ?? null) ? trim((string) $row['expired_date']) : '',
                ];
            })
            ->all();

        $this->merge([
            'q' => $this->filled('q') ? trim((string) $this->input('q')) : null,
            'category_id' => $this->filled('category_id') ? $this->input('category_id') : null,
            'material_id' => $this->filled('material_id') ? $this->input('material_id') : null,
            'rows' => $normalizedRows,
        ]);
    }
}

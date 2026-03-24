<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionHistoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::in(['deposit', 'withdraw'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'household_id' => ['nullable', 'integer', 'exists:household,household_id'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'type' => trim((string) ($validated['type'] ?? '')),
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'household_id' => isset($validated['household_id']) ? (int) $validated['household_id'] : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->filled('type') ? trim((string) $this->input('type')) : null,
            'from' => $this->filled('from') ? trim((string) $this->input('from')) : null,
            'to' => $this->filled('to') ? trim((string) $this->input('to')) : null,
            'household_id' => $this->filled('household_id') ? $this->input('household_id') : null,
        ]);
    }
}

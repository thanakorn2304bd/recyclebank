<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionDateRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function range(): array
    {
        $validated = $this->validated();

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'from' => $this->filled('from') ? trim((string) $this->input('from')) : null,
            'to' => $this->filled('to') ? trim((string) $this->input('to')) : null,
        ]);
    }
}

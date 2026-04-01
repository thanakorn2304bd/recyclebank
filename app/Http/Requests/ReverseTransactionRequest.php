<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReverseTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reversal_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'reversal_date' => (string) $validated['reversal_date'],
            'reason' => trim((string) $validated['reason']),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reversal_date' => $this->filled('reversal_date') ? trim((string) $this->input('reversal_date')) : null,
            'reason' => trim((string) $this->input('reason', '')),
        ]);
    }
}

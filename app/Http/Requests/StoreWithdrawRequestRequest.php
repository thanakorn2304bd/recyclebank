<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_for_date' => ['required', 'date'],
            'requested_amount' => ['required', 'numeric', 'min:0.01'],
            'request_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'requested_for_date' => (string) $validated['requested_for_date'],
            'requested_amount' => round((float) $validated['requested_amount'], 2),
            'request_notes' => $this->filled('request_notes')
                ? trim((string) $validated['request_notes'])
                : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'request_notes' => $this->filled('request_notes')
                ? trim((string) $this->input('request_notes'))
                : null,
        ]);
    }
}

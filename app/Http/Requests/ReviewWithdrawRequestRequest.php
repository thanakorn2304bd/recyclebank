<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewWithdrawRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'transaction_date' => ['nullable', 'date', 'required_if:decision,approved'],
            'review_notes' => ['nullable', 'string', 'max:2000', 'required_if:decision,rejected'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'decision' => (string) $validated['decision'],
            'transaction_date' => $validated['transaction_date'] ?? null,
            'review_notes' => $this->filled('review_notes')
                ? trim((string) $validated['review_notes'])
                : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'review_notes' => $this->filled('review_notes')
                ? trim((string) $this->input('review_notes'))
                : null,
        ]);
    }
}

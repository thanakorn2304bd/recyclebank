<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'review_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'status' => (string) $validated['status'],
            'review_notes' => trim((string) $validated['review_notes']),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->filled('status') ? trim((string) $this->input('status')) : null,
            'review_notes' => trim((string) $this->input('review_notes', '')),
        ]);
    }
}

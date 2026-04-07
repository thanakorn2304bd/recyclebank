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
        $requiresReviewNotes = in_array($this->input('status'), ['inactive', 'rejected'], true);

        return [
            'status' => ['required', Rule::in(['active', 'inactive', 'rejected'])],
            'review_notes' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf($requiresReviewNotes),
                ...(($this->filled('review_notes') || $requiresReviewNotes) ? ['min:5'] : []),
            ],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();
        $reviewNotes = $validated['review_notes'] ?? null;

        return [
            'status' => (string) $validated['status'],
            'review_notes' => $reviewNotes !== null ? trim((string) $reviewNotes) : '',
        ];
    }

    protected function prepareForValidation(): void
    {
        $reviewNotes = trim((string) $this->input('review_notes', ''));

        $this->merge([
            'status' => $this->filled('status') ? trim((string) $this->input('status')) : null,
            'review_notes' => $reviewNotes !== '' ? $reviewNotes : null,
        ]);
    }
}

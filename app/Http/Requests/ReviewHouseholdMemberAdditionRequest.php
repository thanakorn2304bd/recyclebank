<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewHouseholdMemberAdditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiresReviewNotes = $this->input('decision') === 'rejected';

        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
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

        return [
            'decision' => (string) $validated['decision'],
            'review_notes' => $this->filled('review_notes')
                ? trim((string) $validated['review_notes'])
                : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'decision' => $this->filled('decision') ? trim((string) $this->input('decision')) : null,
            'review_notes' => $this->filled('review_notes')
                ? trim((string) $this->input('review_notes'))
                : null,
        ]);
    }
}

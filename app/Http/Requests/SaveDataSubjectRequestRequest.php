<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDataSubjectRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['nullable', 'integer', 'exists:household,household_id'],
            'requester_name' => ['required', 'string', 'max:100'],
            'requester_contact' => ['nullable', 'string', 'max:150'],
            'request_type' => ['required', Rule::in(['access', 'correction', 'deletion', 'restriction', 'objection'])],
            'status' => ['required', Rule::in(['submitted', 'in_review', 'completed', 'rejected'])],
            'submitted_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:submitted_at'],
            'assigned_to' => ['nullable', 'integer', 'exists:user_account,user_id'],
            'request_details' => ['required', 'string', 'min:10', 'max:4000'],
            'resolution_notes' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'household_id' => $validated['household_id'] ?? null,
            'requester_name' => trim((string) $validated['requester_name']),
            'requester_contact' => $this->filled('requester_contact') ? trim((string) $validated['requester_contact']) : null,
            'request_type' => (string) $validated['request_type'],
            'status' => (string) $validated['status'],
            'submitted_at' => (string) $validated['submitted_at'],
            'due_at' => $validated['due_at'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'request_details' => trim((string) $validated['request_details']),
            'resolution_notes' => $this->filled('resolution_notes') ? trim((string) $validated['resolution_notes']) : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requester_name' => trim((string) $this->input('requester_name', '')),
            'requester_contact' => $this->filled('requester_contact') ? trim((string) $this->input('requester_contact')) : null,
            'request_details' => trim((string) $this->input('request_details', '')),
            'resolution_notes' => $this->filled('resolution_notes') ? trim((string) $this->input('resolution_notes')) : null,
        ]);
    }
}

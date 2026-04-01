<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSecurityIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['open', 'contained', 'reported', 'closed'])],
            'assigned_to' => ['nullable', 'integer', 'exists:user_account,user_id'],
            'occurred_at' => ['nullable', 'date'],
            'detected_at' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:255'],
            'affected_scope' => ['nullable', 'string', 'max:255'],
            'affected_record_count' => ['nullable', 'integer', 'min:0'],
            'notification_required' => ['nullable', 'boolean'],
            'authority_notified_at' => ['nullable', 'date'],
            'subject_notified_at' => ['nullable', 'date'],
            'impact_details' => ['nullable', 'string', 'max:4000'],
            'containment_actions' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'severity' => (string) $validated['severity'],
            'status' => (string) $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'occurred_at' => $validated['occurred_at'] ?? null,
            'detected_at' => (string) $validated['detected_at'],
            'summary' => trim((string) $validated['summary']),
            'affected_scope' => $this->filled('affected_scope') ? trim((string) $validated['affected_scope']) : null,
            'affected_record_count' => $validated['affected_record_count'] ?? null,
            'notification_required' => $this->boolean('notification_required'),
            'authority_notified_at' => $validated['authority_notified_at'] ?? null,
            'subject_notified_at' => $validated['subject_notified_at'] ?? null,
            'impact_details' => $this->filled('impact_details') ? trim((string) $validated['impact_details']) : null,
            'containment_actions' => $this->filled('containment_actions') ? trim((string) $validated['containment_actions']) : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'summary' => trim((string) $this->input('summary', '')),
            'affected_scope' => $this->filled('affected_scope') ? trim((string) $this->input('affected_scope')) : null,
            'impact_details' => $this->filled('impact_details') ? trim((string) $this->input('impact_details')) : null,
            'containment_actions' => $this->filled('containment_actions') ? trim((string) $this->input('containment_actions')) : null,
            'notification_required' => $this->boolean('notification_required'),
        ]);
    }
}

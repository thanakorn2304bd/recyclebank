<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityLogFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', Rule::in(['admin', 'staff', 'member'])],
            'user_id' => ['nullable', 'integer', 'exists:user_account,user_id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',
            'user_id.exists' => 'ไม่พบบัญชีผู้ใช้ที่เลือก',
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'module' => trim((string) ($validated['module'] ?? '')),
            'role' => trim((string) ($validated['role'] ?? '')),
            'user_id' => isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => $this->filled('q') ? trim((string) $this->input('q')) : null,
            'module' => $this->filled('module') ? trim((string) $this->input('module')) : null,
            'role' => $this->filled('role') ? trim((string) $this->input('role')) : null,
            'user_id' => $this->filled('user_id') ? $this->input('user_id') : null,
            'from' => $this->filled('from') ? trim((string) $this->input('from')) : null,
            'to' => $this->filled('to') ? trim((string) $this->input('to')) : null,
        ]);
    }
}

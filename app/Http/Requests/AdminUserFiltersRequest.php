<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['admin', 'staff', 'member'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'role' => trim((string) ($validated['role'] ?? '')),
            'status' => trim((string) ($validated['status'] ?? '')),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => $this->filled('q') ? trim((string) $this->input('q')) : null,
            'role' => $this->filled('role') ? trim((string) $this->input('role')) : null,
            'status' => $this->filled('status') ? trim((string) $this->input('status')) : null,
        ]);
    }
}

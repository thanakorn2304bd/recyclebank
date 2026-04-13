<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name', '')),
            'phone' => trim((string) $this->input('phone', '')) ?: null,
            'position' => trim((string) $this->input('position', '')) ?: null,
        ]);
    }
}

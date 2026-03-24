<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:material_category,category_id'],
            'material_name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->missing('description') || $this->input('description') === null) {
            $this->merge([
                'description' => '',
            ]);
        }
    }
}

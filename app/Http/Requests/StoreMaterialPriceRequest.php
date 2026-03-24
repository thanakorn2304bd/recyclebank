<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_id' => ['required', 'integer', 'exists:material,material_id'],
            'price' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'expired_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'ราคาต้องไม่น้อยกว่า 0',
            'expired_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มใช้',
        ];
    }

    public function payload(): array
    {
        return [
            'material_id' => (int) $this->validated('material_id'),
            'price' => number_format((float) $this->validated('price'), 2, '.', ''),
            'effective_date' => (string) $this->validated('effective_date'),
            'expired_date' => $this->validated('expired_date') ?: null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'material_id' => $this->filled('material_id') ? $this->input('material_id') : null,
            'price' => $this->filled('price') ? trim((string) $this->input('price')) : null,
            'effective_date' => $this->filled('effective_date') ? trim((string) $this->input('effective_date')) : null,
            'expired_date' => $this->filled('expired_date') ? trim((string) $this->input('expired_date')) : null,
        ]);
    }
}

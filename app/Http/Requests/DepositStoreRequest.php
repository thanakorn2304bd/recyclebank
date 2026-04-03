<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositStoreRequest extends FormRequest
{
    private const MAX_DECIMAL_VALUE = 99999999.99;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'community_id' => ['required', 'string', 'max:2'],
            'house_no' => ['required', 'string', 'max:20'],
            'transaction_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'integer', 'exists:material,material_id'],
            'items.*.weight' => ['required', 'numeric', 'min:0.01', 'max:'.self::MAX_DECIMAL_VALUE],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.weight.max' => 'น้ำหนักต่อรายการต้องไม่เกิน '.number_format(self::MAX_DECIMAL_VALUE, 2),
        ];
    }

    protected function prepareForValidation(): void
    {
        $communityId = trim((string) $this->input('community_id', ''));
        if (ctype_digit($communityId)) {
            $communityId = str_pad($communityId, 2, '0', STR_PAD_LEFT);
        }

        $this->merge([
            'community_id' => $communityId,
            'house_no' => trim((string) $this->input('house_no', '')),
        ]);
    }
}

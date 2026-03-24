<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LookupHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'community_id' => ['required', 'string', 'max:2'],
            'house_no' => ['required', 'string', 'max:20'],
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

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'found' => false,
            'message' => 'กรุณากรอกเลขที่ชุมชนและบ้านเลขที่ให้ครบถ้วน',
        ], 422));
    }
}

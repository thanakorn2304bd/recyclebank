<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $communityId = $this->route('community')?->community_id;

        return [
            'community_id' => [
                'required',
                'string',
                'size:2',
                Rule::unique('community', 'community_id')->ignore($communityId, 'community_id'),
            ],
            'community_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'community_id.size' => 'รหัสชุมชนต้องมี 2 ตัวอักษรพอดี',
            'community_id.unique' => 'รหัสชุมชนนี้ถูกใช้งานแล้ว',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'community_id' => strtoupper(trim((string) $this->input('community_id', ''))),
            'community_name' => trim((string) $this->input('community_name', '')),
        ]);
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Household $household */
        $household = $this->route('household');

        return [
            'account_no' => [
                'required',
                'string',
                'max:10',
                Rule::unique('household', 'account_no')->ignore($household->household_id, 'household_id'),
            ],
            'house_no' => ['required', 'string', 'max:20'],
            'village_no' => ['nullable', 'string', 'max:10'],
            'community_id' => ['required', 'string', 'exists:community,community_id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['required', 'string', 'max:100'],
            'register_date' => ['required', 'date'],
            'accumulated_months' => ['required', 'integer', 'min:0'],
        ];
    }

    public function householdAttributes(): array
    {
        return $this->validated();
    }

    public function after(): array
    {
        return [
            function ($validator) {
                /** @var Household $household */
                $household = $this->route('household');
                $memberAccount = UserAccount::query()
                    ->where('household_id', $household->household_id)
                    ->where('role', 'member')
                    ->orderBy('user_id')
                    ->first();

                $query = UserAccount::query()
                    ->where('username', (string) $this->input('account_no'));

                if ($memberAccount) {
                    $query->where('user_id', '!=', $memberAccount->user_id);
                }

                if ($query->exists()) {
                    $validator->errors()->add('account_no', 'เลขบัญชีนี้ชนกับชื่อผู้ใช้ที่มีอยู่ในระบบ กรุณาเปลี่ยนเลขบัญชี');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_no' => trim((string) $this->input('account_no', '')),
            'house_no' => trim((string) $this->input('house_no', '')),
            'village_no' => $this->filled('village_no') ? trim((string) $this->input('village_no')) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'contact_person' => trim((string) $this->input('contact_person', '')),
        ]);
    }
}

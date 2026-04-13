<?php

namespace App\Http\Requests;

use App\Models\Household;
use App\Models\Member;
use App\Models\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
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
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['nullable', 'string', 'max:100'],
            'members.*.id_card' => ['nullable', 'string', 'max:20'],
            'members.*.relation' => ['nullable', 'string', 'max:50'],
            'members.*.is_head' => ['nullable'],
        ];
    }

    public function householdAttributes(): array
    {
        return Arr::except($this->validated(), 'members');
    }

    public function members(): array
    {
        return $this->normalizedMembers();
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

                $memberValidator = Validator::make([
                    'members' => $this->normalizedMembers(),
                ], [
                    'members' => ['nullable', 'array'],
                    'members.*.full_name' => ['required', 'string', 'max:100'],
                    'members.*.id_card' => ['required', 'digits:13', 'distinct'],
                    'members.*.relation' => ['required', 'string', 'max:50'],
                    'members.*.is_head' => ['nullable', 'boolean'],
                ], [
                    'members.*.full_name.required' => 'กรุณากรอกชื่อสมาชิกในครัวเรือนให้ครบ',
                    'members.*.id_card.required' => 'กรุณากรอกเลขบัตรประชาชนของสมาชิกให้ครบ',
                    'members.*.id_card.digits' => 'เลขบัตรประชาชนของสมาชิกต้องมี 13 หลัก',
                    'members.*.id_card.distinct' => 'เลขบัตรประชาชนของสมาชิกห้ามซ้ำกัน',
                    'members.*.relation.required' => 'กรุณากรอกความสัมพันธ์ของสมาชิกให้ครบ',
                ]);

                $normalizedMembers = $this->normalizedMembers();
                $householdId = $this->route('household')?->household_id;
                $memberValidator->after(function ($memberValidator) use ($normalizedMembers, $householdId) {
                    if (collect($normalizedMembers)->where('is_head', true)->count() > 1) {
                        $memberValidator->errors()->add('members', 'เลือกหัวหน้าครัวเรือนได้เพียง 1 คน');
                    }

                    foreach ($normalizedMembers as $index => $member) {
                        if ($member['id_card_hash'] && Member::where('id_card_hash', $member['id_card_hash'])
                            ->where('household_id', '!=', $householdId)
                            ->exists()) {
                            $memberValidator->errors()->add(
                                'members.'.$index.'.id_card',
                                'เลขบัตรประชาชนนี้มีอยู่ในระบบแล้ว'
                            );
                        }
                    }
                });
                $memberValidator->passes();

                foreach ($memberValidator->errors()->messages() as $key => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($key, $message);
                    }
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

    private function normalizedMembers(): array
    {
        return collect($this->input('members', []))
            ->map(function ($member) {
                return [
                    'full_name' => trim((string) ($member['full_name'] ?? '')),
                    'id_card' => Member::normalizeIdCard((string) ($member['id_card'] ?? '')),
                    'id_card_last4' => Member::extractIdCardLast4((string) ($member['id_card'] ?? '')),
                    'id_card_hash' => Member::hashIdCard((string) ($member['id_card'] ?? '')),
                    'relation' => trim((string) ($member['relation'] ?? '')),
                    'is_head' => filter_var($member['is_head'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(function ($member) {
                return $member['full_name'] !== ''
                    || $member['id_card'] !== ''
                    || $member['relation'] !== ''
                    || $member['is_head'];
            })
            ->values()
            ->all();
    }
}

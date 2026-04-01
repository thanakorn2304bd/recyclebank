<?php

namespace App\Http\Requests;

use App\Models\Household;
use App\Models\Member;
use App\Models\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class StoreHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_no' => ['nullable', 'string', 'size:10'],
            'house_no' => ['required', 'string', 'max:20', 'regex:/\d/'],
            'village_no' => ['nullable', 'string', 'max:10'],
            'community_id' => ['required', 'string', 'exists:community,community_id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['required', 'string', 'max:100'],
            'register_date' => ['required', 'date'],
            'active_status' => ['required', 'in:pending,active,inactive'],
            'accumulated_months' => ['required', 'integer', 'min:0'],
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['nullable', 'string', 'max:100'],
            'members.*.id_card' => ['nullable', 'string', 'max:20'],
            'members.*.relation' => ['nullable', 'string', 'max:50'],
            'members.*.is_head' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_no.size' => 'เลขบัญชีต้องมี 10 หลัก',
            'house_no.regex' => 'บ้านเลขที่ต้องมีตัวเลขอย่างน้อย 1 หลัก',
        ];
    }

    public function householdAttributes(): array
    {
        $data = Arr::except($this->validated(), 'members');
        $data['account_no'] = $this->resolvedAccountNo();

        return $data;
    }

    public function members(): array
    {
        return $this->normalizedMembers();
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $hasAccountInputsError = collect(['account_no', 'community_id', 'house_no'])
                    ->contains(fn (string $field) => $validator->errors()->has($field));

                if (! $hasAccountInputsError) {
                    $accountNo = $this->resolvedAccountNo();

                    if ($accountNo === '') {
                        $validator->errors()->add('account_no', 'กรุณากรอกเลขบัญชี');
                    } elseif (Household::query()->where('account_no', $accountNo)->exists()) {
                        $validator->errors()->add('account_no', 'เลขบัญชีนี้มีอยู่แล้ว กรุณาตรวจสอบเลขที่ชุมชนหรือบ้านเลขที่');
                    }

                    if ($accountNo !== '' && UserAccount::query()->where('username', $accountNo)->exists()) {
                        $validator->errors()->add('account_no', 'เลขบัญชีนี้ชนกับชื่อผู้ใช้ที่มีอยู่ในระบบ กรุณาแก้เลขบัญชีก่อนดำเนินการต่อ');
                    }
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
                $memberValidator->after(function ($memberValidator) use ($normalizedMembers) {
                    if ($normalizedMembers === []) {
                        $memberValidator->errors()->add('members', 'กรุณาเพิ่มสมาชิกในครัวเรือนอย่างน้อย 1 คน');
                    }

                    if (collect($normalizedMembers)->where('is_head', true)->count() > 1) {
                        $memberValidator->errors()->add('members', 'เลือกหัวหน้าครัวเรือนได้เพียง 1 คน');
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
            'account_no' => $this->filled('account_no') ? trim((string) $this->input('account_no')) : null,
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

    private function resolvedAccountNo(): string
    {
        $accountNo = trim((string) $this->input('account_no', ''));

        if ($accountNo !== '') {
            return $accountNo;
        }

        $communityId = (string) $this->input('community_id', '');
        $houseNo = (string) $this->input('house_no', '');
        $houseDigits = preg_replace('/\D+/', '', $houseNo) ?? '';

        if ($communityId === '' || $houseDigits === '') {
            return '';
        }

        return now()->format('Y').$communityId.str_pad(substr($houseDigits, -4), 4, '0', STR_PAD_LEFT);
    }
}

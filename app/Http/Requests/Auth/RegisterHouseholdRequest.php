<?php

namespace App\Http\Requests\Auth;

use App\Models\Household;
use App\Models\Member;
use App\Models\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class RegisterHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'community_id' => ['required', 'string', 'exists:community,community_id'],
            'house_no' => ['required', 'string', 'max:20', 'regex:/\d/'],
            'village_no' => ['nullable', 'string', 'max:10'],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['nullable', 'string', 'max:100'],
            'members.*.id_card' => ['nullable', 'string', 'max:20'],
            'members.*.relation' => ['nullable', 'string', 'max:50'],
            'members.*.is_head' => ['nullable'],
            'accepted_privacy_notice' => ['accepted'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'house_no.regex' => 'บ้านเลขที่ต้องมีตัวเลขอย่างน้อย 1 หลัก',
            'accepted_privacy_notice.accepted' => 'กรุณารับทราบประกาศคุ้มครองข้อมูลส่วนบุคคลก่อนสมัครสมาชิก',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ];
    }

    public function householdAttributes(): array
    {
        return [
            'account_no' => $this->resolvedAccountNo(),
            'house_no' => trim((string) $this->input('house_no', '')),
            'village_no' => $this->filled('village_no') ? trim((string) $this->input('village_no')) : null,
            'community_id' => (string) $this->input('community_id', ''),
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'contact_person' => trim((string) $this->input('contact_person', '')),
            'register_date' => now()->toDateString(),
            'active_status' => 'pending',
            'accumulated_months' => 0,
            'total_balance' => 0.00,
            'created_by' => null,
        ];
    }

    public function members(): array
    {
        return $this->normalizedMembers();
    }

    public function password(): string
    {
        return (string) $this->validated('password');
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $hasAccountInputsError = collect(['community_id', 'house_no'])
                    ->contains(fn (string $field) => $validator->errors()->has($field));

                if (! $hasAccountInputsError) {
                    $accountNo = $this->resolvedAccountNo();

                    if ($accountNo === '') {
                        $validator->errors()->add('house_no', 'ไม่สามารถสร้างเลขบัญชีจากข้อมูลที่กรอกได้');
                    } elseif (Household::query()->where('account_no', $accountNo)->exists()) {
                        $validator->errors()->add('house_no', 'เลขบัญชีนี้ถูกใช้งานแล้ว กรุณาตรวจสอบข้อมูลอีกครั้ง');
                    }

                    if ($accountNo !== '' && UserAccount::query()->where('username', $accountNo)->exists()) {
                        $validator->errors()->add('house_no', 'เลขบัญชีนี้ถูกใช้งานเป็นชื่อผู้ใช้แล้ว กรุณาตรวจสอบข้อมูลอีกครั้ง');
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
            'house_no' => trim((string) $this->input('house_no', '')),
            'village_no' => $this->filled('village_no') ? trim((string) $this->input('village_no')) : null,
            'contact_person' => trim((string) $this->input('contact_person', '')),
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
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
        $communityId = (string) $this->input('community_id', '');
        $houseNo = (string) $this->input('house_no', '');
        $houseDigits = preg_replace('/\D+/', '', $houseNo) ?? '';

        if ($communityId === '' || $houseDigits === '') {
            return '';
        }

        return now()->format('Y').$communityId.str_pad(substr($houseDigits, -4), 4, '0', STR_PAD_LEFT);
    }
}

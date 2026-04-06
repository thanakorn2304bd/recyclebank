<?php

namespace App\Http\Requests;

use App\Models\Household;
use App\Models\Member;
use App\Support\Households\HouseholdMemberAdditionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class StoreHouseholdMemberAdditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'members' => ['required', 'array', 'min:1'],
            'members.*.full_name' => ['nullable', 'string', 'max:100'],
            'members.*.id_card' => ['nullable', 'string', 'max:20'],
            'members.*.relation' => ['nullable', 'string', 'max:50'],
            'member_household_copies' => ['required', 'array', 'min:1'],
            'member_household_copies.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'member_national_id_copies' => ['required', 'array', 'min:1'],
            'member_national_id_copies.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'members.required' => 'กรุณาเพิ่มสมาชิกใหม่อย่างน้อย 1 คน',
            'members.array' => 'ข้อมูลสมาชิกใหม่ไม่ถูกต้อง',
            'members.min' => 'กรุณาเพิ่มสมาชิกใหม่อย่างน้อย 1 คน',
            'member_household_copies.required' => 'กรุณาอัปโหลดสำเนาทะเบียนบ้านของสมาชิกใหม่ทุกคน',
            'member_household_copies.array' => 'ข้อมูลสำเนาทะเบียนบ้านของสมาชิกใหม่ไม่ถูกต้อง',
            'member_household_copies.min' => 'กรุณาอัปโหลดสำเนาทะเบียนบ้านของสมาชิกใหม่อย่างน้อย 1 คน',
            'member_household_copies.*.mimes' => 'สำเนาทะเบียนบ้านของสมาชิกใหม่ต้องเป็นไฟล์ JPG, PNG หรือ PDF',
            'member_household_copies.*.max' => 'สำเนาทะเบียนบ้านของสมาชิกใหม่ต้องมีขนาดไม่เกิน 5 MB',
            'member_national_id_copies.required' => 'กรุณาอัปโหลดสำเนาบัตรประชาชนของสมาชิกใหม่ทุกคน',
            'member_national_id_copies.array' => 'ข้อมูลสำเนาบัตรประชาชนของสมาชิกใหม่ไม่ถูกต้อง',
            'member_national_id_copies.min' => 'กรุณาอัปโหลดสำเนาบัตรประชาชนของสมาชิกใหม่อย่างน้อย 1 คน',
            'member_national_id_copies.*.mimes' => 'สำเนาบัตรประชาชนของสมาชิกใหม่ต้องเป็นไฟล์ JPG, PNG หรือ PDF',
            'member_national_id_copies.*.max' => 'สำเนาบัตรประชาชนของสมาชิกใหม่ต้องมีขนาดไม่เกิน 5 MB',
        ];
    }

    public function members(): array
    {
        return $this->normalizedMembers();
    }

    public function documentUploads(): array
    {
        return [
            'member_household_copies' => $this->file('member_household_copies', []),
            'member_national_id_copies' => $this->file('member_national_id_copies', []),
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                /** @var Household $household */
                $household = $this->route('household');

                $memberValidator = Validator::make([
                    'members' => $this->normalizedMembers(),
                ], [
                    'members' => ['required', 'array', 'min:1'],
                    'members.*.full_name' => ['required', 'string', 'max:100'],
                    'members.*.id_card' => ['required', 'digits:13', 'distinct'],
                    'members.*.relation' => ['required', 'string', 'max:50'],
                ], [
                    'members.*.full_name.required' => 'กรุณากรอกชื่อสมาชิกใหม่ให้ครบ',
                    'members.*.id_card.required' => 'กรุณากรอกเลขบัตรประชาชนของสมาชิกใหม่ให้ครบ',
                    'members.*.id_card.digits' => 'เลขบัตรประชาชนของสมาชิกใหม่ต้องมี 13 หลัก',
                    'members.*.id_card.distinct' => 'เลขบัตรประชาชนของสมาชิกใหม่ห้ามซ้ำกัน',
                    'members.*.relation.required' => 'กรุณากรอกความสัมพันธ์ของสมาชิกใหม่ให้ครบ',
                ]);
                $memberValidator->passes();

                foreach ($memberValidator->errors()->messages() as $key => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($key, $message);
                    }
                }

                $normalizedMembers = $this->normalizedMembers();
                $existingIds = $household->members()
                    ->get()
                    ->map(fn ($member) => Member::normalizeIdCard((string) $member->id_card))
                    ->filter()
                    ->values()
                    ->all();

                foreach ($normalizedMembers as $index => $member) {
                    $label = $member['full_name'] !== ''
                        ? $member['full_name']
                        : 'สมาชิกใหม่คนที่ '.($index + 1);

                    if (in_array($member['id_card'], $existingIds, true)) {
                        $validator->errors()->add(
                            'members.'.$index.'.id_card',
                            'เลขบัตรประชาชนของ '.$label.' มีอยู่ในครัวเรือนแล้ว'
                        );
                    }

                    if (! $this->hasFile('member_household_copies.'.$index)) {
                        $validator->errors()->add(
                            'member_household_copies.'.$index,
                            'กรุณาอัปโหลดสำเนาทะเบียนบ้านของ '.$label
                        );
                    }

                    if (! $this->hasFile('member_national_id_copies.'.$index)) {
                        $validator->errors()->add(
                            'member_national_id_copies.'.$index,
                            'กรุณาอัปโหลดสำเนาบัตรประชาชนของ '.$label
                        );
                    }
                }

                if (app(HouseholdMemberAdditionService::class)->openRequestFor($household)) {
                    $validator->errors()->add(
                        'members',
                        'ขณะนี้มีคำขอเพิ่มสมาชิกที่ยังรอเจ้าหน้าที่พิจารณาอยู่แล้ว'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'members' => collect($this->input('members', []))
                ->map(fn ($member) => [
                    'full_name' => trim((string) ($member['full_name'] ?? '')),
                    'id_card' => trim((string) ($member['id_card'] ?? '')),
                    'relation' => trim((string) ($member['relation'] ?? '')),
                ])
                ->all(),
        ]);
    }

    private function normalizedMembers(): array
    {
        return collect($this->input('members', []))
            ->map(function ($member) {
                $idCard = Member::normalizeIdCard((string) ($member['id_card'] ?? ''));

                return [
                    'full_name' => trim((string) ($member['full_name'] ?? '')),
                    'id_card' => $idCard,
                    'id_card_last4' => Member::extractIdCardLast4($idCard),
                    'id_card_hash' => Member::hashIdCard($idCard),
                    'relation' => trim((string) ($member['relation'] ?? '')),
                ];
            })
            ->filter(fn ($member) => $member['full_name'] !== '' || $member['id_card'] !== '' || $member['relation'] !== '')
            ->values()
            ->all();
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UploadRegistrationDocumentsRequest extends FormRequest
{
    private const PENDING_REGISTRATION_SESSION_KEY = 'auth.pending_household_registration';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_household_copies' => ['required', 'array', 'min:1'],
            'member_household_copies.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'member_national_id_copies' => ['required', 'array', 'min:1'],
            'member_national_id_copies.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_household_copies.required' => 'กรุณาอัปโหลดสำเนาทะเบียนบ้านของสมาชิกทุกคน',
            'member_household_copies.array' => 'ข้อมูลสำเนาทะเบียนบ้านของสมาชิกไม่ถูกต้อง',
            'member_household_copies.min' => 'กรุณาอัปโหลดสำเนาทะเบียนบ้านของสมาชิกอย่างน้อย 1 คน',
            'member_household_copies.*.required' => 'กรุณาอัปโหลดสำเนาทะเบียนบ้านของสมาชิกให้ครบทุกคน',
            'member_household_copies.*.mimes' => 'สำเนาทะเบียนบ้านของสมาชิกต้องเป็นไฟล์ JPG, PNG หรือ PDF',
            'member_household_copies.*.max' => 'สำเนาทะเบียนบ้านของสมาชิกต้องมีขนาดไม่เกิน 5 MB',
            'member_national_id_copies.required' => 'กรุณาอัปโหลดสำเนาบัตรประจำตัวประชาชนของสมาชิกทุกคน',
            'member_national_id_copies.array' => 'ข้อมูลสำเนาบัตรประจำตัวประชาชนของสมาชิกไม่ถูกต้อง',
            'member_national_id_copies.min' => 'กรุณาอัปโหลดสำเนาบัตรประจำตัวประชาชนของสมาชิกอย่างน้อย 1 คน',
            'member_national_id_copies.*.required' => 'กรุณาอัปโหลดสำเนาบัตรประจำตัวประชาชนของสมาชิกให้ครบทุกคน',
            'member_national_id_copies.*.mimes' => 'สำเนาบัตรประจำตัวประชาชนของสมาชิกต้องเป็นไฟล์ JPG, PNG หรือ PDF',
            'member_national_id_copies.*.max' => 'สำเนาบัตรประจำตัวประชาชนของสมาชิกต้องมีขนาดไม่เกิน 5 MB',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $members = $this->pendingRegistrationMembers();

                if ($members === []) {
                    return;
                }

                foreach ($members as $index => $member) {
                    $memberName = trim((string) ($member['full_name'] ?? ''));
                    $label = $memberName !== '' ? $memberName : 'สมาชิกคนที่ '.($index + 1);

                    if (! $this->hasFile('member_household_copies.'.$index)) {
                        $validator->errors()->add(
                            'member_household_copies.'.$index,
                            'กรุณาอัปโหลดสำเนาทะเบียนบ้านของ '.$label
                        );
                    }

                    if (! $this->hasFile('member_national_id_copies.'.$index)) {
                        $validator->errors()->add(
                            'member_national_id_copies.'.$index,
                            'กรุณาอัปโหลดสำเนาบัตรประจำตัวประชาชนของ '.$label
                        );
                    }
                }
            },
        ];
    }

    private function pendingRegistrationMembers(): array
    {
        $registration = $this->session()->get(self::PENDING_REGISTRATION_SESSION_KEY);
        $members = $registration['members'] ?? [];

        return is_array($members) ? array_values($members) : [];
    }
}

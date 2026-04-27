<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffAccountRequest extends FormRequest
{
    protected $errorBag = 'createStaffAccount';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => ['required', Rule::exists('staff', 'staff_id')],
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('user_account', 'username'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'กรุณาเลือกเจ้าหน้าที่',
            'staff_id.exists' => 'ไม่พบเจ้าหน้าที่ที่เลือก',
            'username.required' => 'กรุณากรอกชื่อผู้ใช้',
            'username.unique' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว',
            'username.regex' => 'ชื่อผู้ใช้ใช้ได้เฉพาะตัวอักษรอังกฤษ ตัวเลข จุด ขีดล่าง และขีดกลาง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
            'account_status.required' => 'กรุณาเลือกสถานะบัญชี',
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'staff_id' => (int) $validated['staff_id'],
            'username' => trim((string) $validated['username']),
            'password' => (string) $validated['password'],
            'is_active' => $validated['account_status'] === 'active',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'staff_id' => $this->filled('staff_id') ? (int) $this->input('staff_id') : null,
            'username' => trim((string) $this->input('username', '')),
            'account_status' => $this->filled('account_status') ? trim((string) $this->input('account_status')) : null,
        ]);
    }
}

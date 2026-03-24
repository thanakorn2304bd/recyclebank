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
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:50'],
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
            'full_name.required' => 'กรุณากรอกชื่อเจ้าหน้าที่',
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
            'full_name' => trim((string) $validated['full_name']),
            'phone' => $this->filled('phone') ? trim((string) $validated['phone']) : null,
            'position' => $this->filled('position') ? trim((string) $validated['position']) : null,
            'username' => trim((string) $validated['username']),
            'password' => (string) $validated['password'],
            'is_active' => $validated['account_status'] === 'active',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name', '')),
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'position' => $this->filled('position') ? trim((string) $this->input('position')) : null,
            'username' => trim((string) $this->input('username', '')),
            'account_status' => $this->filled('account_status') ? trim((string) $this->input('account_status')) : null,
        ]);
    }
}

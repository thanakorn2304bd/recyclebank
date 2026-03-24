<?php

namespace App\Http\Requests;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdCredentialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ];
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
                    ->where('username', $household->account_no);

                if ($memberAccount) {
                    $query->where('user_id', '!=', $memberAccount->user_id);
                }

                if ($query->exists()) {
                    $validator->errors()->add('password', 'เลขบัญชีนี้ชนกับชื่อผู้ใช้ที่มีอยู่ในระบบ กรุณาแก้เลขบัญชีก่อนตั้งรหัสผ่าน');
                }
            },
        ];
    }
}

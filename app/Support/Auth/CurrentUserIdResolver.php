<?php

namespace App\Support\Auth;

use Illuminate\Http\Request;

class CurrentUserIdResolver
{
    public function resolve(Request $request): int
    {
        $userId = $request->user()?->user_id;

        if (! $userId) {
            abort(403, 'ไม่พบบัญชีผู้ใช้ที่เข้าสู่ระบบ');
        }

        return (int) $userId;
    }
}

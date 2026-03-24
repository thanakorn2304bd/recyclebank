<?php

namespace App\Support\AdminUsers;

class AdminUserViewDataFactory
{
    public function roleLabels(): array
    {
        return [
            'admin' => 'ผู้ดูแลระบบ',
            'staff' => 'เจ้าหน้าที่',
            'member' => 'สมาชิก',
        ];
    }
}

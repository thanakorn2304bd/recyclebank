<?php

namespace App\Support\ActivityLogs;

class ActivityLogViewDataFactory
{
    public function roleLabels(): array
    {
        return [
            'admin' => 'ผู้ดูแลระบบ',
            'staff' => 'เจ้าหน้าที่',
            'member' => 'สมาชิก',
        ];
    }

    public function moduleLabels(): array
    {
        return [
            'auth' => 'การเข้าสู่ระบบ',
            'registration' => 'สมัครสมาชิก',
            'households' => 'ครัวเรือน',
            'material_categories' => 'หมวดวัสดุ',
            'materials' => 'วัสดุ',
            'material_prices' => 'ราคาวัสดุ',
            'transactions' => 'ธุรกรรม',
            'reports' => 'รายงาน',
        ];
    }
}

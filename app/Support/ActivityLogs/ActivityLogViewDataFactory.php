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
            'admin.users' => 'บัญชีผู้ใช้',
            'privacy.notice' => 'ประกาศ PDPA',
            'privacy.consents' => 'การรับทราบ PDPA',
            'data_subject_requests' => 'คำขอเจ้าของข้อมูล',
            'security_incidents' => 'เหตุการณ์ข้อมูลส่วนบุคคล',
            'withdraw_requests' => 'คำขอถอน',
            'households' => 'ครัวเรือน',
            'households.review' => 'อนุมัติครัวเรือน',
            'material_categories' => 'หมวดวัสดุ',
            'materials' => 'วัสดุ',
            'material_prices' => 'ราคาวัสดุ',
            'transactions' => 'ธุรกรรม',
            'transactions.reverse' => 'กลับรายการธุรกรรม',
            'reports' => 'รายงาน',
        ];
    }

    public function entityLabels(): array
    {
        return [
            'household' => 'ครัวเรือน',
            'transaction' => 'ธุรกรรม',
            'user_account' => 'บัญชีผู้ใช้',
            'material' => 'วัสดุ',
            'privacy_notice_version' => 'ประกาศ PDPA',
            'privacy_consent' => 'การรับทราบ PDPA',
            'data_subject_request' => 'คำขอเจ้าของข้อมูล',
            'security_incident' => 'เหตุการณ์ข้อมูลส่วนบุคคล',
            'withdraw_request' => 'คำขอถอน',
        ];
    }
}

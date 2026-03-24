<?php

namespace App\Support\Households;

use App\Models\Household;
use App\Models\UserAccount;

class HouseholdViewDataFactory
{
    public function oldMembers(mixed $oldMembers): array
    {
        return is_array($oldMembers) ? array_values($oldMembers) : [];
    }

    public function credentialsPage(Household $household, ?UserAccount $memberAccount): array
    {
        $hasExistingAccount = (bool) $memberAccount;

        if ($memberAccount?->is_active ?? ($household->active_status === 'active')) {
            $accountStatusBadgeClass = 'bg-success';
            $accountStatusLabel = 'เข้าใช้งานได้';
        } elseif ($household->active_status === 'pending') {
            $accountStatusBadgeClass = 'bg-warning text-dark';
            $accountStatusLabel = 'รออนุมัติ';
        } else {
            $accountStatusBadgeClass = 'bg-secondary';
            $accountStatusLabel = 'ปิดการเข้าใช้งาน';
        }

        if ($household->active_status === 'active') {
            $accountHelpAlertClass = 'alert-info';
            $accountHelpMessage = 'หลังตั้งรหัสผ่านแล้ว ครัวเรือนนี้จะเข้าสู่ระบบและดูข้อมูลของตัวเองได้เฉพาะรายการที่ผูกกับบัญชีนี้';
        } elseif ($household->active_status === 'pending') {
            $accountHelpAlertClass = 'alert-warning';
            $accountHelpMessage = 'หลังตั้งรหัสผ่านแล้ว บัญชียังอยู่ในสถานะรออนุมัติ และจะเข้าสู่ระบบได้เมื่อเจ้าหน้าที่เปลี่ยนสถานะครัวเรือนเป็นใช้งาน';
        } else {
            $accountHelpAlertClass = 'alert-secondary';
            $accountHelpMessage = 'บัญชีนี้ถูกปิดการเข้าใช้งานอยู่ แม้ตั้งรหัสผ่านแล้วก็ยังเข้าสู่ระบบไม่ได้จนกว่าจะเปิดใช้งานครัวเรือนอีกครั้ง';
        }

        return [
            'hasExistingAccount' => $hasExistingAccount,
            'pageTitle' => $hasExistingAccount ? 'รีเซ็ตรหัสผ่านครัวเรือน' : 'สร้างบัญชีเข้าใช้งานครัวเรือน',
            'submitLabel' => $hasExistingAccount ? 'บันทึกรหัสผ่านใหม่' : 'บันทึกและสร้างบัญชีเข้าใช้',
            'accountStatusBadgeClass' => $accountStatusBadgeClass,
            'accountStatusLabel' => $accountStatusLabel,
            'accountHelpAlertClass' => $accountHelpAlertClass,
            'accountHelpMessage' => $accountHelpMessage,
        ];
    }
}

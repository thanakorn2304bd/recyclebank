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

    public function membersForEdit(Household $household): array
    {
        return $household->members
            ->map(fn ($member) => [
                'full_name' => $member->full_name,
                'id_card' => $member->id_card,
                'relation' => $member->relation,
                'is_head' => $member->is_head,
            ])
            ->values()
            ->all();
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
            $accountHelpMessage = 'หลังตั้งหรือรีเซ็ตรหัสผ่านแล้ว ครัวเรือนนี้จะเข้าสู่ระบบได้ และระบบจะบังคับให้เปลี่ยนรหัสผ่านอีกครั้งเมื่อเข้าใช้ครั้งแรกด้วยรหัสที่เจ้าหน้าที่ตั้งให้';
        } elseif ($household->active_status === 'pending') {
            $accountHelpAlertClass = 'alert-warning';
            $accountHelpMessage = 'หลังตั้งหรือรีเซ็ตรหัสผ่านแล้ว บัญชียังอยู่ในสถานะรออนุมัติ และจะเข้าสู่ระบบได้เมื่อเจ้าหน้าที่เปลี่ยนสถานะครัวเรือนเป็นใช้งาน จากนั้นระบบจะให้เปลี่ยนรหัสผ่านทันที';
        } else {
            $accountHelpAlertClass = 'alert-secondary';
            $accountHelpMessage = 'บัญชีนี้ถูกปิดการเข้าใช้งานอยู่ แม้ตั้งหรือรีเซ็ตรหัสผ่านแล้วก็ยังเข้าสู่ระบบไม่ได้จนกว่าจะเปิดใช้งานครัวเรือนอีกครั้ง';
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

<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class Admin extends Component
{
    public mixed $authUser;

    public bool $isPrivileged;

    public bool $isAdmin;

    public array $navItems;

    public function __construct(public string $title = 'RecycleBank')
    {
        $this->authUser = auth()->user();
        $this->isPrivileged = (bool) $this->authUser && in_array($this->authUser->role, ['admin', 'staff'], true);
        $this->isAdmin = $this->authUser?->role === 'admin';
        $this->navItems = [
            ['label' => 'ฝาก/รับซื้อ', 'route' => 'deposits.create', 'patterns' => ['deposits.*'], 'privileged' => true, 'accent' => 'deposit'],
            ['label' => 'ถอน', 'route' => 'withdraws.create', 'patterns' => ['withdraws.*'], 'privileged' => true, 'accent' => 'withdraw'],
            ['label' => 'สรุปรายงาน', 'route' => 'reports.index', 'patterns' => ['reports.*']],
            ['label' => 'ประวัติรายการ', 'route' => 'transactions.index', 'patterns' => ['transactions.*']],
            ['label' => 'ครัวเรือน', 'route' => 'households.index', 'patterns' => ['households.*']],
            ['label' => 'เจ้าหน้าที่', 'route' => 'admin.staff.index', 'patterns' => ['admin.staff.*'], 'admin_only' => true],
            ['label' => 'บัญชีผู้ใช้', 'route' => 'admin.users.index', 'patterns' => ['admin.users.*'], 'admin_only' => true],
            ['label' => 'Activity Log', 'route' => 'admin.activity-logs.index', 'patterns' => ['admin.activity-logs.*'], 'admin_only' => true],
            ['label' => 'วัสดุ', 'route' => 'materials.index', 'patterns' => ['materials.*', 'material-categories.*', 'material-prices.*'], 'privileged' => true],
        ];
    }

    public function render(): View
    {
        return view('components.layouts.admin', [
            'title' => $this->title,
            'authUser' => $this->authUser,
            'isPrivileged' => $this->isPrivileged,
            'isAdmin' => $this->isAdmin,
            'navItems' => $this->navItems,
        ]);
    }
}

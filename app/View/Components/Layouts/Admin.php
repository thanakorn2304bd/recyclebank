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
        $pdpaEnabled = (bool) config('features.pdpa', false);
        $this->navItems = array_values(array_filter([
            ['label' => 'ฝาก/รับซื้อ', 'route' => 'deposits.create', 'patterns' => ['deposits.*'], 'privileged' => true, 'staff_only' => true, 'accent' => 'deposit'],
            ['label' => 'ถอน', 'route' => 'withdraws.create', 'patterns' => ['withdraws.*'], 'privileged' => true, 'staff_only' => true, 'accent' => 'withdraw'],
            ['label' => 'คำขอถอน', 'route' => 'withdraw-requests.index', 'patterns' => ['withdraw-requests.*'], 'staff_only' => true],
            ['label' => 'สรุปรายงาน', 'route' => 'reports.index', 'patterns' => ['reports.*']],
            ['label' => 'ประวัติรายการ', 'route' => 'transactions.index', 'patterns' => ['transactions.*']],
            ['label' => 'ครัวเรือน', 'route' => 'households.index', 'patterns' => ['households.*']],
            $pdpaEnabled ? ['label' => 'PDPA', 'route' => 'compliance.dsars.index', 'patterns' => ['compliance.dsars.*', 'compliance.incidents.*'], 'privileged' => true] : null,
            ['label' => 'ชุมชน', 'route' => 'admin.communities.index', 'patterns' => ['admin.communities.*'], 'admin_only' => true],
            ['label' => 'เจ้าหน้าที่', 'route' => 'admin.staff.index', 'patterns' => ['admin.staff.*'], 'admin_only' => true],
            ['label' => 'บัญชีผู้ใช้', 'route' => 'admin.users.index', 'patterns' => ['admin.users.*'], 'admin_only' => true],
            ['label' => 'Activity Log', 'route' => 'admin.activity-logs.index', 'patterns' => ['admin.activity-logs.*'], 'admin_only' => true],
            ['label' => 'Backup', 'route' => 'admin.backup.index', 'patterns' => ['admin.backup.*'], 'admin_only' => true],
            ['label' => 'วัสดุ', 'route' => 'materials.index', 'patterns' => ['materials.*', 'material-categories.*', 'material-prices.*'], 'privileged' => true],
        ], fn ($item) => $item !== null));
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

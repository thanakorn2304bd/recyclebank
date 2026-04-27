<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserFiltersRequest;
use App\Http\Requests\StoreStaffAccountRequest;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use App\Support\AdminUsers\AdminUserService;
use App\Support\AdminUsers\AdminUserViewDataFactory;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index(
        AdminUserFiltersRequest $request,
        AdminUserService $adminUserService,
        AdminUserViewDataFactory $adminUserViewDataFactory
    ) {
        ['q' => $q, 'role' => $role, 'status' => $status] = $request->filters();
        ['users' => $users, 'summary' => $summary] = $adminUserService->indexData([
            'q' => $q,
            'role' => $role,
            'status' => $status,
        ]);
        $roleLabels = $adminUserViewDataFactory->roleLabels();
        $staffOptions = $adminUserService->staffOptions();

        return view('admin.users.index', compact('users', 'summary', 'q', 'role', 'status', 'roleLabels', 'staffOptions'));
    }

    public function storeStaff(
        StoreStaffAccountRequest $request,
        AdminUserService $adminUserService
    ): RedirectResponse {
        $payload = $request->payload();
        $createdUser = $adminUserService->createStaffAccount($payload);
        $staffName = $createdUser->staff?->full_name ?? '';

        ActivityLogger::forCurrentUser(
            'admin.users',
            "เพิ่มบัญชีเจ้าหน้าที่ {$createdUser->username} ({$staffName})"
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "เพิ่มบัญชีเจ้าหน้าที่สำหรับ {$staffName} เรียบร้อย");
    }

    public function toggleActive(UserAccount $user): RedirectResponse
    {
        // ป้องกันไม่ให้ระงับบัญชี admin ตัวเอง
        if ($user->user_id === auth()->id()) {
            return back()->withErrors('ไม่สามารถระงับบัญชีของตัวเองได้');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $action = $user->is_active ? 'เปิดใช้งาน' : 'ระงับ';

        ActivityLogger::forCurrentUser('admin.users', "{$action}บัญชี {$user->username}", [
            'entity_type' => 'user_account',
            'entity_id' => (string) $user->user_id,
            'after' => ['is_active' => $user->is_active],
        ]);

        return back()->with('success', "{$action}บัญชี {$user->username} เรียบร้อย");
    }
}

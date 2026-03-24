<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserFiltersRequest;
use App\Http\Requests\StoreStaffAccountRequest;
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

        return view('admin.users.index', compact('users', 'summary', 'q', 'role', 'status', 'roleLabels'));
    }

    public function storeStaff(
        StoreStaffAccountRequest $request,
        AdminUserService $adminUserService
    ): RedirectResponse {
        $payload = $request->payload();
        $createdUser = $adminUserService->createStaffAccount($payload);

        ActivityLogger::forCurrentUser(
            'admin.users',
            "เพิ่มบัญชีเจ้าหน้าที่ {$createdUser->username} ({$payload['full_name']})"
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "เพิ่มบัญชี staff สำหรับ {$payload['full_name']} เรียบร้อย");
    }
}

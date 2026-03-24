<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserFiltersRequest;
use App\Http\Requests\StoreStaffAccountRequest;
use App\Support\ActivityLogger;
use App\Support\AdminUsers\AdminUserService;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index(AdminUserFiltersRequest $request, AdminUserService $adminUserService)
    {
        ['q' => $q, 'role' => $role, 'status' => $status] = $request->filters();
        ['users' => $users, 'summary' => $summary] = $adminUserService->indexData([
            'q' => $q,
            'role' => $role,
            'status' => $status,
        ]);

        return view('admin.users.index', compact('users', 'summary', 'q', 'role', 'status'));
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

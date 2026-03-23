<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());
        $role = $request->string('role')->toString();
        $status = $request->string('status')->toString();

        $usersQuery = UserAccount::query()
            ->with(['household.community', 'staff'])
            ->withCount('logs')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('username', 'like', "%{$q}%")
                        ->orWhere('role', 'like', "%{$q}%")
                        ->orWhereHas('household', function ($householdQuery) use ($q) {
                            $householdQuery->where('account_no', 'like', "%{$q}%")
                                ->orWhere('contact_person', 'like', "%{$q}%")
                                ->orWhere('house_no', 'like', "%{$q}%");
                        })
                        ->orWhereHas('staff', function ($staffQuery) use ($q) {
                            $staffQuery->where('full_name', 'like', "%{$q}%")
                                ->orWhere('position', 'like', "%{$q}%");
                        });
                });
            })
            ->when(in_array($role, ['admin', 'staff', 'member'], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            });

        $summary = [
            'total' => (clone $usersQuery)->count(),
            'active' => (clone $usersQuery)->where('is_active', true)->count(),
            'inactive' => (clone $usersQuery)->where('is_active', false)->count(),
            'members' => (clone $usersQuery)->where('role', 'member')->count(),
        ];

        $users = $usersQuery
            ->orderByDesc('created_at')
            ->orderBy('user_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'summary', 'q', 'role', 'status'));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('createStaffAccount', [
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:50'],
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('user_account', 'username'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'full_name.required' => 'กรุณากรอกชื่อเจ้าหน้าที่',
            'username.required' => 'กรุณากรอกชื่อผู้ใช้',
            'username.unique' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว',
            'username.regex' => 'ชื่อผู้ใช้ใช้ได้เฉพาะตัวอักษรอังกฤษ ตัวเลข จุด ขีดล่าง และขีดกลาง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
            'account_status.required' => 'กรุณาเลือกสถานะบัญชี',
        ]);

        $fullName = trim((string) $data['full_name']);
        $phone = trim((string) ($data['phone'] ?? ''));
        $position = trim((string) ($data['position'] ?? ''));
        $username = trim((string) $data['username']);
        $isActive = $data['account_status'] === 'active';

        DB::transaction(function () use ($fullName, $phone, $position, $username, $data, $isActive) {
            $staff = Staff::create([
                'full_name' => $fullName,
                'phone' => $phone !== '' ? $phone : null,
                'position' => $position !== '' ? $position : 'เจ้าหน้าที่',
            ]);

            UserAccount::create([
                'username' => $username,
                'password' => $data['password'],
                'role' => 'staff',
                'household_id' => null,
                'staff_id' => $staff->staff_id,
                'created_at' => now(),
                'last_login' => null,
                'is_active' => $isActive,
            ]);

            ActivityLogger::forCurrentUser(
                'admin.users',
                "เพิ่มบัญชีเจ้าหน้าที่ {$username} ({$fullName})"
            );
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', "เพิ่มบัญชี staff สำหรับ {$fullName} เรียบร้อย");
    }
}

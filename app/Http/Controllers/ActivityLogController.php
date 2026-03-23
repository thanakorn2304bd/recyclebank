<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', Rule::in(['admin', 'staff', 'member'])],
            'user_id' => ['nullable', 'integer', 'exists:user_account,user_id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'to.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',
            'user_id.exists' => 'ไม่พบบัญชีผู้ใช้ที่เลือก',
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $module = (string) ($validated['module'] ?? '');
        $role = (string) ($validated['role'] ?? '');
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        $logsQuery = LogActivity::query()
            ->with(['user.household.community', 'user.staff'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('action', 'like', "%{$q}%")
                        ->orWhere('module', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery->where('username', 'like', "%{$q}%")
                                ->orWhereHas('household', function ($householdQuery) use ($q) {
                                    $householdQuery->where('account_no', 'like', "%{$q}%")
                                        ->orWhere('contact_person', 'like', "%{$q}%");
                                })
                                ->orWhereHas('staff', function ($staffQuery) use ($q) {
                                    $staffQuery->where('full_name', 'like', "%{$q}%");
                                });
                        });
                });
            })
            ->when($module !== '', function ($query) use ($module) {
                $query->where('module', $module);
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->whereHas('user', function ($userQuery) use ($role) {
                    $userQuery->where('role', $role);
                });
            })
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('timestamp', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('timestamp', '<=', $to);
            });

        $summary = [
            'total' => (clone $logsQuery)->count(),
            'today' => (clone $logsQuery)->whereDate('timestamp', now()->toDateString())->count(),
            'users' => (clone $logsQuery)->distinct('user_id')->count('user_id'),
            'modules' => (clone $logsQuery)->distinct('module')->count('module'),
        ];

        $logs = $logsQuery
            ->orderByDesc('timestamp')
            ->orderByDesc('log_id')
            ->paginate(25)
            ->withQueryString();

        $modules = LogActivity::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $selectedUser = $userId
            ? UserAccount::query()->with(['household.community', 'staff'])->find($userId)
            : null;

        return view('admin.activity_logs.index', compact(
            'logs',
            'modules',
            'summary',
            'selectedUser',
            'q',
            'module',
            'role',
            'userId',
            'from',
            'to'
        ));
    }
}

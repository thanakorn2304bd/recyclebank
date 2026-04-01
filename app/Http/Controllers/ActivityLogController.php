<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityLogFiltersRequest;
use App\Models\LogActivity;
use App\Models\UserAccount;
use App\Support\ActivityLogs\ActivityLogViewDataFactory;

class ActivityLogController extends Controller
{
    public function index(
        ActivityLogFiltersRequest $request,
        ActivityLogViewDataFactory $activityLogViewDataFactory
    ) {
        [
            'q' => $q,
            'module' => $module,
            'role' => $role,
            'user_id' => $userId,
            'from' => $from,
            'to' => $to,
        ] = $request->filters();

        $logsQuery = LogActivity::query()
            ->with(['user.household.community', 'user.staff'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('action', 'like', "%{$q}%")
                        ->orWhere('module', 'like', "%{$q}%")
                        ->orWhere('entity_type', 'like', "%{$q}%")
                        ->orWhere('entity_id', 'like', "%{$q}%")
                        ->orWhere('ip_address', 'like', "%{$q}%")
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
        $roleLabels = $activityLogViewDataFactory->roleLabels();
        $moduleLabels = $activityLogViewDataFactory->moduleLabels();
        $entityLabels = $activityLogViewDataFactory->entityLabels();

        return view('admin.activity_logs.index', compact(
            'logs',
            'modules',
            'summary',
            'selectedUser',
            'roleLabels',
            'moduleLabels',
            'entityLabels',
            'q',
            'module',
            'role',
            'userId',
            'from',
            'to'
        ));
    }
}

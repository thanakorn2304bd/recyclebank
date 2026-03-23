<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use App\Models\Staff;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());
        $position = trim($request->string('position')->toString());
        $status = $request->string('status')->toString();

        $staffQuery = Staff::query()
            ->with([
                'userAccounts' => fn ($query) => $query
                    ->orderByDesc('is_active')
                    ->orderBy('username'),
            ])
            ->withCount('userAccounts')
            ->withCount([
                'userAccounts as active_accounts_count' => fn ($query) => $query->where('is_active', true),
                'userAccounts as inactive_accounts_count' => fn ($query) => $query->where('is_active', false),
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('full_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('position', 'like', "%{$q}%")
                        ->orWhereHas('userAccounts', function ($userQuery) use ($q) {
                            $userQuery->where('username', 'like', "%{$q}%");
                        });
                });
            })
            ->when($position !== '', function ($query) use ($position) {
                $query->where('position', $position);
            })
            ->when($status === 'active', function ($query) {
                $query->whereHas('userAccounts', fn ($userQuery) => $userQuery->where('is_active', true));
            })
            ->when($status === 'inactive', function ($query) {
                $query->whereHas('userAccounts', fn ($userQuery) => $userQuery->where('is_active', false));
            })
            ->when($status === 'no_account', function ($query) {
                $query->doesntHave('userAccounts');
            });

        $summary = [
            'total' => (clone $staffQuery)->count(),
            'with_accounts' => (clone $staffQuery)->has('userAccounts')->count(),
            'without_accounts' => (clone $staffQuery)->doesntHave('userAccounts')->count(),
            'active_accounts' => (clone $staffQuery)
                ->whereHas('userAccounts', fn ($userQuery) => $userQuery->where('is_active', true))
                ->count(),
        ];

        $positions = Staff::query()
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->orderBy('position')
            ->pluck('position')
            ->unique()
            ->values();

        $staffMembers = $staffQuery
            ->orderBy('full_name')
            ->orderBy('staff_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', compact(
            'staffMembers',
            'summary',
            'q',
            'position',
            'positions',
            'status',
        ));
    }

    public function show(Staff $staff)
    {
        $staff->load([
            'userAccounts' => fn ($query) => $query
                ->withCount('logs')
                ->orderByDesc('is_active')
                ->orderBy('username'),
        ]);

        $userIds = $staff->userAccounts
            ->pluck('user_id')
            ->filter()
            ->values();

        $recentLogs = $userIds->isEmpty()
            ? collect()
            : LogActivity::query()
                ->with('user')
                ->whereIn('user_id', $userIds)
                ->orderByDesc('timestamp')
                ->limit(15)
                ->get();

        $summary = [
            'accounts' => $staff->userAccounts->count(),
            'active_accounts' => $staff->userAccounts->where('is_active', true)->count(),
            'logs' => $staff->userAccounts->sum('logs_count'),
            'last_login' => $staff->userAccounts
                ->pluck('last_login')
                ->filter()
                ->sortDesc()
                ->first(),
        ];

        return view('admin.staff.show', compact('staff', 'recentLogs', 'summary'));
    }
}

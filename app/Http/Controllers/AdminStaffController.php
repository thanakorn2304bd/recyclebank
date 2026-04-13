<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveStaffRequest;
use App\Models\LogActivity;
use App\Models\Staff;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function create(): View
    {
        return view('admin.staff.create');
    }

    public function store(SaveStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $staff = Staff::create($data);

        ActivityLogger::forCurrentUser('staff', "เพิ่มเจ้าหน้าที่ {$staff->full_name}", [
            'entity_type' => 'staff',
            'entity_id' => (string) $staff->staff_id,
            'after' => $data,
        ]);

        return redirect()->route('admin.staff.show', $staff)
            ->with('success', "เพิ่มเจ้าหน้าที่ {$staff->full_name} เรียบร้อย");
    }

    public function edit(Staff $staff): View
    {
        return view('admin.staff.edit', compact('staff'));
    }

    public function update(SaveStaffRequest $request, Staff $staff): RedirectResponse
    {
        $before = $staff->only(['full_name', 'phone', 'position']);
        $data = $request->validated();

        $staff->update($data);

        ActivityLogger::forCurrentUser('staff', "แก้ไขข้อมูลเจ้าหน้าที่ {$staff->full_name}", [
            'entity_type' => 'staff',
            'entity_id' => (string) $staff->staff_id,
            'before' => $before,
            'after' => $data,
        ]);

        return redirect()->route('admin.staff.show', $staff)
            ->with('success', "แก้ไขข้อมูล {$staff->full_name} เรียบร้อย");
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        if ($staff->userAccounts()->exists()) {
            return back()->withErrors('ลบไม่ได้: เจ้าหน้าที่นี้มีบัญชีผู้ใช้ผูกอยู่');
        }

        $name = $staff->full_name;
        $id = $staff->staff_id;

        $staff->delete();

        ActivityLogger::forCurrentUser('staff', "ลบเจ้าหน้าที่ {$name}", [
            'entity_type' => 'staff',
            'entity_id' => (string) $id,
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', "ลบเจ้าหน้าที่ {$name} เรียบร้อย");
    }
}

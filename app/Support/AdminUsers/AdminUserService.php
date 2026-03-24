<?php

namespace App\Support\AdminUsers;

use App\Models\Staff;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;

class AdminUserService
{
    public function indexData(array $filters): array
    {
        $usersQuery = UserAccount::query()
            ->with(['household.community', 'staff'])
            ->withCount('logs')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $q = $filters['q'];

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
            ->when($filters['role'] !== '', fn ($query) => $query->where('role', $filters['role']))
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
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

        return [
            'users' => $users,
            'summary' => $summary,
        ];
    }

    public function createStaffAccount(array $payload): UserAccount
    {
        return DB::transaction(function () use ($payload) {
            $staff = Staff::create([
                'full_name' => $payload['full_name'],
                'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
                'position' => $payload['position'] !== '' && $payload['position'] !== null
                    ? $payload['position']
                    : 'เจ้าหน้าที่',
            ]);

            $userAccount = UserAccount::create([
                'username' => $payload['username'],
                'password' => $payload['password'],
                'role' => 'staff',
                'household_id' => null,
                'staff_id' => $staff->staff_id,
                'created_at' => now(),
                'last_login' => null,
                'is_active' => $payload['is_active'],
            ]);

            return $userAccount->load('staff');
        });
    }
}

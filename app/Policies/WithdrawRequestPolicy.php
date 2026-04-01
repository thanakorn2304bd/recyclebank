<?php

namespace App\Policies;

use App\Models\UserAccount;
use App\Models\WithdrawRequest;

class WithdrawRequestPolicy
{
    public function before(UserAccount $user, string $ability): ?bool
    {
        if (in_array($user->role, ['admin', 'staff'], true)) {
            return true;
        }

        return null;
    }

    public function view(UserAccount $user, WithdrawRequest $withdrawRequest): bool
    {
        return $user->role === 'member'
            && (int) $user->household_id === (int) $withdrawRequest->household_id;
    }
}

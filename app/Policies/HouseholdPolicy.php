<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\UserAccount;

class HouseholdPolicy
{
    public function before(UserAccount $user, string $ability): ?bool
    {
        if (in_array($user->role, ['admin', 'staff'], true)) {
            return true;
        }

        return null;
    }

    public function view(UserAccount $user, Household $household): bool
    {
        return $user->role === 'member'
            && (int) $user->household_id === (int) $household->household_id;
    }
}

<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\UserAccount;

class TransactionPolicy
{
    public function before(UserAccount $user, string $ability): ?bool
    {
        if (in_array($user->role, ['admin', 'staff'], true)) {
            return true;
        }

        return null;
    }

    public function view(UserAccount $user, Transaction $transaction): bool
    {
        return $user->role === 'member'
            && (int) $user->household_id === (int) $transaction->household_id;
    }
}

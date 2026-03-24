<?php

namespace App\Support\Auth;

use Illuminate\Http\Request;

class CurrentUserHouseholdResolver
{
    public function isMember(Request $request): bool
    {
        return $request->user()?->role === 'member';
    }

    public function householdId(Request $request): ?int
    {
        $householdId = $request->user()?->household_id;

        return $householdId ? (int) $householdId : null;
    }
}

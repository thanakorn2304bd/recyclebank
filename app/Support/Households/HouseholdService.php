<?php

namespace App\Support\Households;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;

class HouseholdService
{
    public function create(array $attributes, array $members): Household
    {
        return DB::transaction(function () use ($attributes, $members) {
            $household = Household::create($attributes);
            $this->createMembers($household, $members);

            return $household;
        });
    }

    public function loadDetails(Household $household): Household
    {
        $household->load([
            'community',
            'createdByUser',
            'members' => fn ($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
        ]);

        return $household;
    }

    public function update(Household $household, array $attributes): ?UserAccount
    {
        $household->update($attributes);

        return $this->syncExistingMemberAccount($household);
    }

    public function syncExistingMemberAccount(Household $household): ?UserAccount
    {
        $memberAccount = $this->memberAccountFor($household);

        if (! $memberAccount) {
            return null;
        }

        $this->fillMemberAccountFromHousehold($memberAccount, $household);

        if ($memberAccount->isDirty()) {
            $memberAccount->save();
        }

        return $memberAccount;
    }

    public function storeCredentials(Household $household, string $password): UserAccount
    {
        $memberAccount = $this->memberAccountFor($household) ?? new UserAccount;

        $this->fillMemberAccountFromHousehold($memberAccount, $household);
        $memberAccount->password = $password;
        $memberAccount->created_at ??= now();
        $memberAccount->last_login ??= null;
        $memberAccount->save();

        return $memberAccount;
    }

    public function memberAccountFor(Household $household): ?UserAccount
    {
        return UserAccount::query()
            ->where('household_id', $household->household_id)
            ->where('role', 'member')
            ->orderBy('user_id')
            ->first();
    }

    public function deletionBlockMessage(Household $household): ?string
    {
        if ($household->transactions()->exists()) {
            return 'ลบไม่ได้: มีประวัติการทำรายการที่อ้างถึงครัวเรือนนี้';
        }

        if ($household->members()->exists()) {
            return 'ลบไม่ได้: มีสมาชิกครัวเรือนที่อ้างถึงครัวเรือนนี้';
        }

        if ($household->userAccounts()->exists()) {
            return 'ลบไม่ได้: มีบัญชีผู้ใช้ที่ผูกกับครัวเรือนนี้';
        }

        return null;
    }

    private function createMembers(Household $household, array $members): void
    {
        if ($members === []) {
            return;
        }

        $household->members()->createMany($members);
    }

    private function fillMemberAccountFromHousehold(UserAccount $memberAccount, Household $household): void
    {
        $memberAccount->username = $household->account_no;
        $memberAccount->role = 'member';
        $memberAccount->household_id = $household->household_id;
        $memberAccount->staff_id = null;
        $memberAccount->is_active = $household->active_status === 'active';
    }
}

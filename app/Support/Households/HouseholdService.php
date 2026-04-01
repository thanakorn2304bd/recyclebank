<?php

namespace App\Support\Households;

use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'reviewedByUser.staff',
            'members' => fn ($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
        ]);

        return $household;
    }

    public function indexViewMetrics(LengthAwarePaginator $households): array
    {
        $pageHouseholds = $households->getCollection();

        return [
            'activeCount' => $pageHouseholds->where('active_status', 'active')->count(),
            'pendingCount' => $pageHouseholds->where('active_status', 'pending')->count(),
            'inactiveCount' => $pageHouseholds->where('active_status', 'inactive')->count(),
        ];
    }

    public function update(Household $household, array $attributes, ?array $members = null): ?UserAccount
    {
        return DB::transaction(function () use ($household, $attributes, $members) {
            $household->update($attributes);

            if ($members !== null) {
                $this->syncMembers($household, $members);
            }

            return $this->syncExistingMemberAccount($household);
        });
    }

    public function review(Household $household, string $status, string $notes, int $reviewedBy): array
    {
        if (! in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'status' => 'สถานะสำหรับการพิจารณาไม่ถูกต้อง',
            ]);
        }

        $before = $this->reviewSnapshot($household);

        $household->update([
            'active_status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $memberAccount = $this->syncExistingMemberAccount($household);

        return [
            'household' => $household->fresh(['community', 'createdByUser', 'reviewedByUser']),
            'memberAccount' => $memberAccount?->fresh(),
            'before' => $before,
            'after' => $this->reviewSnapshot($household->fresh()),
        ];
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
        $memberAccount->applyPassword($password, true);
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

    private function syncMembers(Household $household, array $members): void
    {
        $household->members()->delete();
        $this->createMembers($household, $members);
    }

    private function fillMemberAccountFromHousehold(UserAccount $memberAccount, Household $household): void
    {
        $memberAccount->username = $household->account_no;
        $memberAccount->role = 'member';
        $memberAccount->household_id = $household->household_id;
        $memberAccount->staff_id = null;
        $memberAccount->is_active = $household->active_status === 'active';
    }

    private function reviewSnapshot(Household $household): array
    {
        return [
            'household_id' => (int) $household->household_id,
            'account_no' => (string) $household->account_no,
            'active_status' => (string) $household->active_status,
            'reviewed_by' => $household->reviewed_by !== null ? (int) $household->reviewed_by : null,
            'reviewed_at' => $household->reviewed_at?->format('Y-m-d H:i:s'),
            'review_notes' => $household->review_notes,
        ];
    }
}

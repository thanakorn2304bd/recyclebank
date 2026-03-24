<?php

namespace App\Support\Transactions;

use App\Models\Household;
use Illuminate\Validation\ValidationException;

class HouseholdTransactionService
{
    public function findForLookup(string $communityId, string $houseNo): ?Household
    {
        return Household::with('community')
            ->where('community_id', $communityId)
            ->where('house_no', $houseNo)
            ->first();
    }

    public function findForTransaction(string $communityId, string $houseNo, array $columns = ['*']): Household
    {
        $household = Household::query()
            ->where('community_id', $communityId)
            ->where('house_no', $houseNo)
            ->first($columns);

        if ($household) {
            return $household;
        }

        throw ValidationException::withMessages([
            'house_no' => "ไม่พบครัวเรือนสำหรับเลขที่ชุมชน {$communityId} และบ้านเลขที่ {$houseNo}",
        ]);
    }

    public function ensureActive(Household $household): void
    {
        if ($household->active_status === 'active') {
            return;
        }

        throw ValidationException::withMessages([
            'house_no' => $this->unavailableMessage($household),
        ]);
    }

    public function lookupPayload(Household $household): array
    {
        return [
            'found' => true,
            'household' => [
                'household_id' => $household->household_id,
                'account_no' => $household->account_no,
                'contact_person' => $household->contact_person,
                'total_balance' => (float) $household->total_balance,
                'community_id' => $household->community_id,
                'community_name' => $household->community?->community_name,
                'house_no' => $household->house_no,
                'active_status' => $household->active_status,
                'can_transact' => $household->active_status === 'active',
            ],
            'message' => $household->active_status === 'active'
                ? null
                : $this->unavailableMessage($household),
        ];
    }

    public function unavailableMessage(Household $household): string
    {
        return 'ครัวเรือนนี้ยังไม่พร้อมทำรายการ (สถานะ: '
            .$this->statusLabel($household->active_status)
            .')';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ใช้งาน',
            'pending' => 'รออนุมัติ',
            default => 'ปิดใช้งาน',
        };
    }
}

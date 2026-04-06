<?php

namespace App\Support;

use App\Models\DataSubjectRequest;
use App\Models\Household;
use App\Models\HouseholdMemberAdditionRequest;
use App\Models\Material;
use App\Models\SecurityIncident;
use App\Models\UserAccount;
use App\Models\WithdrawRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MainMenuViewDataFactory
{
    public function make(?UserAccount $authUser): array
    {
        $now = now();
        $catalogSections = $this->buildCatalogSections($now->toDateString());
        $categoryFilters = array_map(
            fn (array $section) => [
                'name' => $section['name'],
                'count' => $section['count'],
            ],
            $catalogSections
        );
        $privilegedMenuCount = $this->privilegedMenuCount($authUser);
        $attentionItems = $this->attentionItems($authUser);

        return [
            'authUser' => $authUser,
            'isPrivileged' => $this->isPrivileged($authUser),
            'roleLabel' => $this->roleLabel($authUser),
            'privilegedMenuCount' => $privilegedMenuCount,
            'managementMenuCount' => max($privilegedMenuCount - 2, 0),
            'catalogSections' => $catalogSections,
            'categoryFilters' => $categoryFilters,
            'totalCategories' => count($catalogSections),
            'totalMaterials' => array_sum(array_column($categoryFilters, 'count')),
            'attentionItems' => $attentionItems,
            'attentionCount' => array_sum(array_column($attentionItems, 'count')),
            'todayLabel' => $now->format('d/m/Y'),
            'updatedAtLabel' => $now->format('d/m/Y H:i'),
        ];
    }

    private function buildCatalogSections(string $date): array
    {
        return Material::query()
            ->select('material.*')
            ->selectRaw('current_price.price as current_price')
            ->with('category')
            ->joinCurrentPriceAt($date)
            ->get()
            ->map(function (Material $material) {
                return [
                    'category_name' => $material->category?->category_name ?? 'ไม่ระบุหมวด',
                    'name' => $material->material_name,
                    'unit' => $material->unit,
                    'priceDisplay' => $material->current_price !== null
                        ? number_format((float) $material->current_price, 2)
                        : null,
                ];
            })
            ->sortBy(fn (array $item) => $item['category_name'].'|'.$item['name'])
            ->groupBy('category_name')
            ->map(function (Collection $items, string $categoryName) {
                return [
                    'name' => $categoryName,
                    'count' => $items->count(),
                    'items' => $items
                        ->map(fn (array $item) => Arr::except($item, 'category_name'))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function isPrivileged(?UserAccount $authUser): bool
    {
        return $authUser !== null && in_array($authUser->role, ['admin', 'staff'], true);
    }

    private function privilegedMenuCount(?UserAccount $authUser): int
    {
        return match ($authUser?->role) {
            'admin' => 10,
            'staff' => 8,
            default => 0,
        };
    }

    private function roleLabel(?UserAccount $authUser): string
    {
        return match ($authUser?->role) {
            'admin' => 'ผู้ดูแลระบบ',
            'staff' => 'เจ้าหน้าที่',
            default => 'สมาชิก',
        };
    }

    private function attentionItems(?UserAccount $authUser): array
    {
        if (! $this->isPrivileged($authUser)) {
            return [];
        }

        $today = now()->toDateString();
        $pendingHouseholds = Household::query()
            ->where('active_status', 'pending')
            ->count();
        $openDataSubjectRequests = DataSubjectRequest::query()
            ->whereIn('status', ['submitted', 'in_review'])
            ->count();
        $overdueDataSubjectRequests = DataSubjectRequest::query()
            ->whereIn('status', ['submitted', 'in_review'])
            ->whereDate('due_at', '<', $today)
            ->count();
        $notificationPendingIncidents = SecurityIncident::query()
            ->where('notification_required', true)
            ->where('status', '!=', 'closed')
            ->where(function ($query) {
                $query->whereNull('authority_notified_at')
                    ->orWhereNull('subject_notified_at');
            })
            ->count();
        $pendingMemberAdditionRequests = HouseholdMemberAdditionRequest::query()
            ->where('status', 'pending')
            ->count();
        $pendingWithdrawRequests = WithdrawRequest::query()
            ->where('status', 'pending')
            ->count();

        $items = [
            [
                'label' => 'คำขอสมาชิกใหม่',
                'count' => $pendingHouseholds,
                'description' => 'ครัวเรือนที่ยังรอการพิจารณาอนุมัติ',
                'url' => route('households.index', ['status' => 'pending'], false),
                'accent' => 'amber',
            ],
            [
                'label' => 'DSAR เปิดอยู่',
                'count' => $openDataSubjectRequests,
                'description' => $overdueDataSubjectRequests > 0
                    ? "เกินกำหนดแล้ว {$overdueDataSubjectRequests} รายการ"
                    : 'คำขอเจ้าของข้อมูลที่ยังต้องติดตาม',
                'url' => route('compliance.dsars.index', absolute: false),
                'accent' => 'rose',
            ],
            [
                'label' => 'เหตุที่ต้องแจ้งเตือน',
                'count' => $notificationPendingIncidents,
                'description' => 'เหตุข้อมูลส่วนบุคคลที่ยังต้องแจ้งหน่วยงานหรือเจ้าของข้อมูล',
                'url' => route('compliance.incidents.index', absolute: false),
                'accent' => 'sky',
            ],
            [
                'label' => 'คำขอเพิ่มสมาชิก',
                'count' => $pendingMemberAdditionRequests,
                'description' => 'ครัวเรือนที่ยื่นขอเพิ่มสมาชิกและยังรอเจ้าหน้าที่ตรวจสอบ',
                'url' => route('households.index', ['member_addition' => 'pending'], false),
                'accent' => 'emerald',
            ],
        ];

        if ($pendingWithdrawRequests > 0) {
            $items[] = [
                'label' => 'คำขอถอนรออนุมัติ',
                'count' => $pendingWithdrawRequests,
                'description' => 'สมาชิกที่ยื่นคำขอถอนและรอการพิจารณาจากเจ้าหน้าที่',
                'url' => route('withdraw-requests.index', ['status' => 'pending'], false),
                'accent' => 'emerald',
            ];
        }

        return $items;
    }
}

<?php

namespace App\Support;

use App\Models\DataSubjectRequest;
use App\Models\Household;
use App\Models\HouseholdMemberAdditionRequest;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\SecurityIncident;
use App\Models\UserAccount;
use App\Models\WithdrawRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MainMenuViewDataFactory
{
    public function make(?UserAccount $authUser, ?string $requestedPriceMonth = null): array
    {
        $now = CarbonImmutable::now();
        $priceMonthContext = $this->resolvePriceMonthContext($requestedPriceMonth, $now);
        $catalogSections = $this->buildCatalogSections($priceMonthContext['reference_date']);
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
            'priceMonthOptions' => $priceMonthContext['options'],
            'selectedPriceMonth' => $priceMonthContext['selected_month'],
            'selectedPriceMonthLabel' => $priceMonthContext['selected_label'],
            'selectedPriceDateLabel' => $priceMonthContext['reference_label'],
            'selectedPriceDescription' => $priceMonthContext['description'],
            'selectedPriceNotice' => $priceMonthContext['notice'],
            'isCurrentPriceMonth' => $priceMonthContext['is_current'],
            'newerPriceMonth' => $priceMonthContext['newer_month'],
            'olderPriceMonth' => $priceMonthContext['older_month'],
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
            ->filter(fn (Material $material) => $material->current_price !== null)
            ->map(function (Material $material) {
                return [
                    'category_name' => $material->category?->category_name ?? 'ไม่ระบุหมวด',
                    'name' => $material->material_name,
                    'unit' => $material->unit,
                    'priceDisplay' => number_format((float) $material->current_price, 2),
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

    private function resolvePriceMonthContext(?string $requestedPriceMonth, CarbonImmutable $now): array
    {
        $currentMonth = $now->startOfMonth();
        $options = $this->priceMonthOptions($currentMonth);
        $selectedMonth = $this->selectedPriceMonth($requestedPriceMonth, $options, $currentMonth);
        $selectedMonthValue = $selectedMonth->format('Y-m');
        $selectedIndex = collect($options)->search(
            fn (array $option) => $option['value'] === $selectedMonthValue
        );
        $isCurrentMonth = $selectedMonth->equalTo($currentMonth);
        $referenceDate = $isCurrentMonth ? $now : $selectedMonth->endOfMonth();

        return [
            'options' => $options,
            'selected_month' => $selectedMonthValue,
            'selected_label' => $this->thaiMonthLabel($selectedMonth),
            'reference_date' => $referenceDate->toDateString(),
            'reference_label' => $referenceDate->format('d/m/Y'),
            'description' => $isCurrentMonth
                ? 'แสดงราคาปัจจุบันที่มีผลใช้งาน'
                : 'แสดงราคาย้อนหลังตามเดือนที่เลือก',
            'notice' => $isCurrentMonth
                ? 'ราคาเป็นราคาปัจจุบันที่มีผล ณ '.$referenceDate->format('d/m/Y')
                : 'กำลังแสดงราคาย้อนหลังของเดือน '.$this->thaiMonthLabel($selectedMonth)
                    .' โดยอ้างอิงข้อมูลที่มีผล ณ '.$referenceDate->format('d/m/Y'),
            'is_current' => $isCurrentMonth,
            'newer_month' => $selectedIndex !== false && $selectedIndex > 0
                ? $options[$selectedIndex - 1]
                : null,
            'older_month' => $selectedIndex !== false && isset($options[$selectedIndex + 1])
                ? $options[$selectedIndex + 1]
                : null,
        ];
    }

    private function priceMonthOptions(CarbonImmutable $currentMonth): array
    {
        $minEffectiveDate = MaterialPrice::query()->min('effective_date');
        $firstMonth = $minEffectiveDate
            ? CarbonImmutable::parse($minEffectiveDate)->startOfMonth()
            : $currentMonth;

        if ($firstMonth->greaterThan($currentMonth)) {
            $firstMonth = $currentMonth;
        }

        $options = [];

        for ($month = $currentMonth; $month->greaterThanOrEqualTo($firstMonth); $month = $month->subMonth()) {
            $options[] = [
                'value' => $month->format('Y-m'),
                'label' => $this->thaiMonthLabel($month),
            ];
        }

        return $options;
    }

    private function selectedPriceMonth(
        ?string $requestedPriceMonth,
        array $options,
        CarbonImmutable $currentMonth
    ): CarbonImmutable {
        if (! is_string($requestedPriceMonth) || ! preg_match('/^\d{4}-\d{2}$/', $requestedPriceMonth)) {
            return $currentMonth;
        }

        try {
            $month = CarbonImmutable::createFromFormat('Y-m', $requestedPriceMonth)->startOfMonth();
        } catch (\Throwable) {
            return $currentMonth;
        }

        $availableMonths = array_column($options, 'value');

        if (! in_array($month->format('Y-m'), $availableMonths, true)) {
            return $currentMonth;
        }

        return $month;
    }

    private function thaiMonthLabel(CarbonImmutable $month): string
    {
        $thaiMonths = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];

        return $thaiMonths[(int) $month->format('n')].' '.((int) $month->format('Y') + 543);
    }

    private function isPrivileged(?UserAccount $authUser): bool
    {
        return $authUser !== null && in_array($authUser->role, ['admin', 'staff'], true);
    }

    private function privilegedMenuCount(?UserAccount $authUser): int
    {
        $pdpaEnabled = (bool) config('features.pdpa', false);

        return match ($authUser?->role) {
            'admin' => $pdpaEnabled ? 12 : 11,
            'staff' => $pdpaEnabled ? 8 : 7,
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
        $pdpaEnabled = (bool) config('features.pdpa', false);
        $pendingHouseholds = Household::query()
            ->where('active_status', 'pending')
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
                'label' => 'คำขอเพิ่มสมาชิก',
                'count' => $pendingMemberAdditionRequests,
                'description' => 'ครัวเรือนที่ยื่นขอเพิ่มสมาชิกและยังรอเจ้าหน้าที่ตรวจสอบ',
                'url' => route('households.index', ['member_addition' => 'pending'], false),
                'accent' => 'emerald',
            ],
        ];

        if ($pdpaEnabled) {
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

            array_splice($items, 1, 0, [
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
            ]);
        }

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

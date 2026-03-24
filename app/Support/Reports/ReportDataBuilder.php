<?php

namespace App\Support\Reports;

use App\Exceptions\MissingMemberHouseholdException;
use App\Models\Community;
use App\Models\Household;
use App\Models\MaterialCategory;
use App\Models\Transaction;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportDataBuilder
{
    public function build(UserAccount $user, ReportFilters $filters): array
    {
        $isPrivileged = in_array($user->role, ['admin', 'staff'], true);
        $memberHouseholdId = $isPrivileged ? null : $this->memberHouseholdId($user);

        if (! $isPrivileged && ! $memberHouseholdId) {
            throw new MissingMemberHouseholdException('ไม่พบบัญชีครัวเรือนของผู้ใช้นี้');
        }

        $communities = $isPrivileged
            ? Community::query()->orderBy('community_id')->get(['community_id', 'community_name'])
            : collect();
        $materialCategories = MaterialCategory::query()
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        $householdQuery = $this->householdScope($isPrivileged, $memberHouseholdId, $filters);
        $transactionQuery = $this->transactionScope($isPrivileged, $memberHouseholdId, $filters);
        $depositDetailQuery = $this->depositDetailScope($isPrivileged, $memberHouseholdId, $filters);

        $focusHousehold = null;
        if (! $isPrivileged) {
            $focusHousehold = (clone $householdQuery)
                ->with([
                    'community',
                    'members' => fn ($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
                ])
                ->first();
        }

        $householdSummary = $this->buildHouseholdSummary($householdQuery, $memberHouseholdId, $filters);
        $transactionSummary = $this->buildTransactionSummary($transactionQuery, $depositDetailQuery);
        $monthlySummary = $this->buildMonthlySummary($transactionQuery, $depositDetailQuery);
        $topMaterials = $this->buildTopMaterials($depositDetailQuery);
        $recentTransactions = (clone $transactionQuery)
            ->with('household.community')
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->limit(8)
            ->get();

        $communityStats = collect();
        $topHouseholds = collect();
        $pendingHouseholds = collect();

        if ($isPrivileged) {
            $communityStats = $this->buildCommunityStats($filters);
            $topHouseholds = $this->buildTopHouseholds($filters);
            $pendingHouseholds = (clone $householdQuery)
                ->with('community')
                ->where('active_status', 'pending')
                ->orderBy('register_date')
                ->limit(8)
                ->get();
        }

        $selectedCommunity = $filters->communityId
            ? $communities->firstWhere('community_id', $filters->communityId)
            : null;
        $selectedCategory = $filters->categoryId
            ? $materialCategories->firstWhere('category_id', $filters->categoryId)
            : null;
        $statusLabels = $this->statusLabels();

        return [
            'isPrivileged' => $isPrivileged,
            'from' => $filters->from,
            'to' => $filters->to,
            'communityId' => $filters->communityId,
            'householdStatus' => $filters->householdStatus,
            'categoryId' => $filters->categoryId,
            'communities' => $communities,
            'materialCategories' => $materialCategories,
            'selectedCommunity' => $selectedCommunity,
            'selectedCategory' => $selectedCategory,
            'statusLabels' => $statusLabels,
            'statusClasses' => $this->statusClasses(),
            'statusText' => $filters->householdStatus ? ($statusLabels[$filters->householdStatus] ?? $filters->householdStatus) : null,
            'periodLabel' => $this->buildPeriodLabel($filters),
            'filterSummary' => $this->buildFilterSummary($communities, $materialCategories, $filters, $statusLabels),
            'householdSummary' => $householdSummary,
            'transactionSummary' => $transactionSummary,
            'monthlySummary' => $monthlySummary,
            'monthlyMaxAmount' => max(1, (float) $monthlySummary->map(fn ($row) => max((float) $row->deposit_amount, (float) $row->withdraw_amount))->max()),
            'topMaterials' => $topMaterials,
            'materialMaxWeight' => max(1, (float) ($topMaterials->max('total_weight') ?? 0)),
            'recentTransactions' => $recentTransactions,
            'communityStats' => $communityStats,
            'topHouseholds' => $topHouseholds,
            'pendingHouseholds' => $pendingHouseholds,
            'focusHousehold' => $focusHousehold,
            'statusChart' => [
                'labels' => ['ใช้งาน', 'รออนุมัติ', 'ปิดใช้งาน'],
                'values' => [
                    $householdSummary['activeHouseholds'],
                    $householdSummary['pendingHouseholds'],
                    $householdSummary['inactiveHouseholds'],
                ],
            ],
            'cashflowChart' => [
                'labels' => ['รับซื้อ', 'ถอน'],
                'values' => [
                    round((float) $transactionSummary['depositAmount'], 2),
                    round((float) $transactionSummary['withdrawAmount'], 2),
                ],
            ],
            'materialChart' => [
                'labels' => $topMaterials->take(6)->pluck('material_name')->values()->all(),
                'values' => $topMaterials->take(6)->map(fn ($material) => round((float) $material->total_weight, 2))->values()->all(),
            ],
            'monthlyChart' => [
                'labels' => $monthlySummary->reverse()->pluck('month_label')->values()->all(),
                'deposit' => $monthlySummary->reverse()->map(fn ($month) => round((float) $month->deposit_amount, 2))->values()->all(),
                'withdraw' => $monthlySummary->reverse()->map(fn ($month) => round((float) $month->withdraw_amount, 2))->values()->all(),
            ],
        ];
    }

    private function householdScope(bool $isPrivileged, ?int $memberHouseholdId, ReportFilters $filters): Builder
    {
        return Household::query()
            ->when($isPrivileged && $filters->communityId, fn (Builder $query) => $query->where('community_id', $filters->communityId))
            ->when($isPrivileged && $filters->householdStatus, fn (Builder $query) => $query->where('active_status', $filters->householdStatus))
            ->when(
                ! $isPrivileged,
                function (Builder $query) use ($memberHouseholdId) {
                    if ($memberHouseholdId) {
                        $query->where('household_id', $memberHouseholdId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
            );
    }

    private function transactionScope(bool $isPrivileged, ?int $memberHouseholdId, ReportFilters $filters): Builder
    {
        return Transaction::query()
            ->when(
                ! $isPrivileged,
                function (Builder $query) use ($memberHouseholdId) {
                    if ($memberHouseholdId) {
                        $query->where('household_id', $memberHouseholdId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
            )
            ->when(
                $isPrivileged && ($filters->communityId || $filters->householdStatus),
                function (Builder $query) use ($filters) {
                    $query->whereHas('household', function (Builder $subQuery) use ($filters) {
                        if ($filters->communityId) {
                            $subQuery->where('community_id', $filters->communityId);
                        }

                        if ($filters->householdStatus) {
                            $subQuery->where('active_status', $filters->householdStatus);
                        }
                    });
                }
            )
            ->when($filters->from, fn (Builder $query) => $query->whereDate('transaction_date', '>=', $filters->from))
            ->when($filters->to, fn (Builder $query) => $query->whereDate('transaction_date', '<=', $filters->to))
            ->when(
                $filters->categoryId,
                function (Builder $query) use ($filters) {
                    $query->where(function (Builder $subQuery) use ($filters) {
                        $subQuery->where('transaction_type', 'withdraw')
                            ->orWhere(function (Builder $depositQuery) use ($filters) {
                                $depositQuery->where('transaction_type', 'deposit')
                                    ->whereHas('details.material', fn (Builder $materialQuery) => $materialQuery->where('category_id', $filters->categoryId));
                            });
                    });
                }
            );
    }

    private function depositDetailScope(bool $isPrivileged, ?int $memberHouseholdId, ReportFilters $filters)
    {
        return DB::table('transaction_detail as td')
            ->join('transaction as t', 't.transaction_id', '=', 'td.transaction_id')
            ->join('material as m', 'm.material_id', '=', 'td.material_id')
            ->leftJoin('material_category as mc', 'mc.category_id', '=', 'm.category_id')
            ->join('household as h', 'h.household_id', '=', 't.household_id')
            ->where('t.transaction_type', 'deposit')
            ->when(! $isPrivileged, function ($query) use ($memberHouseholdId) {
                if ($memberHouseholdId) {
                    $query->where('t.household_id', $memberHouseholdId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($isPrivileged && $filters->communityId, fn ($query) => $query->where('h.community_id', $filters->communityId))
            ->when($isPrivileged && $filters->householdStatus, fn ($query) => $query->where('h.active_status', $filters->householdStatus))
            ->when($filters->from, fn ($query) => $query->whereDate('t.transaction_date', '>=', $filters->from))
            ->when($filters->to, fn ($query) => $query->whereDate('t.transaction_date', '<=', $filters->to))
            ->when($filters->categoryId, fn ($query) => $query->where('m.category_id', $filters->categoryId));
    }

    private function buildHouseholdSummary(Builder $householdQuery, ?int $memberHouseholdId, ReportFilters $filters): array
    {
        $summary = (clone $householdQuery)
            ->selectRaw('COUNT(*) as total_households')
            ->selectRaw("SUM(CASE WHEN active_status = 'active' THEN 1 ELSE 0 END) as active_households")
            ->selectRaw("SUM(CASE WHEN active_status = 'pending' THEN 1 ELSE 0 END) as pending_households")
            ->selectRaw("SUM(CASE WHEN active_status = 'inactive' THEN 1 ELSE 0 END) as inactive_households")
            ->selectRaw('COALESCE(SUM(total_balance), 0) as total_balance')
            ->selectRaw('COALESCE(AVG(total_balance), 0) as average_balance')
            ->first();

        $memberCount = DB::table('member as m')
            ->join('household as h', 'h.household_id', '=', 'm.household_id')
            ->when($memberHouseholdId, fn ($query) => $query->where('h.household_id', $memberHouseholdId))
            ->when($filters->communityId, fn ($query) => $query->where('h.community_id', $filters->communityId))
            ->when($filters->householdStatus, fn ($query) => $query->where('h.active_status', $filters->householdStatus))
            ->count();

        return [
            'totalHouseholds' => (int) ($summary->total_households ?? 0),
            'activeHouseholds' => (int) ($summary->active_households ?? 0),
            'pendingHouseholds' => (int) ($summary->pending_households ?? 0),
            'inactiveHouseholds' => (int) ($summary->inactive_households ?? 0),
            'totalBalance' => (float) ($summary->total_balance ?? 0),
            'averageBalance' => (float) ($summary->average_balance ?? 0),
            'memberCount' => (int) $memberCount,
        ];
    }

    private function buildTransactionSummary(Builder $transactionQuery, $depositDetailQuery): array
    {
        $withdrawSummary = (clone $transactionQuery)
            ->where('transaction_type', 'withdraw')
            ->selectRaw('COUNT(*) as withdraw_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as withdraw_amount')
            ->first();

        $depositSummary = (clone $depositDetailQuery)
            ->selectRaw('COUNT(DISTINCT t.transaction_id) as deposit_count')
            ->selectRaw('COALESCE(SUM(td.amount), 0) as deposit_amount')
            ->selectRaw('COALESCE(SUM(td.weight), 0) as deposit_weight')
            ->first();

        $depositAmount = (float) ($depositSummary->deposit_amount ?? 0);
        $withdrawAmount = (float) ($withdrawSummary->withdraw_amount ?? 0);
        $depositCount = (int) ($depositSummary->deposit_count ?? 0);
        $withdrawCount = (int) ($withdrawSummary->withdraw_count ?? 0);

        return [
            'transactionCount' => $depositCount + $withdrawCount,
            'depositCount' => $depositCount,
            'withdrawCount' => $withdrawCount,
            'depositAmount' => $depositAmount,
            'withdrawAmount' => $withdrawAmount,
            'netAmount' => $depositAmount - $withdrawAmount,
            'depositWeight' => (float) ($depositSummary->deposit_weight ?? 0),
            'averageDepositAmount' => $depositCount > 0 ? $depositAmount / $depositCount : 0.0,
        ];
    }

    private function buildMonthlySummary(Builder $transactionQuery, $depositDetailQuery): Collection
    {
        $depositRows = (clone $depositDetailQuery)
            ->select('t.transaction_id', 't.transaction_date', 'td.amount', 'td.weight')
            ->orderBy('t.transaction_date')
            ->get();

        $withdrawRows = (clone $transactionQuery)
            ->where('transaction_type', 'withdraw')
            ->orderBy('transaction_date')
            ->get(['transaction_id', 'transaction_date', 'total_amount']);

        $monthKeys = $depositRows
            ->pluck('transaction_date')
            ->merge($withdrawRows->pluck('transaction_date'))
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->sort()
            ->values();

        return $monthKeys->map(function (string $monthKey) use ($depositRows, $withdrawRows) {
            $depositInMonth = $depositRows->filter(
                fn ($row) => Carbon::parse($row->transaction_date)->format('Y-m') === $monthKey
            );
            $withdrawInMonth = $withdrawRows->filter(
                fn ($row) => $row->transaction_date->format('Y-m') === $monthKey
            );

            return (object) [
                'month_key' => $monthKey,
                'month_label' => Carbon::createFromFormat('Y-m', $monthKey)->format('m/Y'),
                'transaction_count' => $depositInMonth->pluck('transaction_id')->unique()->count() + $withdrawInMonth->count(),
                'deposit_amount' => (float) $depositInMonth->sum(fn ($row) => (float) $row->amount),
                'withdraw_amount' => (float) $withdrawInMonth->sum(fn (Transaction $row) => (float) $row->total_amount),
                'deposit_weight' => (float) $depositInMonth->sum(fn ($row) => (float) $row->weight),
            ];
        })->sortByDesc('month_key')->values()->take(12);
    }

    private function buildTopMaterials($depositDetailQuery): Collection
    {
        return (clone $depositDetailQuery)
            ->select('td.material_id', 'm.material_name', 'm.unit', 'mc.category_name')
            ->selectRaw('COALESCE(SUM(td.weight), 0) as total_weight')
            ->selectRaw('COALESCE(SUM(td.amount), 0) as total_amount')
            ->selectRaw('COUNT(DISTINCT t.transaction_id) as transaction_count')
            ->groupBy('td.material_id', 'm.material_name', 'm.unit', 'mc.category_name')
            ->orderByDesc('total_weight')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();
    }

    private function buildCommunityStats(ReportFilters $filters): Collection
    {
        $communities = Community::query()
            ->when($filters->communityId, fn (Builder $query) => $query->where('community_id', $filters->communityId))
            ->orderBy('community_id')
            ->get(['community_id', 'community_name']);

        $householdStats = Household::query()
            ->when($filters->communityId, fn (Builder $query) => $query->where('community_id', $filters->communityId))
            ->when($filters->householdStatus, fn (Builder $query) => $query->where('active_status', $filters->householdStatus))
            ->select('community_id')
            ->selectRaw('COUNT(*) as household_count')
            ->selectRaw("SUM(CASE WHEN active_status = 'active' THEN 1 ELSE 0 END) as active_household_count")
            ->selectRaw("SUM(CASE WHEN active_status = 'pending' THEN 1 ELSE 0 END) as pending_household_count")
            ->selectRaw("SUM(CASE WHEN active_status = 'inactive' THEN 1 ELSE 0 END) as inactive_household_count")
            ->selectRaw('COALESCE(SUM(total_balance), 0) as total_balance')
            ->groupBy('community_id')
            ->get()
            ->keyBy('community_id');

        $memberStats = DB::table('member as m')
            ->join('household as h', 'h.household_id', '=', 'm.household_id')
            ->when($filters->communityId, fn ($query) => $query->where('h.community_id', $filters->communityId))
            ->when($filters->householdStatus, fn ($query) => $query->where('h.active_status', $filters->householdStatus))
            ->select('h.community_id')
            ->selectRaw('COUNT(*) as member_count')
            ->groupBy('h.community_id')
            ->get()
            ->keyBy('community_id');

        $depositStats = $this->depositDetailScope(true, null, $filters)
            ->select('h.community_id')
            ->selectRaw('COALESCE(SUM(td.amount), 0) as deposit_amount')
            ->selectRaw('COALESCE(SUM(td.weight), 0) as deposit_weight')
            ->selectRaw('COUNT(DISTINCT t.transaction_id) as deposit_count')
            ->groupBy('h.community_id')
            ->get()
            ->keyBy('community_id');

        $withdrawStats = DB::table('transaction as t')
            ->join('household as h', 'h.household_id', '=', 't.household_id')
            ->where('t.transaction_type', 'withdraw')
            ->when($filters->communityId, fn ($query) => $query->where('h.community_id', $filters->communityId))
            ->when($filters->householdStatus, fn ($query) => $query->where('h.active_status', $filters->householdStatus))
            ->when($filters->from, fn ($query) => $query->whereDate('t.transaction_date', '>=', $filters->from))
            ->when($filters->to, fn ($query) => $query->whereDate('t.transaction_date', '<=', $filters->to))
            ->select('h.community_id')
            ->selectRaw('COUNT(*) as withdraw_count')
            ->selectRaw('COALESCE(SUM(t.total_amount), 0) as withdraw_amount')
            ->groupBy('h.community_id')
            ->get()
            ->keyBy('community_id');

        return $communities->map(function (Community $community) use ($householdStats, $memberStats, $depositStats, $withdrawStats) {
            $household = $householdStats->get($community->community_id);
            $members = $memberStats->get($community->community_id);
            $deposits = $depositStats->get($community->community_id);
            $withdraws = $withdrawStats->get($community->community_id);

            return (object) [
                'community_id' => $community->community_id,
                'community_name' => $community->community_name,
                'household_count' => (int) ($household->household_count ?? 0),
                'active_household_count' => (int) ($household->active_household_count ?? 0),
                'pending_household_count' => (int) ($household->pending_household_count ?? 0),
                'inactive_household_count' => (int) ($household->inactive_household_count ?? 0),
                'member_count' => (int) ($members->member_count ?? 0),
                'transaction_count' => (int) (($deposits->deposit_count ?? 0) + ($withdraws->withdraw_count ?? 0)),
                'deposit_amount' => (float) ($deposits->deposit_amount ?? 0),
                'withdraw_amount' => (float) ($withdraws->withdraw_amount ?? 0),
                'deposit_weight' => (float) ($deposits->deposit_weight ?? 0),
                'total_balance' => (float) ($household->total_balance ?? 0),
            ];
        });
    }

    private function buildTopHouseholds(ReportFilters $filters): Collection
    {
        $deposits = $this->depositDetailScope(true, null, $filters)
            ->leftJoin('community as c', 'c.community_id', '=', 'h.community_id')
            ->select('h.household_id', 'h.account_no', 'h.contact_person', 'h.total_balance', 'c.community_name')
            ->selectRaw('COUNT(DISTINCT t.transaction_id) as transaction_count')
            ->selectRaw('COALESCE(SUM(td.amount), 0) as deposit_amount')
            ->selectRaw('COALESCE(SUM(td.weight), 0) as deposit_weight')
            ->groupBy('h.household_id', 'h.account_no', 'h.contact_person', 'h.total_balance', 'c.community_name')
            ->get()
            ->keyBy('household_id');

        $withdraws = DB::table('transaction as t')
            ->join('household as h', 'h.household_id', '=', 't.household_id')
            ->where('t.transaction_type', 'withdraw')
            ->when($filters->communityId, fn ($query) => $query->where('h.community_id', $filters->communityId))
            ->when($filters->householdStatus, fn ($query) => $query->where('h.active_status', $filters->householdStatus))
            ->when($filters->from, fn ($query) => $query->whereDate('t.transaction_date', '>=', $filters->from))
            ->when($filters->to, fn ($query) => $query->whereDate('t.transaction_date', '<=', $filters->to))
            ->select('h.household_id')
            ->selectRaw('COALESCE(SUM(t.total_amount), 0) as withdraw_amount')
            ->groupBy('h.household_id')
            ->get()
            ->keyBy('household_id');

        return $deposits
            ->map(function ($household) use ($withdraws) {
                $household->withdraw_amount = (float) ($withdraws->get($household->household_id)->withdraw_amount ?? 0);

                return $household;
            })
            ->sortByDesc('deposit_amount')
            ->take(10)
            ->values();
    }

    private function buildPeriodLabel(ReportFilters $filters): string
    {
        if (! $filters->from && ! $filters->to) {
            return 'ทุกช่วงเวลา';
        }

        if ($filters->from && $filters->to) {
            return sprintf('ตั้งแต่ %s ถึง %s', date('d/m/Y', strtotime($filters->from)), date('d/m/Y', strtotime($filters->to)));
        }

        if ($filters->from) {
            return sprintf('ตั้งแต่ %s', date('d/m/Y', strtotime($filters->from)));
        }

        return sprintf('ถึง %s', date('d/m/Y', strtotime($filters->to)));
    }

    private function buildFilterSummary(
        Collection $communities,
        Collection $materialCategories,
        ReportFilters $filters,
        array $statusLabels
    ): array {
        $summary = [];

        if ($filters->communityId) {
            $community = $communities->firstWhere('community_id', $filters->communityId);
            if ($community) {
                $summary[] = 'ชุมชน: '.$community->community_id.' - '.$community->community_name;
            }
        }

        if ($filters->householdStatus) {
            $summary[] = 'สถานะครัวเรือน: '.($statusLabels[$filters->householdStatus] ?? $filters->householdStatus);
        }

        if ($filters->categoryId) {
            $category = $materialCategories->firstWhere('category_id', $filters->categoryId);
            if ($category) {
                $summary[] = 'หมวดวัสดุ: '.$category->category_name;
            }
        }

        return $summary;
    }

    private function statusLabels(): array
    {
        return [
            'active' => 'ใช้งาน',
            'pending' => 'รออนุมัติ',
            'inactive' => 'ปิดใช้งาน',
        ];
    }

    private function statusClasses(): array
    {
        return [
            'active' => 'bg-success-subtle text-success',
            'pending' => 'bg-warning-subtle text-warning-emphasis',
            'inactive' => 'bg-secondary-subtle text-secondary',
        ];
    }

    private function memberHouseholdId(UserAccount $user): ?int
    {
        $householdId = $user->household_id;

        return $householdId ? (int) $householdId : null;
    }
}

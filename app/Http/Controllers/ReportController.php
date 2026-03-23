<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Household;
use App\Models\MaterialCategory;
use App\Models\Transaction;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request, true);

        if (isset($data['redirect'])) {
            return $data['redirect'];
        }

        return view('reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);

        ActivityLogger::forCurrentUser('reports', 'ส่งออกรายงานสรุปเป็น PDF');

        $pdf = Pdf::loadView('pdf.report_summary', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->buildExportFilename('pdf'));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildReportData($request);

        ActivityLogger::forCurrentUser('reports', 'ส่งออกรายงานสรุปเป็น Excel');

        $spreadsheet = $this->buildSpreadsheet($data);
        $tempFile = tempnam(sys_get_temp_dir(), 'recyclebank-report-');

        if ($tempFile === false) {
            abort(500, 'ไม่สามารถสร้างไฟล์รายงานชั่วคราวได้');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download(
            $tempFile,
            $this->buildExportFilename('xlsx'),
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function buildReportData(Request $request, bool $redirectOnMissingHousehold = false): array
    {
        $user = $request->user();
        $isPrivileged = in_array($user->role, ['admin', 'staff'], true);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'community_id' => $isPrivileged ? ['nullable', 'string', 'exists:community,community_id'] : ['nullable'],
            'household_status' => $isPrivileged ? ['nullable', 'in:active,pending,inactive'] : ['nullable'],
            'category_id' => ['nullable', 'integer', 'exists:material_category,category_id'],
        ], [
            'to.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',
            'community_id.exists' => 'ไม่พบชุมชนที่เลือก',
            'household_status.in' => 'สถานะครัวเรือนไม่ถูกต้อง',
            'category_id.exists' => 'ไม่พบหมวดวัสดุที่เลือก',
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;
        $communityId = $isPrivileged ? ($validated['community_id'] ?? null) : null;
        $householdStatus = $isPrivileged ? ($validated['household_status'] ?? null) : null;
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $memberHouseholdId = $isPrivileged ? null : $this->memberHouseholdId($request);

        if (! $isPrivileged && ! $memberHouseholdId) {
            if ($redirectOnMissingHousehold) {
                return [
                    'redirect' => redirect()->route('main-menu')
                        ->withErrors('ไม่พบบัญชีครัวเรือนของผู้ใช้นี้'),
                ];
            }

            abort(404, 'ไม่พบบัญชีครัวเรือนของผู้ใช้นี้');
        }

        $communities = $isPrivileged
            ? Community::query()->orderBy('community_id')->get(['community_id', 'community_name'])
            : collect();
        $materialCategories = MaterialCategory::query()
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        $householdQuery = $this->householdScope($isPrivileged, $memberHouseholdId, $communityId, $householdStatus);
        $transactionQuery = $this->transactionScope(
            $isPrivileged,
            $memberHouseholdId,
            $communityId,
            $householdStatus,
            $from,
            $to,
            $categoryId
        );
        $depositDetailQuery = $this->depositDetailScope(
            $isPrivileged,
            $memberHouseholdId,
            $communityId,
            $householdStatus,
            $from,
            $to,
            $categoryId
        );

        $focusHousehold = null;
        if (! $isPrivileged) {
            $focusHousehold = (clone $householdQuery)
                ->with([
                    'community',
                    'members' => fn ($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
                ])
                ->first();
        }

        $householdSummary = $this->buildHouseholdSummary($householdQuery, $memberHouseholdId, $communityId, $householdStatus);
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
            $communityStats = $this->buildCommunityStats($communityId, $householdStatus, $from, $to, $categoryId);
            $topHouseholds = $this->buildTopHouseholds($communityId, $householdStatus, $from, $to, $categoryId);
            $pendingHouseholds = (clone $householdQuery)
                ->with('community')
                ->where('active_status', 'pending')
                ->orderBy('register_date')
                ->limit(8)
                ->get();
        }

        $statusChart = [
            'labels' => ['ใช้งาน', 'รออนุมัติ', 'ปิดใช้งาน'],
            'values' => [
                $householdSummary['activeHouseholds'],
                $householdSummary['pendingHouseholds'],
                $householdSummary['inactiveHouseholds'],
            ],
        ];

        $cashflowChart = [
            'labels' => ['รับซื้อ', 'ถอน'],
            'values' => [
                round((float) $transactionSummary['depositAmount'], 2),
                round((float) $transactionSummary['withdrawAmount'], 2),
            ],
        ];

        $materialChart = [
            'labels' => $topMaterials->take(6)->pluck('material_name')->values()->all(),
            'values' => $topMaterials->take(6)->map(fn ($material) => round((float) $material->total_weight, 2))->values()->all(),
        ];

        $monthlyChart = [
            'labels' => $monthlySummary->reverse()->pluck('month_label')->values()->all(),
            'deposit' => $monthlySummary->reverse()->map(fn ($month) => round((float) $month->deposit_amount, 2))->values()->all(),
            'withdraw' => $monthlySummary->reverse()->map(fn ($month) => round((float) $month->withdraw_amount, 2))->values()->all(),
        ];

        return [
            'isPrivileged' => $isPrivileged,
            'from' => $from,
            'to' => $to,
            'communityId' => $communityId,
            'householdStatus' => $householdStatus,
            'categoryId' => $categoryId,
            'communities' => $communities,
            'materialCategories' => $materialCategories,
            'periodLabel' => $this->buildPeriodLabel($from, $to),
            'filterSummary' => $this->buildFilterSummary($communities, $materialCategories, $communityId, $householdStatus, $categoryId),
            'householdSummary' => $householdSummary,
            'transactionSummary' => $transactionSummary,
            'monthlySummary' => $monthlySummary,
            'topMaterials' => $topMaterials,
            'recentTransactions' => $recentTransactions,
            'communityStats' => $communityStats,
            'topHouseholds' => $topHouseholds,
            'pendingHouseholds' => $pendingHouseholds,
            'focusHousehold' => $focusHousehold,
            'statusChart' => $statusChart,
            'cashflowChart' => $cashflowChart,
            'materialChart' => $materialChart,
            'monthlyChart' => $monthlyChart,
        ];
    }

    private function householdScope(
        bool $isPrivileged,
        ?int $memberHouseholdId,
        ?string $communityId,
        ?string $householdStatus
    ): Builder {
        return Household::query()
            ->when($isPrivileged && $communityId, fn (Builder $query) => $query->where('community_id', $communityId))
            ->when($isPrivileged && $householdStatus, fn (Builder $query) => $query->where('active_status', $householdStatus))
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

    private function transactionScope(
        bool $isPrivileged,
        ?int $memberHouseholdId,
        ?string $communityId,
        ?string $householdStatus,
        ?string $from,
        ?string $to,
        ?int $categoryId
    ): Builder {
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
                $isPrivileged && ($communityId || $householdStatus),
                function (Builder $query) use ($communityId, $householdStatus) {
                    $query->whereHas('household', function (Builder $subQuery) use ($communityId, $householdStatus) {
                        if ($communityId) {
                            $subQuery->where('community_id', $communityId);
                        }

                        if ($householdStatus) {
                            $subQuery->where('active_status', $householdStatus);
                        }
                    });
                }
            )
            ->when($from, fn (Builder $query) => $query->whereDate('transaction_date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('transaction_date', '<=', $to))
            ->when(
                $categoryId,
                function (Builder $query) use ($categoryId) {
                    $query->where(function (Builder $subQuery) use ($categoryId) {
                        $subQuery->where('transaction_type', 'withdraw')
                            ->orWhere(function (Builder $depositQuery) use ($categoryId) {
                                $depositQuery->where('transaction_type', 'deposit')
                                    ->whereHas('details.material', fn (Builder $materialQuery) => $materialQuery->where('category_id', $categoryId));
                            });
                    });
                }
            );
    }

    private function depositDetailScope(
        bool $isPrivileged,
        ?int $memberHouseholdId,
        ?string $communityId,
        ?string $householdStatus,
        ?string $from,
        ?string $to,
        ?int $categoryId
    ) {
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
            ->when($isPrivileged && $communityId, fn ($query) => $query->where('h.community_id', $communityId))
            ->when($isPrivileged && $householdStatus, fn ($query) => $query->where('h.active_status', $householdStatus))
            ->when($from, fn ($query) => $query->whereDate('t.transaction_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('t.transaction_date', '<=', $to))
            ->when($categoryId, fn ($query) => $query->where('m.category_id', $categoryId));
    }

    private function buildHouseholdSummary(
        Builder $householdQuery,
        ?int $memberHouseholdId,
        ?string $communityId,
        ?string $householdStatus
    ): array {
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
            ->when($communityId, fn ($query) => $query->where('h.community_id', $communityId))
            ->when($householdStatus, fn ($query) => $query->where('h.active_status', $householdStatus))
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
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->sort()
            ->values();

        return $monthKeys->map(function (string $monthKey) use ($depositRows, $withdrawRows) {
            $depositInMonth = $depositRows->filter(
                fn ($row) => \Carbon\Carbon::parse($row->transaction_date)->format('Y-m') === $monthKey
            );
            $withdrawInMonth = $withdrawRows->filter(
                fn ($row) => $row->transaction_date->format('Y-m') === $monthKey
            );

            return (object) [
                'month_key' => $monthKey,
                'month_label' => \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('m/Y'),
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

    private function buildCommunityStats(
        ?string $communityId,
        ?string $householdStatus,
        ?string $from,
        ?string $to,
        ?int $categoryId
    ): Collection {
        $communities = Community::query()
            ->when($communityId, fn (Builder $query) => $query->where('community_id', $communityId))
            ->orderBy('community_id')
            ->get(['community_id', 'community_name']);

        $householdStats = Household::query()
            ->when($communityId, fn (Builder $query) => $query->where('community_id', $communityId))
            ->when($householdStatus, fn (Builder $query) => $query->where('active_status', $householdStatus))
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
            ->when($communityId, fn ($query) => $query->where('h.community_id', $communityId))
            ->when($householdStatus, fn ($query) => $query->where('h.active_status', $householdStatus))
            ->select('h.community_id')
            ->selectRaw('COUNT(*) as member_count')
            ->groupBy('h.community_id')
            ->get()
            ->keyBy('community_id');

        $depositStats = $this->depositDetailScope(true, null, $communityId, $householdStatus, $from, $to, $categoryId)
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
            ->when($communityId, fn ($query) => $query->where('h.community_id', $communityId))
            ->when($householdStatus, fn ($query) => $query->where('h.active_status', $householdStatus))
            ->when($from, fn ($query) => $query->whereDate('t.transaction_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('t.transaction_date', '<=', $to))
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

    private function buildTopHouseholds(
        ?string $communityId,
        ?string $householdStatus,
        ?string $from,
        ?string $to,
        ?int $categoryId
    ): Collection {
        $deposits = $this->depositDetailScope(true, null, $communityId, $householdStatus, $from, $to, $categoryId)
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
            ->when($communityId, fn ($query) => $query->where('h.community_id', $communityId))
            ->when($householdStatus, fn ($query) => $query->where('h.active_status', $householdStatus))
            ->when($from, fn ($query) => $query->whereDate('t.transaction_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('t.transaction_date', '<=', $to))
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

    private function buildPeriodLabel(?string $from, ?string $to): string
    {
        if (! $from && ! $to) {
            return 'ทุกช่วงเวลา';
        }

        if ($from && $to) {
            return sprintf('ตั้งแต่ %s ถึง %s', date('d/m/Y', strtotime($from)), date('d/m/Y', strtotime($to)));
        }

        if ($from) {
            return sprintf('ตั้งแต่ %s', date('d/m/Y', strtotime($from)));
        }

        return sprintf('ถึง %s', date('d/m/Y', strtotime($to)));
    }

    private function buildFilterSummary(
        Collection $communities,
        Collection $materialCategories,
        ?string $communityId,
        ?string $householdStatus,
        ?int $categoryId
    ): array {
        $summary = [];

        if ($communityId) {
            $community = $communities->firstWhere('community_id', $communityId);
            if ($community) {
                $summary[] = 'ชุมชน: ' . $community->community_id . ' - ' . $community->community_name;
            }
        }

        if ($householdStatus) {
            $statusLabels = [
                'active' => 'ใช้งาน',
                'pending' => 'รออนุมัติ',
                'inactive' => 'ปิดใช้งาน',
            ];
            $summary[] = 'สถานะครัวเรือน: ' . ($statusLabels[$householdStatus] ?? $householdStatus);
        }

        if ($categoryId) {
            $category = $materialCategories->firstWhere('category_id', $categoryId);
            if ($category) {
                $summary[] = 'หมวดวัสดุ: ' . $category->category_name;
            }
        }

        return $summary;
    }

    private function buildSpreadsheet(array $data): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary');

        $summaryRows = [
            ['สรุปรายงานธนาคารวัสดุรีไซเคิล', ''],
            ['ช่วงข้อมูล', $data['periodLabel']],
            ['สิทธิ์ผู้ใช้', $data['isPrivileged'] ? 'staff/admin' : 'member'],
            ['ตัวกรองเพิ่มเติม', $data['filterSummary'] !== [] ? implode(' | ', $data['filterSummary']) : 'ไม่มี'],
            ['', ''],
        ];

        if ($data['isPrivileged']) {
            $summaryRows = array_merge($summaryRows, [
                ['ครัวเรือนทั้งหมด', $data['householdSummary']['totalHouseholds']],
                ['ครัวเรือนใช้งาน', $data['householdSummary']['activeHouseholds']],
                ['ครัวเรือนรออนุมัติ', $data['householdSummary']['pendingHouseholds']],
                ['ครัวเรือนปิดใช้งาน', $data['householdSummary']['inactiveHouseholds']],
                ['สมาชิกในครัวเรือน', $data['householdSummary']['memberCount']],
                ['ยอดคงเหลือรวม', $data['householdSummary']['totalBalance']],
                ['ยอดรับซื้อรวม', $data['transactionSummary']['depositAmount']],
                ['ยอดถอนรวม', $data['transactionSummary']['withdrawAmount']],
                ['น้ำหนักรับซื้อรวม', $data['transactionSummary']['depositWeight']],
                ['จำนวนรายการรวม', $data['transactionSummary']['transactionCount']],
            ]);
        } else {
            $summaryRows = array_merge($summaryRows, [
                ['เลขบัญชี', $data['focusHousehold']?->account_no ?? '-'],
                ['ผู้ติดต่อ', $data['focusHousehold']?->contact_person ?? '-'],
                ['ชุมชน', $data['focusHousehold']?->community?->community_name ?? '-'],
                ['ยอดคงเหลือปัจจุบัน', (float) ($data['focusHousehold']?->total_balance ?? 0)],
                ['ยอดรับซื้อสะสม', $data['transactionSummary']['depositAmount']],
                ['ยอดถอนสะสม', $data['transactionSummary']['withdrawAmount']],
                ['น้ำหนักวัสดุที่ขายได้', $data['transactionSummary']['depositWeight']],
                ['จำนวนสมาชิกในครัวเรือน', $data['householdSummary']['memberCount']],
            ]);
        }

        $summarySheet->fromArray($summaryRows, null, 'A1');
        $this->styleWorksheet($summarySheet, 'A1:B' . count($summaryRows));

        $materialSheet = $spreadsheet->createSheet();
        $materialSheet->setTitle('Top Materials');
        $materialRows = [
            ['วัสดุ', 'หมวด', 'จำนวนรายการ', 'น้ำหนัก', 'มูลค่า'],
        ];
        foreach ($data['topMaterials'] as $material) {
            $materialRows[] = [
                $material->material_name,
                $material->category_name ?? 'ไม่ระบุหมวด',
                (int) $material->transaction_count,
                (float) $material->total_weight,
                (float) $material->total_amount,
            ];
        }
        $materialSheet->fromArray($materialRows, null, 'A1');
        $this->styleWorksheet($materialSheet, 'A1:E' . max(1, count($materialRows)));

        $monthlySheet = $spreadsheet->createSheet();
        $monthlySheet->setTitle('Monthly');
        $monthlyRows = [
            ['เดือน', 'จำนวนรายการ', 'ยอดรับซื้อ', 'ยอดถอน', 'น้ำหนักรับซื้อ'],
        ];
        foreach ($data['monthlySummary'] as $month) {
            $monthlyRows[] = [
                $month->month_label,
                (int) $month->transaction_count,
                (float) $month->deposit_amount,
                (float) $month->withdraw_amount,
                (float) $month->deposit_weight,
            ];
        }
        $monthlySheet->fromArray($monthlyRows, null, 'A1');
        $this->styleWorksheet($monthlySheet, 'A1:E' . max(1, count($monthlyRows)));

        $detailSheet = $spreadsheet->createSheet();
        if ($data['isPrivileged']) {
            $detailSheet->setTitle('Communities');
            $detailRows = [
                ['ชุมชน', 'ครัวเรือน', 'สมาชิก', 'ยอดรับซื้อ', 'ยอดถอน', 'น้ำหนัก', 'ยอดคงเหลือ'],
            ];
            foreach ($data['communityStats'] as $community) {
                $detailRows[] = [
                    $community->community_id . ' - ' . $community->community_name,
                    (int) $community->household_count,
                    (int) $community->member_count,
                    (float) $community->deposit_amount,
                    (float) $community->withdraw_amount,
                    (float) $community->deposit_weight,
                    (float) $community->total_balance,
                ];
            }
            $detailSheet->fromArray($detailRows, null, 'A1');
            $this->styleWorksheet($detailSheet, 'A1:G' . max(1, count($detailRows)));
        } else {
            $detailSheet->setTitle('Recent Transactions');
            $detailRows = [
                ['วันที่', 'ประเภท', 'น้ำหนัก', 'จำนวนเงิน'],
            ];
            foreach ($data['recentTransactions'] as $transaction) {
                $detailRows[] = [
                    optional($transaction->transaction_date)->format('d/m/Y'),
                    $transaction->transaction_type === 'deposit' ? 'ฝาก' : 'ถอน',
                    (float) $transaction->total_weight,
                    (float) $transaction->total_amount,
                ];
            }
            $detailSheet->fromArray($detailRows, null, 'A1');
            $this->styleWorksheet($detailSheet, 'A1:D' . max(1, count($detailRows)));
        }

        return $spreadsheet;
    }

    private function styleWorksheet(Worksheet $sheet, string $range): void
    {
        [$startCell, $endCell] = explode(':', $range);
        preg_match('/([A-Z]+)(\d+)/', $endCell, $matches);
        $lastColumn = $matches[1] ?? 'A';
        $lastRow = (int) ($matches[2] ?? 1);

        $sheet->getStyle($range)->getFont()->setName('Arial');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('D7F0E3');

        for ($columnIndex = 1; $columnIndex <= Coordinate::columnIndexFromString($lastColumn); $columnIndex++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
        }

        if ($lastRow >= 2) {
            $sheet->getStyle('A2:' . $lastColumn . $lastRow)->getAlignment()->setWrapText(true);
        }
    }

    private function buildExportFilename(string $extension): string
    {
        return 'report_' . now()->format('Ymd_His') . '.' . $extension;
    }

    private function memberHouseholdId(Request $request): ?int
    {
        $householdId = $request->user()?->household_id;

        return $householdId ? (int) $householdId : null;
    }
}

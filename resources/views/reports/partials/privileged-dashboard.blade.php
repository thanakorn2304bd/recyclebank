<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">ครัวเรือนทั้งหมด</div>
      <div class="rb-metric-value">{{ number_format($householdSummary['totalHouseholds']) }}</div>
      <div class="rb-metric-note mt-2">
        ใช้งาน {{ number_format($householdSummary['activeHouseholds']) }} · รออนุมัติ {{ number_format($householdSummary['pendingHouseholds']) }}
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">สมาชิกในครัวเรือน</div>
      <div class="rb-metric-value">{{ number_format($householdSummary['memberCount']) }}</div>
      <div class="rb-metric-note mt-2">
        ค่าเฉลี่ยยอดคงเหลือ {{ number_format($householdSummary['averageBalance'], 2) }} บาท/ครัวเรือน
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">ยอดรับซื้อรวม</div>
      <div class="rb-metric-value">{{ number_format($transactionSummary['depositAmount'], 2) }}</div>
      <div class="rb-metric-note mt-2">
        น้ำหนักรวม {{ number_format($transactionSummary['depositWeight'], 2) }} กก.
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">ยอดถอนรวม</div>
      <div class="rb-metric-value">{{ number_format($transactionSummary['withdrawAmount'], 2) }}</div>
      <div class="rb-metric-note mt-2">
        รายการทั้งหมด {{ number_format($transactionSummary['transactionCount']) }} ครั้ง
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-8">
    <div class="card rb-chart-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-1">กราฟแนวโน้มรายเดือน</div>
        <div class="small text-muted mb-3">เปรียบเทียบยอดรับซื้อกับยอดถอนในแต่ละเดือน</div>
        <div class="rb-chart-shell">
          <canvas id="rbMonthlyChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card rb-chart-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-1">สัดส่วนสถานะครัวเรือน</div>
        <div class="small text-muted mb-3">ดูโครงสร้างครัวเรือนที่ใช้งาน, รออนุมัติ และปิดใช้งาน</div>
        <div class="rb-chart-shell rb-chart-shell-sm">
          <canvas id="rbStatusChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5">
    <div class="card rb-chart-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-1">กราฟวัสดุยอดนิยม</div>
        <div class="small text-muted mb-3">น้ำหนักวัสดุที่รับซื้อมากที่สุด 6 อันดับแรก</div>
        <div class="rb-chart-shell rb-chart-shell-sm">
          <canvas id="rbMaterialChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-7">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="rb-section-title">วัสดุที่รับซื้อมากที่สุด</div>
          <span class="badge text-bg-light">Top {{ $topMaterials->count() }}</span>
        </div>
        <div class="table-responsive">
          <table class="table rb-table align-middle mb-0">
            <thead>
              <tr>
                <th>วัสดุ</th>
                <th style="width: 180px;">สัดส่วน</th>
                <th class="text-end" style="width: 120px;">น้ำหนัก</th>
                <th class="text-end" style="width: 140px;">มูลค่า</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topMaterials as $material)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $material->material_name }}</div>
                    <div class="small text-muted">
                      {{ $material->category_name ?? 'ไม่ระบุหมวด' }} · {{ number_format((int) $material->transaction_count) }} รายการ
                    </div>
                  </td>
                  <td>
                    <div class="rb-bar-track">
                      <div class="rb-bar-fill" style="width: {{ min(100, round(((float) $material->total_weight / $materialMaxWeight) * 100, 2)) }}%;"></div>
                    </div>
                  </td>
                  <td class="text-end">{{ number_format((float) $material->total_weight, 2) }} {{ $material->unit }}</td>
                  <td class="text-end">{{ number_format((float) $material->total_amount, 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลการรับซื้อในช่วงที่เลือก</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="rb-section-title">ครัวเรือนรออนุมัติ</div>
          <span class="badge bg-warning-subtle text-warning-emphasis">{{ number_format($householdSummary['pendingHouseholds']) }} ราย</span>
        </div>
        <div class="rb-mini-list">
          @forelse($pendingHouseholds as $household)
            <div class="py-3">
              <div class="d-flex justify-content-between gap-3">
                <div>
                  <div class="fw-semibold">{{ $household->account_no }} - {{ $household->contact_person }}</div>
                  <div class="small text-muted">
                    {{ $household->community?->community_name ?? '-' }} · บ้านเลขที่ {{ $household->house_no }}
                  </div>
                </div>
                <div class="text-end small text-muted">
                  สมัครเมื่อ<br>{{ optional($household->register_date)->format('d/m/Y') }}
                </div>
              </div>
              <div class="mt-2">
                <a href="{{ route('households.show', $household) }}" class="btn btn-sm btn-outline-dark">ดูครัวเรือน</a>
              </div>
            </div>
          @empty
            <div class="py-4 text-center text-muted">ไม่มีครัวเรือนที่รออนุมัติในขณะนี้</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-7">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-3">สรุปตามเดือน</div>
        <div class="table-responsive">
          <table class="table rb-table align-middle mb-0">
            <thead>
              <tr>
                <th>เดือน</th>
                <th style="width: 160px;">แนวโน้ม</th>
                <th class="text-end" style="width: 120px;">รับซื้อ</th>
                <th class="text-end" style="width: 120px;">ถอน</th>
              </tr>
            </thead>
            <tbody>
              @forelse($monthlySummary as $month)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $month->month_label }}</div>
                    <div class="small text-muted">{{ number_format((int) $month->transaction_count) }} รายการ</div>
                  </td>
                  <td>
                    <div class="rb-bar-track mb-2">
                      <div class="rb-bar-fill" style="width: {{ min(100, round(((float) $month->deposit_amount / $monthlyMaxAmount) * 100, 2)) }}%;"></div>
                    </div>
                    <div class="small text-muted">น้ำหนัก {{ number_format((float) $month->deposit_weight, 2) }} กก.</div>
                  </td>
                  <td class="text-end text-success">{{ number_format((float) $month->deposit_amount, 2) }}</td>
                  <td class="text-end text-warning-emphasis">{{ number_format((float) $month->withdraw_amount, 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลรายเดือน</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-6">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="rb-section-title">ครัวเรือนที่มีมูลค่ารับซื้อสูงสุด</div>
          <span class="badge text-bg-light">Top {{ $topHouseholds->count() }}</span>
        </div>
        <div class="table-responsive">
          <table class="table rb-table align-middle mb-0">
            <thead>
              <tr>
                <th>ครัวเรือน</th>
                <th class="text-end" style="width: 120px;">รับซื้อ</th>
                <th class="text-end" style="width: 120px;">ถอน</th>
                <th class="text-end" style="width: 120px;">ยอดคงเหลือ</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topHouseholds as $household)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $household->account_no }} - {{ $household->contact_person }}</div>
                    <div class="small text-muted">
                      {{ $household->community_name ?? '-' }} · {{ number_format((int) $household->transaction_count) }} รายการ
                    </div>
                    <div class="mt-2">
                      <a href="{{ route('households.show', $household->household_id) }}" class="btn btn-sm btn-outline-dark">ดูครัวเรือน</a>
                    </div>
                  </td>
                  <td class="text-end text-success">{{ number_format((float) $household->deposit_amount, 2) }}</td>
                  <td class="text-end text-warning-emphasis">{{ number_format((float) $household->withdraw_amount, 2) }}</td>
                  <td class="text-end">{{ number_format((float) $household->total_balance, 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลครัวเรือนตามช่วงที่เลือก</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-3">สรุปตามชุมชน</div>
        <div class="table-responsive">
          <table class="table rb-table align-middle mb-0">
            <thead>
              <tr>
                <th>ชุมชน</th>
                <th class="text-end" style="width: 110px;">ครัวเรือน</th>
                <th class="text-end" style="width: 110px;">สมาชิก</th>
                <th class="text-end" style="width: 120px;">รับซื้อ</th>
                <th class="text-end" style="width: 120px;">ถอน</th>
                <th class="text-end" style="width: 120px;">น้ำหนัก</th>
              </tr>
            </thead>
            <tbody>
              @forelse($communityStats as $community)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $community->community_id }} - {{ $community->community_name }}</div>
                    <div class="small text-muted">
                      ใช้งาน {{ number_format((int) $community->active_household_count) }}
                      · รออนุมัติ {{ number_format((int) $community->pending_household_count) }}
                      · ปิด {{ number_format((int) $community->inactive_household_count) }}
                    </div>
                  </td>
                  <td class="text-end">{{ number_format((int) $community->household_count) }}</td>
                  <td class="text-end">{{ number_format((int) $community->member_count) }}</td>
                  <td class="text-end text-success">{{ number_format((float) $community->deposit_amount, 2) }}</td>
                  <td class="text-end text-warning-emphasis">{{ number_format((float) $community->withdraw_amount, 2) }}</td>
                  <td class="text-end">{{ number_format((float) $community->deposit_weight, 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลชุมชน</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

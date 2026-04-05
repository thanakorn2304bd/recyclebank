<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">ยอดคงเหลือปัจจุบัน</div>
      <div class="rb-metric-value">{{ number_format((float) ($focusHousehold?->total_balance ?? 0), 2) }}</div>
      <div class="rb-metric-note mt-2">สถานะบัญชี {{ $statusLabels[$focusHousehold?->active_status ?? 'inactive'] ?? '-' }}</div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">ยอดรับซื้อสะสม</div>
      <div class="rb-metric-value">{{ number_format($transactionSummary['depositAmount'], 2) }}</div>
      <div class="rb-metric-note mt-2">จำนวน {{ number_format($transactionSummary['depositCount']) }} รายการ</div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">ยอดถอนสะสม</div>
      <div class="rb-metric-value">{{ number_format($transactionSummary['withdrawAmount'], 2) }}</div>
      <div class="rb-metric-note mt-2">จำนวน {{ number_format($transactionSummary['withdrawCount']) }} รายการ</div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="p-3 rb-metric-card h-100">
      <div class="rb-metric-label">น้ำหนักวัสดุที่ขายได้</div>
      <div class="rb-metric-value">{{ number_format($transactionSummary['depositWeight'], 2) }}</div>
      <div class="rb-metric-note mt-2">สุทธิ {{ number_format($transactionSummary['netAmount'], 2) }} บาท</div>
    </div>
  </div>
</div>

@php
  $sectionOptions = [
      ['id' => 'monthly-chart', 'label' => 'กราฟรายเดือน'],
      ['id' => 'cashflow-chart', 'label' => 'สัดส่วนรับซื้อ/ถอน'],
      ['id' => 'household-info', 'label' => 'ข้อมูลครัวเรือน'],
      ['id' => 'monthly-table', 'label' => 'สรุปตามเดือน'],
      ['id' => 'material-chart', 'label' => 'กราฟวัสดุ'],
      ['id' => 'top-materials', 'label' => 'วัสดุที่ขายได้มากที่สุด'],
      ['id' => 'recent-transactions', 'label' => 'รายการล่าสุด'],
  ];
@endphp

@include('reports.partials.section-visibility-controls', [
  'sectionOptions' => $sectionOptions,
  'storageKey' => 'member',
])

<div class="row g-4 mb-4" data-report-section-row>
  <div class="col-xl-8" data-report-section="monthly-chart">
    <div class="card rb-chart-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-1">กราฟแนวโน้มรายเดือนของฉัน</div>
        <div class="small text-muted mb-3">ดูยอดรับซื้อและยอดถอนในแต่ละเดือนของครัวเรือนตัวเอง</div>
        <div class="rb-chart-shell">
          <canvas id="rbMonthlyChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4" data-report-section="cashflow-chart">
    <div class="card rb-chart-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-1">สัดส่วนยอดรับซื้อและถอน</div>
        <div class="small text-muted mb-3">เทียบมูลค่าที่ขายวัสดุได้กับยอดถอนของครัวเรือน</div>
        <div class="rb-chart-shell rb-chart-shell-sm">
          <canvas id="rbCashflowChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4" data-report-section-row>
  <div class="col-xl-5" data-report-section="household-info">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-3">ข้อมูลครัวเรือนของฉัน</div>
        @if($focusHousehold)
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <div class="small text-muted">เลขบัญชี</div>
              <div class="fw-semibold">{{ $focusHousehold->account_no }}</div>
            </div>
            <div class="col-sm-6">
              <div class="small text-muted">ชุมชน</div>
              <div class="fw-semibold">{{ $focusHousehold->community?->community_name ?? '-' }}</div>
            </div>
            <div class="col-sm-6">
              <div class="small text-muted">ผู้ติดต่อ</div>
              <div class="fw-semibold">{{ $focusHousehold->contact_person }}</div>
            </div>
            <div class="col-sm-6">
              <div class="small text-muted">สมาชิกในครัวเรือน</div>
              <div class="fw-semibold">{{ number_format($householdSummary['memberCount']) }} คน</div>
            </div>
          </div>

          <div class="mb-3">
            <span class="badge {{ $statusClasses[$focusHousehold->active_status] ?? 'bg-secondary-subtle text-secondary' }}">
              {{ $statusLabels[$focusHousehold->active_status] ?? $focusHousehold->active_status }}
            </span>
          </div>

          <div class="table-responsive">
            <table class="table rb-table align-middle mb-0">
              <thead>
                <tr>
                  <th>สมาชิก</th>
                  <th style="width: 150px;">ความสัมพันธ์</th>
                </tr>
              </thead>
              <tbody>
                @forelse($focusHousehold->members as $member)
                  <tr>
                    <td>
                      {{ $member->full_name }}
                      @if($member->is_head)
                        <span class="badge bg-success-subtle text-success ms-2">หัวหน้าครัวเรือน</span>
                      @endif
                    </td>
                    <td>{{ $member->relation }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="2" class="text-center text-muted py-3">ไม่พบข้อมูลสมาชิกในครัวเรือน</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-xl-7" data-report-section="monthly-table">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-3">สรุปตามเดือน</div>
        <div class="table-responsive">
          <table class="table rb-table align-middle mb-0">
            <thead>
              <tr>
                <th>เดือน</th>
                <th style="width: 180px;">แนวโน้ม</th>
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
                  <td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลรายการในช่วงที่เลือก</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4" data-report-section-row>
  <div class="col-xl-5" data-report-section="material-chart">
    <div class="card rb-chart-card h-100">
      <div class="card-body">
        <div class="rb-section-title mb-1">กราฟวัสดุที่ฉันขายได้มากที่สุด</div>
        <div class="small text-muted mb-3">ดูน้ำหนักวัสดุยอดนิยมของครัวเรือนตัวเอง</div>
        <div class="rb-chart-shell rb-chart-shell-sm">
          <canvas id="rbMaterialChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-7" data-report-section="top-materials">
    <div class="card rb-report-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="rb-section-title">วัสดุที่ฉันขายได้มากที่สุด</div>
          <span class="badge text-bg-light">Top {{ $topMaterials->count() }}</span>
        </div>
        <div class="table-responsive">
          <table class="table rb-table align-middle mb-0">
            <thead>
              <tr>
                <th>วัสดุ</th>
                <th style="width: 170px;">สัดส่วน</th>
                <th class="text-end" style="width: 120px;">น้ำหนัก</th>
                <th class="text-end" style="width: 120px;">มูลค่า</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topMaterials as $material)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $material->material_name }}</div>
                    <div class="small text-muted">{{ $material->category_name ?? 'ไม่ระบุหมวด' }}</div>
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
                  <td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลวัสดุที่ขายได้</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

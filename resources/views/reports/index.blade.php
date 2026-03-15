<x-layouts.admin title="สรุปรายงาน">
  @php
    $selectedCommunity = $communityId ? $communities->firstWhere('community_id', $communityId) : null;
    $selectedCategory = $categoryId ? $materialCategories->firstWhere('category_id', $categoryId) : null;
    $materialMaxWeight = max(1, (float) ($topMaterials->max('total_weight') ?? 0));
    $monthlyMaxAmount = max(1, (float) $monthlySummary->map(fn ($row) => max((float) $row->deposit_amount, (float) $row->withdraw_amount))->max());
    $statusLabels = [
        'active' => 'ใช้งาน',
        'pending' => 'รออนุมัติ',
        'inactive' => 'ปิดใช้งาน',
    ];
    $statusClasses = [
        'active' => 'bg-success-subtle text-success',
        'pending' => 'bg-warning-subtle text-warning-emphasis',
        'inactive' => 'bg-secondary-subtle text-secondary',
    ];
    $statusText = $householdStatus ? ($statusLabels[$householdStatus] ?? $householdStatus) : null;
  @endphp

  <style>
    .rb-report-card {
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      box-shadow: 0 12px 28px rgba(15, 109, 74, 0.08);
    }

    .rb-metric-card {
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      background: linear-gradient(160deg, #ffffff 0%, #f2fbf6 100%);
      box-shadow: 0 12px 30px rgba(15, 109, 74, 0.06);
    }

    .rb-metric-label {
      font-size: 0.82rem;
      color: #4b6b5c;
      margin-bottom: 0.35rem;
    }

    .rb-metric-value {
      font-size: 1.55rem;
      font-weight: 700;
      color: #0f5132;
      line-height: 1.1;
    }

    .rb-metric-note {
      color: #5f6b66;
      font-size: 0.85rem;
    }

    .rb-section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #0f5132;
    }

    .rb-bar-track {
      height: 0.45rem;
      border-radius: 999px;
      background: #e7f5ed;
      overflow: hidden;
    }

    .rb-bar-fill {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, #17a97a 0%, #0f6d4a 100%);
    }

    .rb-mini-list > div + div {
      border-top: 1px solid #edf7f1;
    }

    .rb-table td,
    .rb-table th {
      vertical-align: middle;
    }

    .rb-filter-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      border-radius: 999px;
      background: #eef8f2;
      border: 1px solid #d7f0e3;
      padding: 0.45rem 0.8rem;
      font-size: 0.82rem;
      color: #0f5132;
    }

    .rb-chart-card {
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      background: linear-gradient(180deg, #fcfffd 0%, #f4fbf7 100%);
      box-shadow: 0 16px 30px rgba(15, 109, 74, 0.07);
    }

    .rb-chart-shell {
      position: relative;
      min-height: 290px;
    }

    .rb-chart-shell canvas {
      width: 100% !important;
      height: 290px !important;
    }

    .rb-chart-shell.rb-chart-shell-sm {
      min-height: 250px;
    }

    .rb-chart-shell.rb-chart-shell-sm canvas {
      height: 250px !important;
    }

    .rb-note {
      border-left: 4px solid #17a97a;
      background: #f2fbf6;
      color: #325244;
      padding: 0.85rem 1rem;
      border-radius: 0.85rem;
      font-size: 0.9rem;
    }
  </style>

  <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
      <h3 class="mb-1">สรุปรายงาน</h3>
      <div class="text-muted">
        ช่วงข้อมูล {{ $periodLabel }}
        @if($selectedCommunity)
          · ชุมชน {{ $selectedCommunity->community_id }} - {{ $selectedCommunity->community_name }}
        @endif
        @if($statusText)
          · สถานะ {{ $statusText }}
        @endif
        @if($selectedCategory)
          · หมวดวัสดุ {{ $selectedCategory->category_name }}
        @endif
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-dark" href="{{ route('reports.export.pdf', request()->query()) }}">Export PDF</a>
      <a class="btn btn-outline-dark" href="{{ route('reports.export.excel', request()->query()) }}">Export Excel</a>
      <a class="btn btn-outline-dark" href="{{ route('transactions.index', request()->query()) }}">ดูประวัติรายการ</a>
      <a class="btn btn-success" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  <form class="row g-2 mb-3">
    <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }}">
      <label class="form-label">จากวันที่</label>
      <input type="date" class="form-control" name="from" value="{{ $from }}">
    </div>
    <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }}">
      <label class="form-label">ถึงวันที่</label>
      <input type="date" class="form-control" name="to" value="{{ $to }}">
    </div>
    @if($isPrivileged)
      <div class="col-md-2">
        <label class="form-label">ชุมชน</label>
        <select class="form-select" name="community_id">
          <option value="">ทุกชุมชน</option>
          @foreach($communities as $community)
            <option value="{{ $community->community_id }}" @selected($communityId === $community->community_id)>
              {{ $community->community_id }} - {{ $community->community_name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">สถานะครัวเรือน</label>
        <select class="form-select" name="household_status">
          <option value="">ทุกสถานะ</option>
          <option value="active" @selected($householdStatus === 'active')>ใช้งาน</option>
          <option value="pending" @selected($householdStatus === 'pending')>รออนุมัติ</option>
          <option value="inactive" @selected($householdStatus === 'inactive')>ปิดใช้งาน</option>
        </select>
      </div>
    @endif
    <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }}">
      <label class="form-label">หมวดวัสดุ</label>
      <select class="form-select" name="category_id">
        <option value="">ทุกหมวด</option>
        @foreach($materialCategories as $category)
          <option value="{{ $category->category_id }}" @selected((string) $categoryId === (string) $category->category_id)>
            {{ $category->category_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }} d-flex align-items-end gap-2">
      <button class="btn btn-primary w-100">ออกรายงาน</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('reports.index') }}">ล้าง</a>
    </div>
  </form>

  <div class="rb-note mb-3">
    หมวดวัสดุจะใช้กับข้อมูลรับซื้อ เช่น ยอดรับซื้อ, กราฟวัสดุ, น้ำหนัก, Top วัสดุ และอันดับครัวเรือนที่ขายวัสดุ ขณะที่ข้อมูลถอนยังแสดงตามช่วงวันที่และสิทธิ์ผู้ใช้ตามปกติ
  </div>

  @if($filterSummary !== [])
    <div class="d-flex flex-wrap gap-2 mb-4">
      @foreach($filterSummary as $item)
        <span class="rb-filter-chip">{{ $item }}</span>
      @endforeach
    </div>
  @endif

  @if($isPrivileged)
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
  @else
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

    <div class="row g-4 mb-4">
      <div class="col-xl-8">
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
      <div class="col-xl-4">
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

    <div class="row g-4 mb-4">
      <div class="col-xl-5">
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

      <div class="col-xl-7">
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

    <div class="row g-4 mb-4">
      <div class="col-xl-5">
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
      <div class="col-xl-7">
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
  @endif

  <div class="card rb-report-card">
    <div class="card-body">
      <div class="rb-section-title mb-3">{{ $isPrivileged ? 'รายการล่าสุดในระบบ' : 'รายการล่าสุดของฉัน' }}</div>
      <div class="table-responsive">
        <table class="table rb-table align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 100px;">วันที่</th>
              <th style="width: 95px;">ประเภท</th>
              @if($isPrivileged)
                <th>ครัวเรือน</th>
              @endif
              <th class="text-end" style="width: 110px;">น้ำหนัก</th>
              <th class="text-end" style="width: 120px;">จำนวนเงิน</th>
              <th style="width: 110px;"></th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentTransactions as $transaction)
              <tr>
                <td>{{ optional($transaction->transaction_date)->format('d/m/Y') }}</td>
                <td>
                  @if($transaction->transaction_type === 'deposit')
                    <span class="badge bg-success">ฝาก</span>
                  @else
                    <span class="badge bg-warning text-dark">ถอน</span>
                  @endif
                </td>
                @if($isPrivileged)
                  <td>
                    <div class="fw-semibold">{{ $transaction->household?->account_no }} - {{ $transaction->household?->contact_person }}</div>
                    <div class="small text-muted">{{ $transaction->household?->community?->community_name ?? '-' }}</div>
                  </td>
                @endif
                <td class="text-end">{{ number_format((float) $transaction->total_weight, 2) }}</td>
                <td class="text-end">{{ number_format((float) $transaction->total_amount, 2) }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('transactions.show', $transaction) }}">ดู</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ $isPrivileged ? 6 : 5 }}" class="text-center text-muted py-4">ยังไม่มีรายการในระบบ</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    (function () {
      const monthlyLabels = @json($monthlyChart['labels']);
      const monthlyDeposit = @json($monthlyChart['deposit']);
      const monthlyWithdraw = @json($monthlyChart['withdraw']);
      const cashflowLabels = @json($cashflowChart['labels']);
      const cashflowValues = @json($cashflowChart['values']);
      const statusLabels = @json($statusChart['labels']);
      const statusValues = @json($statusChart['values']);
      const materialLabels = @json($materialChart['labels']);
      const materialValues = @json($materialChart['values']);

      function createChart(id, config) {
        const element = document.getElementById(id);
        if (!element || typeof Chart === 'undefined') {
          return null;
        }

        return new Chart(element, config);
      }

      createChart('rbMonthlyChart', {
        type: 'bar',
        data: {
          labels: monthlyLabels,
          datasets: [
            {
              label: 'ยอดรับซื้อ',
              data: monthlyDeposit,
              backgroundColor: 'rgba(23, 169, 122, 0.82)',
              borderRadius: 8,
              maxBarThickness: 34,
            },
            {
              label: 'ยอดถอน',
              data: monthlyWithdraw,
              backgroundColor: 'rgba(245, 158, 11, 0.82)',
              borderRadius: 8,
              maxBarThickness: 34,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
          scales: {
            x: {
              grid: {
                display: false,
              },
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback(value) {
                  return Number(value).toLocaleString('th-TH');
                },
              },
            },
          },
        },
      });

      createChart('rbMaterialChart', {
        type: 'bar',
        data: {
          labels: materialLabels,
          datasets: [
            {
              label: 'น้ำหนักรวม',
              data: materialValues,
              backgroundColor: [
                '#16a34a',
                '#0f766e',
                '#2563eb',
                '#0891b2',
                '#84cc16',
                '#10b981',
              ],
              borderRadius: 10,
              maxBarThickness: 42,
            },
          ],
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false,
            },
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: {
                callback(value) {
                  return Number(value).toLocaleString('th-TH');
                },
              },
            },
            y: {
              grid: {
                display: false,
              },
            },
          },
        },
      });

      createChart('rbStatusChart', {
        type: 'doughnut',
        data: {
          labels: statusLabels,
          datasets: [
            {
              data: statusValues,
              backgroundColor: ['#16a34a', '#f59e0b', '#64748b'],
              borderWidth: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      });

      createChart('rbCashflowChart', {
        type: 'doughnut',
        data: {
          labels: cashflowLabels,
          datasets: [
            {
              data: cashflowValues,
              backgroundColor: ['#10b981', '#f59e0b'],
              borderWidth: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      });
    })();
  </script>
</x-layouts.admin>

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <style>
    @page { size: A4 landscape; margin: 14mm; }

    @font-face {
      font-family: "THSarabunNew";
      src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format("truetype");
      font-weight: normal;
      font-style: normal;
    }

    @font-face {
      font-family: "THSarabunNew";
      src: url("{{ storage_path('fonts/THSarabunNew Bold.ttf') }}") format("truetype");
      font-weight: bold;
      font-style: normal;
    }

    * { box-sizing: border-box; }

    body {
      font-family: "THSarabunNew", DejaVu Sans, sans-serif;
      color: #123524;
      font-size: 18px;
      line-height: 1.25;
    }

    h1, h2, h3 {
      margin: 0;
      line-height: 1.1;
    }

    .title {
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 4px;
    }

    .subtitle {
      color: #365947;
      margin-bottom: 10px;
    }

    .chips {
      margin-bottom: 12px;
    }

    .chip {
      display: inline-block;
      margin: 0 6px 6px 0;
      padding: 4px 10px;
      border: 1px solid #b9dcc8;
      border-radius: 999px;
      background: #eef8f2;
      font-size: 16px;
    }

    .grid {
      width: 100%;
      border-collapse: separate;
      border-spacing: 10px;
      margin: 0 -10px 8px;
    }

    .metric {
      width: 25%;
      border: 1px solid #d7f0e3;
      border-radius: 10px;
      background: #f8fdf9;
      padding: 10px 12px;
      vertical-align: top;
    }

    .metric-label {
      color: #4a6c5a;
      font-size: 15px;
    }

    .metric-value {
      font-size: 26px;
      font-weight: bold;
      margin: 4px 0;
    }

    .metric-note {
      color: #5f6b66;
      font-size: 15px;
    }

    .section {
      margin-top: 10px;
      page-break-inside: avoid;
    }

    .section-title {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 6px;
    }

    table.report {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }

    table.report th,
    table.report td {
      border: 1px solid #cde5d7;
      padding: 6px 8px;
      vertical-align: top;
    }

    table.report th {
      background: #eef8f2;
      text-align: left;
      font-weight: bold;
    }

    .text-right {
      text-align: right;
    }

    .muted {
      color: #5f6b66;
      font-size: 15px;
    }

    .page-break {
      page-break-before: always;
    }
  </style>
</head>
<body>
  <div class="title">สรุปรายงานธนาคารวัสดุรีไซเคิล</div>
  <div class="subtitle">
    ช่วงข้อมูล {{ $periodLabel }} · สิทธิ์ผู้ใช้ {{ $isPrivileged ? 'staff/admin' : 'member' }}
  </div>

  @if($filterSummary !== [])
    <div class="chips">
      @foreach($filterSummary as $item)
        <span class="chip">{{ $item }}</span>
      @endforeach
    </div>
  @endif

  <table class="grid">
    <tr>
      @if($isPrivileged)
        <td class="metric">
          <div class="metric-label">ครัวเรือนทั้งหมด</div>
          <div class="metric-value">{{ number_format($householdSummary['totalHouseholds']) }}</div>
          <div class="metric-note">ใช้งาน {{ number_format($householdSummary['activeHouseholds']) }} · รออนุมัติ {{ number_format($householdSummary['pendingHouseholds']) }}</div>
        </td>
        <td class="metric">
          <div class="metric-label">สมาชิกในครัวเรือน</div>
          <div class="metric-value">{{ number_format($householdSummary['memberCount']) }}</div>
          <div class="metric-note">ค่าเฉลี่ยยอดคงเหลือ {{ number_format($householdSummary['averageBalance'], 2) }} บาท</div>
        </td>
        <td class="metric">
          <div class="metric-label">ยอดรับซื้อรวม</div>
          <div class="metric-value">{{ number_format($transactionSummary['depositAmount'], 2) }}</div>
          <div class="metric-note">น้ำหนักรวม {{ number_format($transactionSummary['depositWeight'], 2) }} กก.</div>
        </td>
        <td class="metric">
          <div class="metric-label">ยอดถอนรวม</div>
          <div class="metric-value">{{ number_format($transactionSummary['withdrawAmount'], 2) }}</div>
          <div class="metric-note">จำนวนรายการ {{ number_format($transactionSummary['transactionCount']) }}</div>
        </td>
      @else
        <td class="metric">
          <div class="metric-label">เลขบัญชี</div>
          <div class="metric-value">{{ $focusHousehold?->account_no ?? '-' }}</div>
          <div class="metric-note">{{ $focusHousehold?->contact_person ?? '-' }}</div>
        </td>
        <td class="metric">
          <div class="metric-label">ยอดคงเหลือปัจจุบัน</div>
          <div class="metric-value">{{ number_format((float) ($focusHousehold?->total_balance ?? 0), 2) }}</div>
          <div class="metric-note">สมาชิกในครัวเรือน {{ number_format($householdSummary['memberCount']) }} คน</div>
        </td>
        <td class="metric">
          <div class="metric-label">ยอดรับซื้อสะสม</div>
          <div class="metric-value">{{ number_format($transactionSummary['depositAmount'], 2) }}</div>
          <div class="metric-note">จำนวน {{ number_format($transactionSummary['depositCount']) }} รายการ</div>
        </td>
        <td class="metric">
          <div class="metric-label">ยอดถอนสะสม</div>
          <div class="metric-value">{{ number_format($transactionSummary['withdrawAmount'], 2) }}</div>
          <div class="metric-note">น้ำหนักรวม {{ number_format($transactionSummary['depositWeight'], 2) }} กก.</div>
        </td>
      @endif
    </tr>
  </table>

  @if(! $isPrivileged && $focusHousehold)
    <div class="section">
      <div class="section-title">ข้อมูลครัวเรือน</div>
      <table class="report">
        <thead>
          <tr>
            <th style="width: 20%;">เลขบัญชี</th>
            <th style="width: 24%;">ผู้ติดต่อ</th>
            <th style="width: 24%;">ชุมชน</th>
            <th style="width: 16%;">ยอดคงเหลือ</th>
            <th style="width: 16%;">สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $focusHousehold->account_no }}</td>
            <td>{{ $focusHousehold->contact_person }}</td>
            <td>{{ $focusHousehold->community?->community_name ?? '-' }}</td>
            <td class="text-right">{{ number_format((float) $focusHousehold->total_balance, 2) }}</td>
            <td>{{ $focusHousehold->active_status }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  @endif

  <div class="section">
    <div class="section-title">{{ $isPrivileged ? 'วัสดุที่รับซื้อมากที่สุด' : 'วัสดุที่ฉันขายได้มากที่สุด' }}</div>
    <table class="report">
      <thead>
        <tr>
          <th>วัสดุ</th>
          <th style="width: 20%;">หมวด</th>
          <th style="width: 12%;">จำนวนรายการ</th>
          <th style="width: 14%;">น้ำหนัก</th>
          <th style="width: 14%;">มูลค่า</th>
        </tr>
      </thead>
      <tbody>
        @forelse($topMaterials as $material)
          <tr>
            <td>{{ $material->material_name }}</td>
            <td>{{ $material->category_name ?? 'ไม่ระบุหมวด' }}</td>
            <td class="text-right">{{ number_format((int) $material->transaction_count) }}</td>
            <td class="text-right">{{ number_format((float) $material->total_weight, 2) }} {{ $material->unit }}</td>
            <td class="text-right">{{ number_format((float) $material->total_amount, 2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="muted">ไม่มีข้อมูลวัสดุในช่วงที่เลือก</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-title">สรุปตามเดือน</div>
    <table class="report">
      <thead>
        <tr>
          <th style="width: 16%;">เดือน</th>
          <th style="width: 18%;">จำนวนรายการ</th>
          <th style="width: 22%;">ยอดรับซื้อ</th>
          <th style="width: 22%;">ยอดถอน</th>
          <th style="width: 22%;">น้ำหนักรับซื้อ</th>
        </tr>
      </thead>
      <tbody>
        @forelse($monthlySummary as $month)
          <tr>
            <td>{{ $month->month_label }}</td>
            <td class="text-right">{{ number_format((int) $month->transaction_count) }}</td>
            <td class="text-right">{{ number_format((float) $month->deposit_amount, 2) }}</td>
            <td class="text-right">{{ number_format((float) $month->withdraw_amount, 2) }}</td>
            <td class="text-right">{{ number_format((float) $month->deposit_weight, 2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="muted">ไม่มีข้อมูลรายเดือนในช่วงที่เลือก</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($isPrivileged)
    <div class="section page-break">
      <div class="section-title">ครัวเรือนที่มีมูลค่ารับซื้อสูงสุด</div>
      <table class="report">
        <thead>
          <tr>
            <th>ครัวเรือน</th>
            <th style="width: 18%;">ชุมชน</th>
            <th style="width: 14%;">รับซื้อ</th>
            <th style="width: 14%;">ถอน</th>
            <th style="width: 14%;">ยอดคงเหลือ</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topHouseholds as $household)
            <tr>
              <td>{{ $household->account_no }} - {{ $household->contact_person }}</td>
              <td>{{ $household->community_name ?? '-' }}</td>
              <td class="text-right">{{ number_format((float) $household->deposit_amount, 2) }}</td>
              <td class="text-right">{{ number_format((float) $household->withdraw_amount, 2) }}</td>
              <td class="text-right">{{ number_format((float) $household->total_balance, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="muted">ไม่มีข้อมูลครัวเรือนในช่วงที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section">
      <div class="section-title">สรุปตามชุมชน</div>
      <table class="report">
        <thead>
          <tr>
            <th>ชุมชน</th>
            <th style="width: 10%;">ครัวเรือน</th>
            <th style="width: 10%;">สมาชิก</th>
            <th style="width: 14%;">รับซื้อ</th>
            <th style="width: 14%;">ถอน</th>
            <th style="width: 14%;">น้ำหนัก</th>
            <th style="width: 14%;">ยอดคงเหลือ</th>
          </tr>
        </thead>
        <tbody>
          @forelse($communityStats as $community)
            <tr>
              <td>{{ $community->community_id }} - {{ $community->community_name }}</td>
              <td class="text-right">{{ number_format((int) $community->household_count) }}</td>
              <td class="text-right">{{ number_format((int) $community->member_count) }}</td>
              <td class="text-right">{{ number_format((float) $community->deposit_amount, 2) }}</td>
              <td class="text-right">{{ number_format((float) $community->withdraw_amount, 2) }}</td>
              <td class="text-right">{{ number_format((float) $community->deposit_weight, 2) }}</td>
              <td class="text-right">{{ number_format((float) $community->total_balance, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="muted">ไม่มีข้อมูลชุมชนในช่วงที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @else
    <div class="section">
      <div class="section-title">รายการล่าสุดของฉัน</div>
      <table class="report">
        <thead>
          <tr>
            <th style="width: 16%;">วันที่</th>
            <th style="width: 16%;">ประเภท</th>
            <th style="width: 18%;">น้ำหนัก</th>
            <th style="width: 18%;">จำนวนเงิน</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentTransactions as $transaction)
            <tr>
              <td>{{ optional($transaction->transaction_date)->format('d/m/Y') }}</td>
              <td>{{ $transaction->transaction_type === 'deposit' ? 'ฝาก' : 'ถอน' }}</td>
              <td class="text-right">{{ number_format((float) $transaction->total_weight, 2) }}</td>
              <td class="text-right">{{ number_format((float) $transaction->total_amount, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="muted">ไม่มีรายการล่าสุดในช่วงที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endif
</body>
</html>

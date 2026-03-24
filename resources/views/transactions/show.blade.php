<x-layouts.admin :title="$isDepositSummary ? 'สรุปรายการฝาก/รับซื้อ' : 'รายละเอียดรายการ'">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">{{ $isDepositSummary ? 'Deposit Summary' : 'Transaction Detail' }}</div>
      <h1 class="rb-page-title">
        {{ $isDepositSummary ? 'สรุปรายการฝาก/รับซื้อ' : 'รายละเอียดรายการ' }} #{{ $transaction->transaction_id }}
      </h1>
      <p class="rb-page-subtitle">
        วันที่ {{ $transaction->transaction_date }} |
        ครัวเรือน {{ $transaction->household?->account_no }} - {{ $transaction->household?->contact_person }}
      </p>
    </div>

    <div class="rb-page-actions">
      @if($isDepositSummary)
        <a class="btn btn-outline-secondary" href="{{ route('deposits.create') }}">กลับหน้ารับฝาก</a>
      @else
        <a class="btn btn-outline-secondary" href="{{ route('transactions.index') }}">กลับ</a>
      @endif
      <a class="btn btn-primary" href="{{ route('transactions.receipt', $transaction) }}" target="_blank">
        {{ $transaction->transaction_type === 'withdraw' ? 'ใบถอนเงิน PDF' : 'พิมพ์ใบเสร็จ' }}
      </a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">ประเภท</div>
      <div class="rb-stat-value">{{ $transaction->transaction_type === 'deposit' ? 'ฝาก' : 'ถอน' }}</div>
      <div class="rb-stat-meta">
        @if($transaction->transaction_type === 'deposit')
          รายการรับซื้อวัสดุจากครัวเรือน
        @else
          รายการถอนเงินจากยอดคงเหลือ
        @endif
      </div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">น้ำหนักรวม</div>
      <div class="rb-stat-value">{{ number_format((float) $transaction->total_weight, 2) }}</div>
      <div class="rb-stat-meta">กิโลกรัมรวมของรายการนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">จำนวนเงินรวม</div>
      <div class="rb-stat-value">{{ number_format((float) $transaction->total_amount, 2) }}</div>
      <div class="rb-stat-meta">มูลค่ารวมที่บันทึกในธุรกรรมนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดคงเหลือปัจจุบัน</div>
      <div class="rb-stat-value">{{ number_format((float) ($transaction->household?->total_balance ?? 0), 2) }}</div>
      <div class="rb-stat-meta">ยอดล่าสุดของครัวเรือนเจ้าของรายการ</div>
    </div>
  </div>

  @if($transaction->transaction_type === 'withdraw')
    <div class="alert alert-info">
      รายการถอนจะไม่มีรายละเอียดวัสดุย่อย เพราะบันทึกเป็นยอดถอนเงินโดยตรง
    </div>
  @else
    <div class="rb-surface p-3 p-lg-4">
      <div class="rb-section-head">
        <div>
          <h2 class="rb-card-title">รายละเอียดวัสดุ</h2>
          <p class="rb-card-subtitle">แสดงน้ำหนัก ราคา/หน่วย และจำนวนเงินของวัสดุแต่ละรายการ</p>
        </div>
        <span class="rb-chip">{{ $transaction->details->count() }} รายการ</span>
      </div>

      <div class="table-responsive">
        <table class="table table-striped align-middle" data-sortable-table>
          <thead>
            <tr>
              <th>วัสดุ</th>
              <th style="width:140px;" class="text-end" data-sort-type="number">น้ำหนัก</th>
              <th style="width:140px;" class="text-end" data-sort-type="number">ราคา/หน่วย</th>
              <th style="width:160px;" class="text-end" data-sort-type="number">จำนวนเงิน</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transaction->details as $d)
              <tr>
                <td>{{ $d->material?->material_name }}</td>
                <td class="text-end">{{ number_format((float) $d->weight, 2) }}</td>
                <td class="text-end">{{ number_format((float) $d->price_per_unit, 2) }}</td>
                <td class="text-end">{{ number_format((float) $d->amount, 2) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th class="text-end" colspan="3">รวม</th>
              <th class="text-end">{{ number_format((float) $transaction->total_amount, 2) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  @endif
</x-layouts.admin>

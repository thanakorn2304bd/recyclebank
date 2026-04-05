<x-layouts.admin title="Statement ครัวเรือน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Household Statement</div>
      <h1 class="rb-page-title">Statement ครัวเรือน</h1>
      <p class="rb-page-subtitle">
        {{ $household->account_no }} - {{ $household->contact_person }}
        พร้อมยอดคงเหลือปัจจุบัน {{ number_format((float) $household->total_balance, 2) }} บาท
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('transactions.index') }}">กลับรายการทั้งหมด</a>
      @if($isPrivileged)
        <a class="btn btn-outline-dark" href="{{ route('deposits.create') }}">+ ฝาก</a>
        <a class="btn btn-primary" href="{{ route('withdraws.create') }}">+ ถอน</a>
      @endif
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดคงเหลือ</div>
      <div class="rb-stat-value">{{ number_format((float) $household->total_balance, 2) }}</div>
      <div class="rb-stat-meta">ยอดคงเหลือปัจจุบันของบัญชีนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ธุรกรรมในหน้า</div>
      <div class="rb-stat-value">{{ number_format($txs->count()) }}</div>
      <div class="rb-stat-meta">จากทั้งหมด {{ number_format($txs->total()) }} รายการ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ฝาก</div>
      <div class="rb-stat-value">{{ number_format($depositCount) }}</div>
      <div class="rb-stat-meta">จำนวนรายการฝากในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ถอน</div>
      <div class="rb-stat-value">{{ number_format($withdrawCount) }}</div>
      <div class="rb-stat-meta">จำนวนรายการถอนในหน้าปัจจุบัน</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">กรองช่วงวันที่</h2>
        <p class="rb-card-subtitle">ใช้ดู statement เป็นช่วงเวลา หรือโฟกัสเฉพาะรายการที่เกี่ยวข้อง</p>
      </div>
      @if($from || $to)
        <span class="rb-chip">มีตัวกรองวันที่</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-3">
        <label class="form-label">จากวันที่</label>
        <input type="date" class="form-control" name="from" value="{{ $from }}">
      </div>
      <div class="col-lg-3">
        <label class="form-label">ถึงวันที่</label>
        <input type="date" class="form-control" name="to" value="{{ $to }}">
      </div>
      <div class="col-lg-6 d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100">กรอง</button>
        <a class="btn btn-outline-secondary w-100" href="{{ route('transactions.household', $household) }}">ล้าง</a>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการของครัวเรือนนี้</h2>
        <p class="rb-card-subtitle">กดเรียงหัวตารางได้ และเปิดดูรายละเอียดรายการแต่ละครั้งต่อได้ทันที</p>
      </div>
      <span class="rb-chip">
        {{ number_format($txs->firstItem() ?? 0) }}-{{ number_format($txs->lastItem() ?? 0) }}
        / {{ number_format($txs->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:90px;" data-sort-type="number">#</th>
            <th style="width:140px;" data-sort-type="date">วันที่</th>
            <th style="width:110px;">ประเภท</th>
            <th style="width:140px;" class="text-end" data-sort-type="number">น้ำหนัก</th>
            <th style="width:160px;" class="text-end" data-sort-type="number">จำนวนเงิน</th>
            <th style="width:140px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($txs as $t)
            <tr>
              <td>{{ $t->transaction_id }}</td>
              <td>{{ $t->transaction_date?->format('d/m/Y') ?? '-' }}</td>
              <td>
                @if($t->transaction_type === 'deposit')
                  <span class="badge bg-success">ฝาก</span>
                @else
                  <span class="badge bg-warning text-dark">ถอน</span>
                @endif
                @if($t->is_reversal)
                  <div class="text-danger small mt-1">รายการชดเชยของ #{{ $t->reversal_of_transaction_id }}</div>
                @elseif($t->reversed_at)
                  <div class="text-warning small mt-1">ถูกกลับรายการแล้วโดย #{{ $t->reversalTransaction?->transaction_id ?? '-' }}</div>
                @endif
              </td>
              <td class="text-end">{{ number_format((float) $t->total_weight, 2) }}</td>
              <td class="text-end {{ (float) $t->total_amount < 0 ? 'text-danger' : '' }}">{{ number_format((float) $t->total_amount, 2) }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('transactions.show', $t) }}">ดูรายละเอียด</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">ไม่พบรายการของครัวเรือนนี้ในช่วงวันที่ที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $txs->links() }}
  </div>
</x-layouts.admin>

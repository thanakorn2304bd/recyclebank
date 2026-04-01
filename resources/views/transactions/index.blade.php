<x-layouts.admin title="ประวัติรายการ">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Transaction History</div>
      <h1 class="rb-page-title">ประวัติรายการ</h1>
      <p class="rb-page-subtitle">
        ดูภาพรวมธุรกรรมทั้งหมด กรองตามครัวเรือน ช่วงวันที่ หรือประเภท และเปิดดูรายละเอียดแต่ละรายการได้ทันที
      </p>
    </div>
    <div class="rb-page-actions">
      @if($isPrivileged)
        <a class="btn btn-outline-dark" href="{{ route('deposits.create') }}">+ ฝาก/รับซื้อ</a>
        <a class="btn btn-primary" href="{{ route('withdraws.create') }}">+ ถอน</a>
      @endif
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">รายการทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($txs->total()) }}</div>
      <div class="rb-stat-meta">แสดง {{ number_format($txs->count()) }} รายการในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ฝาก/รับซื้อ</div>
      <div class="rb-stat-value">{{ number_format($depositCount) }}</div>
      <div class="rb-stat-meta">จำนวนรายการฝากในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ถอน</div>
      <div class="rb-stat-value">{{ number_format($withdrawCount) }}</div>
      <div class="rb-stat-meta">จำนวนรายการถอนในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดรวมในหน้า</div>
      <div class="rb-stat-value">{{ number_format((float) $pageAmount, 2) }}</div>
      <div class="rb-stat-meta">น้ำหนักรวม {{ number_format((float) $pageWeight, 2) }} กก.</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">กรองรายการ</h2>
        <p class="rb-card-subtitle">เลือกครัวเรือน ประเภท หรือช่วงวันที่ เพื่อดูรายการที่เกี่ยวข้องได้เร็วขึ้น</p>
      </div>
      @if($householdId || $type || $from || $to)
        <span class="rb-chip">มีตัวกรองที่กำลังใช้งาน</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label">ครัวเรือน</label>
        <select class="form-select" name="household_id">
          <option value="">ทั้งหมด</option>
          @foreach($households as $h)
            <option value="{{ $h->household_id }}" @selected((string) $householdId === (string) $h->household_id)>
              {{ $h->account_no }} - {{ $h->contact_person }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">ประเภท</label>
        <select class="form-select" name="type">
          <option value="">ทั้งหมด</option>
          <option value="deposit" @selected($type === 'deposit')>ฝาก</option>
          <option value="withdraw" @selected($type === 'withdraw')>ถอน</option>
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">จากวันที่</label>
        <input type="date" class="form-control" name="from" value="{{ $from }}">
      </div>
      <div class="col-lg-2">
        <label class="form-label">ถึงวันที่</label>
        <input type="date" class="form-control" name="to" value="{{ $to }}">
      </div>
      <div class="col-lg-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100">กรอง</button>
        <a class="btn btn-outline-secondary w-100" href="{{ route('transactions.index') }}">ล้าง</a>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการธุรกรรม</h2>
        <p class="rb-card-subtitle">คลิกหัวตารางเพื่อเรียงลำดับข้อมูล และเข้าไปดู statement ของแต่ละครัวเรือนได้</p>
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
            <th>ครัวเรือน</th>
            <th style="width:130px;" class="text-end" data-sort-type="number">น้ำหนัก</th>
            <th style="width:140px;" class="text-end" data-sort-type="number">จำนวนเงิน</th>
            <th style="width:140px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($txs as $t)
            <tr>
              <td>{{ $t->transaction_id }}</td>
              <td>{{ $t->transaction_date }}</td>
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
              <td>
                {{ $t->household?->account_no }} - {{ $t->household?->contact_person }}
                <div class="text-muted small mt-1">
                  <a href="{{ route('transactions.household', $t->household_id) }}">ดู statement</a>
                </div>
              </td>
              <td class="text-end">{{ number_format((float) $t->total_weight, 2) }}</td>
              <td class="text-end {{ (float) $t->total_amount < 0 ? 'text-danger' : '' }}">{{ number_format((float) $t->total_amount, 2) }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('transactions.show', $t) }}">ดูรายละเอียด</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">ไม่พบประวัติรายการตามตัวกรองที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $txs->links() }}
  </div>
</x-layouts.admin>

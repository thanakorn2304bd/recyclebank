<x-layouts.admin title="ประวัติรายการ">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ประวัติรายการ (ทั้งหมด)</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-dark" href="{{ route('deposits.create') }}">+ ฝาก/รับซื้อ</a>
      <a class="btn btn-outline-dark" href="{{ route('withdraws.create') }}">+ ถอน</a>
    </div>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-3">
      <label class="form-label">ครัวเรือน</label>
      <select class="form-select" name="household_id">
        <option value="">ทั้งหมด</option>
        @foreach($households as $h)
          <option value="{{ $h->household_id }}" @selected((string)$householdId === (string)$h->household_id)>
            {{ $h->account_no }} - {{ $h->contact_person }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">ประเภท</label>
      <select class="form-select" name="type">
        <option value="">ทั้งหมด</option>
        <option value="deposit" @selected($type==='deposit')>ฝาก</option>
        <option value="withdraw" @selected($type==='withdraw')>ถอน</option>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">จากวันที่</label>
      <input type="date" class="form-control" name="from" value="{{ $from }}">
    </div>

    <div class="col-md-2">
      <label class="form-label">ถึงวันที่</label>
      <input type="date" class="form-control" name="to" value="{{ $to }}">
    </div>

    <div class="col-md-3 d-flex align-items-end gap-2">
      <button class="btn btn-outline-primary w-100">กรอง</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('transactions.index') }}">ล้าง</a>
    </div>
  </form>

  <table class="table table-striped bg-white">
    <thead>
      <tr>
        <th style="width:90px;">#</th>
        <th style="width:140px;">วันที่</th>
        <th style="width:110px;">ประเภท</th>
        <th>ครัวเรือน</th>
        <th style="width:130px;" class="text-end">น้ำหนัก</th>
        <th style="width:140px;" class="text-end">จำนวนเงิน</th>
        <th style="width:140px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($txs as $t)
        <tr>
          <td>{{ $t->transaction_id }}</td>
          <td>{{ $t->transaction_date }}</td>
          <td>
            @if($t->transaction_type === 'deposit')
              <span class="badge bg-success">ฝาก</span>
            @else
              <span class="badge bg-warning text-dark">ถอน</span>
            @endif
          </td>
          <td>
            {{ $t->household?->account_no }} - {{ $t->household?->contact_person }}
            <div class="text-muted small">
              <a href="{{ route('transactions.household', $t->household_id) }}">ดู statement</a>
            </div>
          </td>
          <td class="text-end">{{ number_format((float)$t->total_weight, 2) }}</td>
          <td class="text-end">{{ number_format((float)$t->total_amount, 2) }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('transactions.show', $t) }}">ดูรายละเอียด</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $txs->links() }}
</x-layouts.admin>

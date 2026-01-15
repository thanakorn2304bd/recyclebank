<x-layouts.admin title="Statement ครัวเรือน">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Statement ครัวเรือน</h3>
      <div class="text-muted">
        {{ $household->account_no }} - {{ $household->contact_person }}
        | ยอดคงเหลือ: <b>{{ number_format((float)$household->total_balance,2) }}</b>
      </div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('transactions.index') }}">กลับรายการทั้งหมด</a>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-3">
      <label class="form-label">จากวันที่</label>
      <input type="date" class="form-control" name="from" value="{{ $from }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">ถึงวันที่</label>
      <input type="date" class="form-control" name="to" value="{{ $to }}">
    </div>
    <div class="col-md-6 d-flex align-items-end gap-2">
      <button class="btn btn-outline-primary w-100">กรอง</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('transactions.household', $household) }}">ล้าง</a>
      <a class="btn btn-outline-dark w-100" href="{{ route('deposits.create') }}">+ ฝาก</a>
      <a class="btn btn-outline-dark w-100" href="{{ route('withdraws.create') }}">+ ถอน</a>
    </div>
  </form>

  <table class="table table-striped bg-white">
    <thead>
      <tr>
        <th style="width:90px;">#</th>
        <th style="width:140px;">วันที่</th>
        <th style="width:110px;">ประเภท</th>
        <th style="width:140px;" class="text-end">น้ำหนัก</th>
        <th style="width:160px;" class="text-end">จำนวนเงิน</th>
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

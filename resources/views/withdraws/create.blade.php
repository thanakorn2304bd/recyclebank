<x-layouts.admin title="ถอน (Withdraw)">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ถอนเงิน (Withdraw)</h3>
    <a class="btn btn-outline-dark" href="{{ route('deposits.create') }}">ไปหน้า “ฝาก/รับซื้อ”</a>
  </div>

  <form method="POST" action="{{ route('withdraws.store') }}" class="bg-white p-3 rounded">
    @csrf

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">บัญชีครัวเรือน (account_no)</label>
        <select class="form-select" name="household_id" required>
          <option value="">-- เลือกครัวเรือน --</option>
          @foreach($households as $h)
            <option value="{{ $h->household_id }}" @selected(old('household_id') == $h->household_id)>
              {{ $h->account_no }} - {{ $h->contact_person }} (คงเหลือ {{ number_format((float)$h->total_balance,2) }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">วันที่ทำรายการ</label>
        <input type="date" class="form-control" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">จำนวนเงินที่ถอน</label>
        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
      </div>
    </div>

    <button class="btn btn-warning mt-3">บันทึกถอน</button>
    <a class="btn btn-secondary mt-3" href="{{ route('materials.index') }}">กลับหน้า “วัสดุ”</a>
  </form>

  
</x-layouts.admin>

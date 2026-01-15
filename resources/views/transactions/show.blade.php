<x-layouts.admin title="รายละเอียดรายการ">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">รายละเอียดรายการ #{{ $transaction->transaction_id }}</h3>
      <div class="text-muted">
        วันที่: {{ $transaction->transaction_date }}
        | ประเภท:
        @if($transaction->transaction_type === 'deposit')
          <span class="badge bg-success">ฝาก</span>
        @else
          <span class="badge bg-warning text-dark">ถอน</span>
        @endif
      </div>
      <div class="text-muted">
        ครัวเรือน: {{ $transaction->household?->account_no }} - {{ $transaction->household?->contact_person }}
      </div>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="{{ route('transactions.index') }}">กลับ</a>
      <a class="btn btn-outline-primary" href="#" onclick="window.print(); return false;">พิมพ์</a>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="bg-white p-3 rounded border">
        <div class="text-muted">น้ำหนักรวม</div>
        <div class="fs-4">{{ number_format((float)$transaction->total_weight, 2) }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="bg-white p-3 rounded border">
        <div class="text-muted">จำนวนเงินรวม</div>
        <div class="fs-4">{{ number_format((float)$transaction->total_amount, 2) }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="bg-white p-3 rounded border">
        <div class="text-muted">ยอดคงเหลือปัจจุบัน</div>
        <div class="fs-4">{{ number_format((float)($transaction->household?->total_balance ?? 0), 2) }}</div>
      </div>
    </div>
  </div>

  @if($transaction->transaction_type === 'withdraw')
    <div class="alert alert-info">
      รายการถอน ไม่มีรายละเอียดวัสดุ (transaction_detail)
    </div>
  @else
    <table class="table table-striped bg-white">
      <thead>
        <tr>
          <th>วัสดุ</th>
          <th style="width:140px;" class="text-end">น้ำหนัก</th>
          <th style="width:140px;" class="text-end">ราคา/หน่วย</th>
          <th style="width:160px;" class="text-end">จำนวนเงิน</th>
        </tr>
      </thead>
      <tbody>
        @foreach($transaction->details as $d)
          <tr>
            <td>{{ $d->material?->material_name }}</td>
            <td class="text-end">{{ number_format((float)$d->weight, 2) }}</td>
            <td class="text-end">{{ number_format((float)$d->price_per_unit, 2) }}</td>
            <td class="text-end">{{ number_format((float)$d->amount, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th class="text-end" colspan="3">รวม</th>
          <th class="text-end">{{ number_format((float)$transaction->total_amount, 2) }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
</x-layouts.admin>

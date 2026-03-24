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

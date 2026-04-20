@php
  $statusLabels = [
    'pending' => 'รออนุมัติ',
    'approved' => 'อนุมัติแล้ว',
    'rejected' => 'ไม่อนุมัติ',
    'cancelled' => 'ยกเลิกแล้ว',
  ];
  $statusClasses = [
    'pending' => 'bg-warning text-dark',
    'approved' => 'bg-success',
    'rejected' => 'bg-danger',
    'cancelled' => 'bg-secondary',
  ];
@endphp

<x-layouts.admin :title="$isPrivileged ? 'คำขอถอน' : 'คำขอถอนเงิน'">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">{{ $isPrivileged ? 'Withdraw Request Queue' : 'Member Withdrawal Request' }}</div>
      <h1 class="rb-page-title">{{ $isPrivileged ? 'คำขอถอน' : 'คำขอถอนเงิน' }}</h1>
      <p class="rb-page-subtitle">
        @if($isPrivileged)
          ติดตามคำขอถอนที่สมาชิกหรือเจ้าหน้าที่บันทึกไว้ ตรวจสอบแบบฟอร์ม และอนุมัติให้กลายเป็นรายการถอนจริงเมื่อเอกสารครบถ้วน
        @else
          ยื่นคำขอถอนเงินของครัวเรือน ติดตามสถานะ และพิมพ์แบบฟอร์มเพื่อยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน
        @endif
      </p>
    </div>
    <div class="rb-page-actions">
      @if($isPrivileged)
        <a class="btn btn-outline-secondary" href="{{ route('withdraws.create') }}">สร้างคำขอจากหน้าถอน</a>
      @else
        <a class="btn btn-primary" href="{{ route('withdraw-requests.create') }}">+ ยื่นคำขอถอน</a>
      @endif
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">คำขอทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['total']) }}</div>
      <div class="rb-stat-meta">รายการตามเงื่อนไขที่กำลังเปิดดู</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">รออนุมัติ</div>
      <div class="rb-stat-value">{{ number_format($summary['pending']) }}</div>
      <div class="rb-stat-meta">คำขอที่ยังต้องติดตามต่อ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">อนุมัติแล้ว</div>
      <div class="rb-stat-value">{{ number_format($summary['approved']) }}</div>
      <div class="rb-stat-meta">คำขอที่เปลี่ยนเป็นรายการถอนจริงแล้ว</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ไม่อนุมัติ</div>
      <div class="rb-stat-value">{{ number_format($summary['rejected']) }}</div>
      <div class="rb-stat-meta">คำขอที่เจ้าหน้าที่บันทึกผลไว้แล้ว</div>
    </div>
  </div>

  @if(! $isPrivileged && $memberHousehold)
    <div class="alert alert-info">
      บัญชี {{ $memberHousehold->account_no }} - {{ $memberHousehold->contact_person }}
      มียอดคงเหลือปัจจุบัน {{ number_format((float) $memberHousehold->total_balance, 2) }} บาท
      เมื่อยื่นคำขอแล้ว กรุณาพิมพ์แบบฟอร์มไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน
    </div>
  @endif

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">{{ $isPrivileged ? 'ค้นหาและกรองคำขอถอน' : 'ค้นหาคำขอของฉัน' }}</h2>
        <p class="rb-card-subtitle">
          {{ $isPrivileged
            ? 'ค้นจากเลขคำขอ เลขบัญชี ชื่อผู้ติดต่อ บ้านเลขที่ หรือหมายเหตุของคำขอ แล้วกรองตามสถานะได้'
            : 'ค้นจากเลขคำขอหรือหมายเหตุของคำขอ และกรองตามสถานะได้' }}
        </p>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-8">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="{{ $isPrivileged ? 'เช่น WR-202604..., ACC0001, สมชาย, 99/1' : 'เช่น WR-202604... หรือ หมายเหตุคำขอ' }}">
      </div>
      <div class="col-lg-4">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="status">
          <option value="">ทุกสถานะ</option>
          @foreach($statusLabels as $statusKey => $statusLabel)
            <option value="{{ $statusKey }}" @selected($status === $statusKey)>{{ $statusLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('withdraw-requests.index') }}">ล้าง</a>
        <button class="btn btn-primary">ค้นหา</button>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">{{ $isPrivileged ? 'ทะเบียนคำขอถอน' : 'รายการคำขอถอนของฉัน' }}</h2>
        <p class="rb-card-subtitle">
          {{ $isPrivileged
            ? 'รายการที่รออนุมัติจะถูกจัดไว้ด้านบน เพื่อให้เจ้าหน้าที่เข้าไปพิจารณาได้เร็วขึ้น'
            : 'รายการที่รออนุมัติจะมีปุ่มพิมพ์แบบฟอร์ม เพื่อให้คุณนำไปยื่นที่เทศบาลพร้อมหลักฐาน' }}
        </p>
      </div>
      <span class="rb-chip">
        {{ number_format($withdrawRequests->firstItem() ?? 0) }}-{{ number_format($withdrawRequests->lastItem() ?? 0) }}
        / {{ number_format($withdrawRequests->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:180px;">เลขคำขอ</th>
            <th style="width:130px;">วันที่ยื่น</th>
            <th style="width:130px;">วันที่ต้องการถอน</th>
            @if($isPrivileged)
              <th>ครัวเรือน</th>
            @else
              <th>หมายเหตุ</th>
            @endif
            <th style="width:140px;" class="text-end">จำนวนเงิน</th>
            <th style="width:150px;">สถานะ</th>
            <th style="width:180px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($withdrawRequests as $item)
            <tr>
              <td class="fw-semibold">{{ $item->request_no }}</td>
              <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
              <td>{{ $item->requested_for_date?->format('d/m/Y') ?? '-' }}</td>
              @if($isPrivileged)
                <td>
                  <div class="fw-semibold">{{ $item->household?->account_no }} - {{ $item->household?->contact_person }}</div>
                  <div class="text-muted small">
                    บ้านเลขที่ {{ $item->household?->house_no ?: '-' }}
                    @if($item->household?->community)
                      · {{ $item->household->community->community_name }}
                    @endif
                  </div>
                </td>
              @else
                <td>{{ $item->request_notes ?: '-' }}</td>
              @endif
              <td class="text-end">{{ number_format((float) $item->requested_amount, 2) }}</td>
              <td>
                <span class="badge {{ $statusClasses[$item->status] ?? 'bg-secondary' }}">
                  {{ $statusLabels[$item->status] ?? $item->status }}
                </span>
                @if($item->status === 'approved' && $item->approvedTransaction)
                  <div class="text-success small mt-1">รายการถอน #{{ $item->approvedTransaction->transaction_id }}</div>
                @elseif($item->status === 'pending' && ! $isPrivileged)
                  <div class="text-muted small mt-1">พิมพ์แบบฟอร์มแล้วนำไปยื่นที่เทศบาล</div>
                @endif
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('withdraw-requests.show', $item) }}">
                  {{ $item->status === 'pending' && $isPrivileged ? 'พิจารณา' : 'ดูรายละเอียด' }}
                </a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('withdraw-requests.form', $item) }}" target="_blank">พิมพ์แบบฟอร์ม</a>
                @if(! $isPrivileged && $item->status === 'pending')
                  <form method="POST" action="{{ route('withdraw-requests.cancel', $item) }}" class="d-inline" onsubmit="return confirm('ยืนยันการยกเลิกคำขอถอน {{ $item->request_no }} ใช่หรือไม่?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger">ยกเลิกคำขอ</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ $isPrivileged ? 7 : 7 }}" class="text-center text-muted py-4">
                {{ $isPrivileged ? 'ยังไม่มีคำขอถอนตามเงื่อนไขที่เลือก' : 'คุณยังไม่มีคำขอถอนในระบบ' }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $withdrawRequests->links() }}
  </div>
</x-layouts.admin>

@php
  $statusLabels = [
    'pending' => 'รออนุมัติ',
    'approved' => 'อนุมัติแล้ว',
    'rejected' => 'ไม่อนุมัติ',
  ];
  $statusClasses = [
    'pending' => 'bg-warning text-dark',
    'approved' => 'bg-success',
    'rejected' => 'bg-danger',
  ];
@endphp

<x-layouts.admin :title="'คำขอถอน '.$withdrawRequest->request_no">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">{{ $isPrivileged ? 'Withdraw Request Review' : 'Withdraw Request Detail' }}</div>
      <h1 class="rb-page-title">คำขอ {{ $withdrawRequest->request_no }}</h1>
      <p class="rb-page-subtitle">
        {{ $withdrawRequest->household?->account_no }} - {{ $withdrawRequest->household?->contact_person }}
        | สถานะ {{ $statusLabels[$withdrawRequest->status] ?? $withdrawRequest->status }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('withdraw-requests.index') }}">กลับรายการคำขอ</a>
      <a class="btn btn-outline-secondary" href="{{ route('withdraw-requests.form', $withdrawRequest) }}" target="_blank">พิมพ์แบบฟอร์ม</a>
      @if($withdrawRequest->approvedTransaction)
        <a class="btn btn-primary" href="{{ route('transactions.show', $withdrawRequest->approvedTransaction) }}">ดูรายการถอนจริง</a>
      @endif
    </div>
  </div>

  @if($withdrawRequest->status === 'pending')
    <div class="alert alert-warning">
      คำขอนี้ยังรอการอนุมัติ กรุณาพิมพ์แบบฟอร์มคำขอถอนและนำไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน
      @if($isPrivileged)
        ก่อนบันทึกผลการพิจารณาให้ตรวจสอบเอกสารและยอดคงเหลืออีกครั้ง
      @endif
    </div>
  @elseif($withdrawRequest->status === 'approved')
    <div class="alert alert-success">
      คำขอนี้ได้รับการอนุมัติแล้ว
      @if($withdrawRequest->approvedTransaction)
        และสร้างรายการถอนจริง #{{ $withdrawRequest->approvedTransaction->transaction_id }} เรียบร้อย
      @endif
    </div>
  @else
    <div class="alert alert-danger">
      คำขอนี้ไม่ได้รับการอนุมัติ
      @if($withdrawRequest->review_notes)
        เหตุผล: {{ $withdrawRequest->review_notes }}
      @endif
    </div>
  @endif

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">สถานะล่าสุด</div>
      <div class="rb-stat-value rb-stat-value-sm">{{ $statusLabels[$withdrawRequest->status] ?? $withdrawRequest->status }}</div>
      <div class="rb-stat-meta">อัปเดตเมื่อ {{ $withdrawRequest->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">จำนวนเงินที่ขอถอน</div>
      <div class="rb-stat-value">{{ number_format((float) $withdrawRequest->requested_amount, 2) }}</div>
      <div class="rb-stat-meta">วันที่ต้องการถอน {{ $withdrawRequest->requested_for_date?->format('d/m/Y') ?? '-' }}</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">เลขที่บัญชี</div>
      <div class="rb-stat-value rb-stat-value-sm">{{ $withdrawRequest->household?->account_no ?? '-' }}</div>
      <div class="rb-stat-meta">{{ $withdrawRequest->household?->contact_person ?? '-' }}</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">รายการถอนจริง</div>
      <div class="rb-stat-value rb-stat-value-sm">{{ $withdrawRequest->approvedTransaction?->transaction_id ? '#'.$withdrawRequest->approvedTransaction->transaction_id : '-' }}</div>
      <div class="rb-stat-meta">
        {{ $withdrawRequest->reviewed_at?->format('d/m/Y H:i') ?? 'ยังไม่พิจารณา' }}
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">รายละเอียดคำขอ</h2>
            <p class="rb-card-subtitle">ข้อมูลคำขอถอนและข้อมูลครัวเรือนที่อ้างอิง</p>
          </div>
        </div>

        <dl class="row rb-detail-list mb-0">
          <dt class="col-sm-5 mb-3">เลขคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $withdrawRequest->request_no }}</dd>

          <dt class="col-sm-5 mb-3">วันที่ยื่นคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $withdrawRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">วันที่ต้องการถอน</dt>
          <dd class="col-sm-7 mb-3">{{ $withdrawRequest->requested_for_date?->format('d/m/Y') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">จำนวนเงิน</dt>
          <dd class="col-sm-7 mb-3">{{ number_format((float) $withdrawRequest->requested_amount, 2) }} บาท</dd>

          <dt class="col-sm-5 mb-3">ครัวเรือน</dt>
          <dd class="col-sm-7 mb-3">
            {{ $withdrawRequest->household?->account_no ? $withdrawRequest->household->account_no.' - '.$withdrawRequest->household->contact_person : '-' }}
          </dd>

          <dt class="col-sm-5 mb-3">ชุมชน / บ้านเลขที่</dt>
          <dd class="col-sm-7 mb-3">
            {{ $withdrawRequest->household?->community?->community_name ?? '-' }}
            / {{ $withdrawRequest->household?->house_no ?? '-' }}
          </dd>

          <dt class="col-sm-5 mb-3">ผู้ยื่นคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $withdrawRequest->requestedByUser?->username ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">สถานะ</dt>
          <dd class="col-sm-7 mb-3">
            <span class="badge {{ $statusClasses[$withdrawRequest->status] ?? 'bg-secondary' }}">
              {{ $statusLabels[$withdrawRequest->status] ?? $withdrawRequest->status }}
            </span>
          </dd>

          <dt class="col-sm-5 mb-3">หมายเหตุคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $withdrawRequest->request_notes ?: '-' }}</dd>

          <dt class="col-sm-5 mb-0">หมายเหตุการพิจารณา</dt>
          <dd class="col-sm-7 mb-0">{{ $withdrawRequest->review_notes ?: '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="rb-surface p-4">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">{{ $isPrivileged ? 'พิจารณาคำขอ' : 'สิ่งที่ต้องทำต่อ' }}</h2>
            <p class="rb-card-subtitle">
              {{ $isPrivileged
                ? 'ตรวจสอบแบบฟอร์มและหลักฐานประกอบก่อนอนุมัติหรือไม่อนุมัติ'
                : 'พิมพ์แบบฟอร์มและเตรียมหลักฐานให้พร้อมก่อนนำไปยื่นที่เทศบาล' }}
            </p>
          </div>
        </div>

        @if($isPrivileged && $withdrawRequest->status === 'pending')
          <form method="POST" action="{{ route('withdraw-requests.review', $withdrawRequest) }}">
            @csrf
            @method('PATCH')

            <div class="row g-3">
              <div class="col-lg-4">
                <label class="form-label">ผลการพิจารณา</label>
                <select class="form-select" name="decision" required>
                  <option value="approved" @selected(old('decision') === 'approved')>อนุมัติ</option>
                  <option value="rejected" @selected(old('decision') === 'rejected')>ไม่อนุมัติ</option>
                </select>
              </div>
              <div class="col-lg-4">
                <label class="form-label">วันที่ทำรายการถอนจริง</label>
                <input class="form-control" type="date" name="transaction_date" value="{{ old('transaction_date', $withdrawRequest->requested_for_date?->toDateString() ?? now()->toDateString()) }}">
              </div>
              <div class="col-lg-4">
                <label class="form-label">ยอดคงเหลือปัจจุบัน</label>
                <input class="form-control" value="{{ number_format((float) ($withdrawRequest->household?->total_balance ?? 0), 2) }} บาท" readonly>
              </div>
              <div class="col-12">
                <label class="form-label">หมายเหตุการพิจารณา</label>
                <textarea class="form-control" name="review_notes" rows="5" placeholder="ถ้าไม่อนุมัติควรระบุเหตุผลให้ชัดเจน เช่น เอกสารไม่ครบ ยอดคงเหลือไม่พอ หรือยังไม่ยื่นหลักฐาน">{{ old('review_notes') }}</textarea>
              </div>
            </div>

            <div class="rb-info-panel mt-4">
              <div class="fw-semibold text-emerald-900 mb-2">ก่อนกดยืนยัน</div>
              <ul class="mb-0 ps-3">
                <li>ตรวจสอบว่าผู้ยื่นนำแบบฟอร์มคำขอถอนมาพร้อมลายมือชื่อแล้ว</li>
                <li>ตรวจสอบสำเนาทะเบียนบ้านและบัตรประชาชนให้ครบถ้วน</li>
                <li>เมื่ออนุมัติ ระบบจะสร้างรายการถอนจริงและหักยอดคงเหลือทันที</li>
              </ul>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <button class="btn btn-primary">บันทึกผลการพิจารณา</button>
            </div>
          </form>
        @else
          <div class="rb-info-panel">
            <div class="fw-semibold text-emerald-900 mb-2">ขั้นตอนถัดไป</div>
            <ul class="mb-0 ps-3">
              @if($withdrawRequest->status === 'pending')
                <li>พิมพ์แบบฟอร์มคำขอถอนจากปุ่มด้านบน</li>
                <li>ลงลายมือชื่อให้เรียบร้อย</li>
                <li>นำแบบฟอร์มไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน</li>
                <li>รอเจ้าหน้าที่ตรวจสอบและอัปเดตสถานะในระบบ</li>
              @elseif($withdrawRequest->status === 'approved')
                <li>คำขอได้รับการอนุมัติแล้ว</li>
                @if($withdrawRequest->approvedTransaction)
                  <li>สามารถเปิดดูรายละเอียดรายการถอนจริงได้จากปุ่ม “ดูรายการถอนจริง”</li>
                @endif
                <li>หากต้องใช้เอกสารอ้างอิง ให้พิมพ์แบบฟอร์มคำขอฉบับนี้เก็บไว้ประกอบ</li>
              @else
                <li>คำขอไม่ได้รับการอนุมัติ กรุณาตรวจสอบหมายเหตุการพิจารณา</li>
                <li>หากต้องการยื่นใหม่ ให้แก้ข้อมูลหรือเตรียมเอกสารให้ครบก่อนยื่นคำขออีกครั้ง</li>
              @endif
            </ul>
          </div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .rb-stat-value-sm {
      font-size: 1.35rem;
    }
  </style>
</x-layouts.admin>

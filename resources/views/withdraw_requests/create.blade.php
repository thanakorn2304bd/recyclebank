<x-layouts.admin title="ยื่นคำขอถอนเงิน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Member Withdrawal Request</div>
      <h1 class="rb-page-title">ยื่นคำขอถอนเงิน</h1>
      <p class="rb-page-subtitle">
        กรอกจำนวนเงินและวันที่ต้องการถอน จากนั้นพิมพ์แบบฟอร์มไปลงลายมือชื่อและยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('withdraw-requests.index') }}">กลับรายการคำขอ</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">เลขที่บัญชี</div>
      <div class="rb-stat-value rb-stat-value-sm">{{ $household->account_no }}</div>
      <div class="rb-stat-meta">{{ $household->contact_person }}</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดคงเหลือปัจจุบัน</div>
      <div class="rb-stat-value">{{ number_format((float) $household->total_balance, 2) }}</div>
      <div class="rb-stat-meta">ระบบจะตรวจสอบไม่ให้ยื่นเกินยอดคงเหลือปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ชุมชน / บ้านเลขที่</div>
      <div class="rb-stat-value rb-stat-value-sm">{{ $household->community?->community_name ?? '-' }}</div>
      <div class="rb-stat-meta">บ้านเลขที่ {{ $household->house_no }}</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">เบอร์ติดต่อ</div>
      <div class="rb-stat-value rb-stat-value-sm">{{ $household->phone ?: '-' }}</div>
      <div class="rb-stat-meta">ใช้สำหรับติดต่อเมื่อเจ้าหน้าที่พิจารณาคำขอ</div>
    </div>
  </div>

  <div class="alert alert-warning">
    หลังยื่นคำขอแล้ว รายการจะขึ้นสถานะ <strong>รออนุมัติ</strong> และคุณต้องพิมพ์แบบฟอร์มนี้ไปยื่นที่เทศบาล
    พร้อมสำเนาทะเบียนบ้านและบัตรประชาชนก่อนที่เจ้าหน้าที่จะอนุมัติรายการถอนจริง
  </div>

  <form method="POST" action="{{ route('withdraw-requests.store') }}" class="rb-surface p-4" id="withdrawRequestForm">
    @csrf

    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">กรอกข้อมูลคำขอถอน</h2>
        <p class="rb-card-subtitle">ระบุวันที่ต้องการถอนและจำนวนเงินที่ต้องการยื่นคำขอ</p>
      </div>
      <span class="rb-chip">Step 1</span>
    </div>

    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label">วันที่ต้องการถอน</label>
        <input class="form-control" type="date" name="requested_for_date" value="{{ old('requested_for_date', now()->toDateString()) }}" required>
      </div>
      <div class="col-lg-4">
        <label class="form-label">จำนวนเงินที่ต้องการถอน</label>
        <input class="form-control" type="number" step="0.01" min="0.01" max="{{ number_format((float) $household->total_balance, 2, '.', '') }}" name="requested_amount" value="{{ old('requested_amount') }}" required>
      </div>
      <div class="col-lg-4">
        <label class="form-label">ยอดคงเหลืออ้างอิง</label>
        <input class="form-control" value="{{ number_format((float) $household->total_balance, 2) }} บาท" readonly>
      </div>
      <div class="col-12">
        <label class="form-label">หมายเหตุเพิ่มเติม</label>
        <textarea class="form-control" name="request_notes" rows="4" placeholder="เช่น สะดวกมารับเงินช่วงเช้า หรือต้องการให้ติดต่อกลับก่อนนัดหมาย">{{ old('request_notes') }}</textarea>
      </div>
    </div>

    <div class="rb-info-panel mt-4">
      <div class="fw-semibold text-emerald-900 mb-2">สิ่งที่จะเกิดขึ้นหลังจากกดยื่นคำขอ</div>
      <ul class="mb-0 ps-3">
        <li>ระบบจะบันทึกคำขอเป็นสถานะรออนุมัติ</li>
        <li>คุณสามารถพิมพ์แบบฟอร์มคำขอถอนออกมาเพื่อลงลายมือชื่อได้ทันที</li>
        <li>ให้นำแบบฟอร์มนี้ไปยื่นที่เทศบาลพร้อมสำเนาทะเบียนบ้านและบัตรประชาชน</li>
        <li>เมื่อเจ้าหน้าที่อนุมัติแล้ว รายการจะเปลี่ยนเป็นอนุมัติแล้วและถูกบันทึกเป็นรายการถอนจริง</li>
      </ul>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
      <button class="btn btn-primary">ยื่นคำขอถอน</button>
      <a class="btn btn-outline-secondary" href="{{ route('withdraw-requests.index') }}">ยกเลิก</a>
    </div>
  </form>

  <style>
    .rb-stat-value-sm {
      font-size: 1.35rem;
    }
  </style>
</x-layouts.admin>

<x-layouts.admin title="ตั้งรหัสผ่านครัวเรือน">
  @php
    $hasExistingAccount = (bool) $memberAccount;
  @endphp

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h3 class="mb-0">{{ $hasExistingAccount ? 'รีเซ็ตรหัสผ่านครัวเรือน' : 'สร้างบัญชีเข้าใช้งานครัวเรือน' }}</h3>
      <div class="text-muted">ขั้นตอน 2 จาก 2 สำหรับครัวเรือนเลขบัญชี {{ $household->account_no }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-secondary" href="{{ route('households.edit', $household) }}">ย้อนกลับ</a>
      <a class="btn btn-outline-primary" href="{{ route('households.show', $household) }}">ดูรายละเอียดครัวเรือน</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="bg-white p-3 h-100">
        <h5 class="mb-3">ข้อมูลสำหรับเข้าใช้งาน</h5>
        <dl class="row mb-0">
          <dt class="col-5 text-muted mb-2">เลขบัญชี</dt>
          <dd class="col-7 mb-2">{{ $household->account_no }}</dd>

          <dt class="col-5 text-muted mb-2">ชื่อผู้ใช้</dt>
          <dd class="col-7 mb-2">{{ $household->account_no }}</dd>

          <dt class="col-5 text-muted mb-2">ผู้ติดต่อ</dt>
          <dd class="col-7 mb-2">{{ $household->contact_person }}</dd>

          <dt class="col-5 text-muted mb-2">สถานะบัญชี</dt>
          <dd class="col-7 mb-2">
            @if(($memberAccount?->is_active ?? ($household->active_status === 'active')))
              <span class="badge bg-success">เข้าใช้งานได้</span>
            @elseif($household->active_status === 'pending')
              <span class="badge bg-warning text-dark">รออนุมัติ</span>
            @else
              <span class="badge bg-secondary">ปิดการเข้าใช้งาน</span>
            @endif
          </dd>

          <dt class="col-5 text-muted mb-2">เข้าใช้ล่าสุด</dt>
          <dd class="col-7 mb-2">{{ $memberAccount?->last_login?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-5 text-muted mb-0">หมายเหตุ</dt>
          <dd class="col-7 mb-0">ครัวเรือนจะใช้เลขบัญชีนี้เป็นชื่อผู้ใช้เพื่อดูข้อมูลของตัวเอง</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <form method="POST" action="{{ route('households.credentials.store', $household) }}" class="bg-white p-3">
        @csrf

        <div class="mb-3">
          <label class="form-label">ชื่อผู้ใช้</label>
          <input class="form-control" value="{{ $household->account_no }}" readonly>
          <div class="form-text">ระบบใช้เลขบัญชีของครัวเรือนเป็นชื่อผู้ใช้โดยอัตโนมัติ</div>
        </div>

        <div class="mb-3">
          <label class="form-label">รหัสผ่าน</label>
          <input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password">
        </div>

        <div class="mb-3">
          <label class="form-label">ยืนยันรหัสผ่าน</label>
          <input type="password" class="form-control" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </div>

        @if($household->active_status === 'active')
          <div class="alert alert-info mb-3">
            หลังตั้งรหัสผ่านแล้ว ครัวเรือนนี้จะเข้าสู่ระบบและดูข้อมูลของตัวเองได้เฉพาะรายการที่ผูกกับบัญชีนี้
          </div>
        @elseif($household->active_status === 'pending')
          <div class="alert alert-warning mb-3">
            หลังตั้งรหัสผ่านแล้ว บัญชียังอยู่ในสถานะรออนุมัติ และจะเข้าสู่ระบบได้เมื่อเจ้าหน้าที่เปลี่ยนสถานะครัวเรือนเป็นใช้งาน
          </div>
        @else
          <div class="alert alert-secondary mb-3">
            บัญชีนี้ถูกปิดการเข้าใช้งานอยู่ แม้ตั้งรหัสผ่านแล้วก็ยังเข้าสู่ระบบไม่ได้จนกว่าจะเปิดใช้งานครัวเรือนอีกครั้ง
          </div>
        @endif

        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-success">{{ $hasExistingAccount ? 'บันทึกรหัสผ่านใหม่' : 'บันทึกและสร้างบัญชีเข้าใช้' }}</button>
          <a class="btn btn-secondary" href="{{ route('households.show', $household) }}">ข้ามไปก่อน</a>
        </div>
      </form>
    </div>
  </div>
</x-layouts.admin>

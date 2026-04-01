<x-layouts.admin title="ตั้งรหัสผ่านครัวเรือน">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h3 class="mb-0">{{ $pageTitle }}</h3>
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
            <span class="badge {{ $accountStatusBadgeClass }}">{{ $accountStatusLabel }}</span>
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

        <div class="alert {{ $accountHelpAlertClass }} mb-3">
          {{ $accountHelpMessage }}
        </div>

        <div class="form-text mb-3">รหัสผ่านที่เจ้าหน้าที่ตั้งหรือรีเซ็ตให้จะใช้เป็นรหัสชั่วคราว และระบบจะบังคับให้สมาชิกเปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ</div>

        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-success">{{ $submitLabel }}</button>
          <a class="btn btn-secondary" href="{{ route('households.show', $household) }}">ข้ามไปก่อน</a>
        </div>
      </form>
    </div>
  </div>
</x-layouts.admin>

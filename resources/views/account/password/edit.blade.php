<x-layouts.admin title="เปลี่ยนรหัสผ่าน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Account Security</div>
      <h1 class="rb-page-title">เปลี่ยนรหัสผ่าน</h1>
      <p class="rb-page-subtitle">
        อัปเดตรหัสผ่านของบัญชี {{ $user->username }} เพื่อให้เข้าใช้งานต่อได้อย่างปลอดภัย
      </p>
    </div>
    <div class="rb-page-actions">
      @if(! $requiresPasswordReset)
        <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
      @endif
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">สถานะบัญชี</h2>
            <p class="rb-card-subtitle">สรุปสิทธิ์และข้อมูลรหัสผ่านล่าสุดของบัญชีนี้</p>
          </div>
        </div>

        <dl class="row rb-detail-list mb-0">
          <dt class="col-sm-5 mb-3">ชื่อผู้ใช้</dt>
          <dd class="col-sm-7 mb-3">{{ $user->username }}</dd>

          <dt class="col-sm-5 mb-3">สิทธิ์</dt>
          <dd class="col-sm-7 mb-3">{{ $user->role }}</dd>

          <dt class="col-sm-5 mb-3">เปลี่ยนรหัสล่าสุด</dt>
          <dd class="col-sm-7 mb-3">{{ $user->password_changed_at?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">นโยบายตอนนี้</dt>
          <dd class="col-sm-7 mb-3">
            @if($requiresPasswordReset)
              <span class="badge bg-warning text-dark">ต้องเปลี่ยนรหัสก่อนใช้งานต่อ</span>
            @else
              <span class="badge bg-success">ใช้งานได้ตามปกติ</span>
            @endif
          </dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <form method="POST" action="{{ route('account.password.update') }}" class="rb-surface p-4">
        @csrf
        @method('PUT')

        @if($requiresPasswordReset)
          <div class="alert alert-warning">
            บัญชีนี้ถูกตั้งหรือรีเซ็ตรหัสผ่านโดยเจ้าหน้าที่ จึงต้องเปลี่ยนรหัสผ่านใหม่ก่อนเข้าใช้งานส่วนอื่นของระบบ
          </div>
        @else
          <div class="alert alert-info">
            แนะนำให้ตั้งรหัสผ่านที่จดจำได้ง่ายสำหรับคุณ แต่เดายากสำหรับผู้อื่น และไม่ซ้ำกับรหัสผ่านเดิม
          </div>
        @endif

        <div class="mb-3">
          <label class="form-label">รหัสผ่านปัจจุบัน</label>
          <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
        </div>

        <div class="mb-3">
          <label class="form-label">รหัสผ่านใหม่</label>
          <input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password">
          <div class="form-text">อย่างน้อย 8 ตัวอักษร และไม่ควรใช้ข้อมูลเดาง่าย เช่น เบอร์โทรหรือวันเกิด</div>
        </div>

        <div class="mb-3">
          <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
          <input type="password" class="form-control" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-primary">บันทึกรหัสผ่านใหม่</button>
          @if(! $requiresPasswordReset)
            <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">ยกเลิก</a>
          @endif
        </div>
      </form>
    </div>
  </div>
</x-layouts.admin>

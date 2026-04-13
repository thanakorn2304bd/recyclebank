<x-layouts.admin title="Backup ฐานข้อมูล">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Database Backup</div>
      <h1 class="rb-page-title">Backup ฐานข้อมูล</h1>
      <p class="rb-page-subtitle">
        สร้าง ดาวน์โหลด และจัดการ backup ฐานข้อมูลของระบบ ควรสำรองข้อมูลก่อนทำการเปลี่ยนแปลงสำคัญทุกครั้ง
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.activity-logs.index') }}">Activity Log</a>
      <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="rb-surface p-4 mb-4">
    <div class="rb-section-head mb-3">
      <div>
        <h2 class="rb-card-title">สร้าง Backup ใหม่</h2>
        <p class="rb-card-subtitle">ระบบจะส่งออก SQL dump แบบบีบอัด (.sql.gz) และเก็บสูงสุด 30 ไฟล์ล่าสุด — ไฟล์เก่าเกินจะถูกลบอัตโนมัติ</p>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.backup.store') }}"
          onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').textContent = 'กำลังสร้าง backup...'">
      @csrf
      <button type="submit" class="btn btn-primary">
        สร้าง Backup ตอนนี้
      </button>
    </form>
  </div>

  <div class="rb-surface p-4 mb-4 border-warning" style="border-left: 4px solid #ffc107;">
    <div class="rb-section-head mb-3">
      <div>
        <h2 class="rb-card-title">Restore จากไฟล์ Backup</h2>
        <p class="rb-card-subtitle text-danger fw-semibold">⚠️ การ restore จะเขียนทับข้อมูลปัจจุบันทั้งหมด — ควรสร้าง backup ใหม่ก่อนทุกครั้ง</p>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data"
          onsubmit="return confirm('ยืนยันการ Restore?\n\nข้อมูลปัจจุบันทั้งหมดจะถูกเขียนทับด้วยข้อมูลจากไฟล์ที่เลือก\nดำเนินการต่อ?')">
      @csrf
      <div class="d-flex gap-3 align-items-end flex-wrap">
        <div>
          <label class="form-label fw-semibold">เลือกไฟล์ .sql.gz</label>
          <input type="file" name="backup_file" accept=".gz"
                 class="form-control @error('backup_file') is-invalid @enderror"
                 style="width:320px;" required>
          @error('backup_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-warning fw-semibold">
          อัปโหลดและ Restore
        </button>
      </div>
    </form>
  </div>

  <div class="rb-surface p-4">
    <div class="rb-section-head mb-3">
      <div>
        <h2 class="rb-card-title">รายการ Backup</h2>
        <p class="rb-card-subtitle">{{ $backups->count() }} ไฟล์ — คลิก "ดาวน์โหลด" เพื่อบันทึกไว้ที่เครื่องของคุณ</p>
      </div>
    </div>

    @if($backups->isEmpty())
      <div class="text-center py-5 text-muted">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-archive mb-3 d-block mx-auto opacity-25" viewBox="0 0 16 16">
          <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
        </svg>
        <p class="mb-1">ยังไม่มี backup</p>
        <p class="small">กด "สร้าง Backup ตอนนี้" เพื่อเริ่มต้น</p>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ชื่อไฟล์</th>
              <th class="text-end">ขนาด</th>
              <th>วันที่สร้าง</th>
              <th class="text-end">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            @foreach($backups as $backup)
              <tr>
                <td>
                  <span class="font-monospace small">{{ $backup['name'] }}</span>
                </td>
                <td class="text-end text-muted small">{{ $backup['size'] }}</td>
                <td class="text-muted small">{{ $backup['created_at'] }}</td>
                <td class="text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.backup.download', $backup['name']) }}"
                       class="btn btn-sm btn-outline-primary">
                      ดาวน์โหลด
                    </a>
                    <form method="POST"
                          action="{{ route('admin.backup.destroy', $backup['name']) }}"
                          onsubmit="return confirm('ลบ backup นี้?\n{{ $backup['name'] }}')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">ลบ</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</x-layouts.admin>

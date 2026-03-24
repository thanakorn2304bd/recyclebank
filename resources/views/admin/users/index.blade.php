<x-layouts.admin title="บัญชีผู้ใช้ทั้งหมด">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Admin Users</div>
      <h1 class="rb-page-title">บัญชีผู้ใช้ทั้งหมด</h1>
      <p class="rb-page-subtitle">
        รวมบัญชีผู้ใช้ทุกประเภทในระบบไว้ในหน้าเดียว ทั้ง admin, staff และสมาชิก พร้อมสถานะการใช้งาน เวลาเข้าใช้ล่าสุด และจำนวน log ที่เกี่ยวข้อง
      </p>
    </div>
    <div class="rb-page-actions">
      <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createStaffAccountModal">+ เพิ่มบัญชี staff</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">ดูข้อมูลเจ้าหน้าที่</a>
      <a class="btn btn-outline-secondary" href="{{ route('admin.activity-logs.index') }}">ดู Activity Log</a>
      <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">บัญชีทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['total']) }}</div>
      <div class="rb-stat-meta">ผลลัพธ์หลังใช้ตัวกรองปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">เปิดใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($summary['active']) }}</div>
      <div class="rb-stat-meta">บัญชีที่สามารถเข้าสู่ระบบได้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ปิดใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($summary['inactive']) }}</div>
      <div class="rb-stat-meta">บัญชีที่ยังถูกปิดหรือรออนุมัติ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">สมาชิก</div>
      <div class="rb-stat-value">{{ number_format($summary['members']) }}</div>
      <div class="rb-stat-meta">จำนวนบัญชีสมาชิกในผลลัพธ์นี้</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและกรองบัญชี</h2>
        <p class="rb-card-subtitle">ค้นหาจากชื่อผู้ใช้ ครัวเรือน ผู้ติดต่อ หรือชื่อเจ้าหน้าที่ แล้วกรองตาม role และสถานะบัญชี</p>
      </div>
      @if($q !== '' || $role !== '' || $status !== '')
        <span class="rb-chip">กำลังใช้ตัวกรอง</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-6">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหา username / เลขบัญชี / ผู้ติดต่อ / ชื่อเจ้าหน้าที่">
      </div>
      <div class="col-lg-3">
        <label class="form-label">สิทธิ์ผู้ใช้</label>
        <select class="form-select" name="role">
          <option value="">ทุกสิทธิ์</option>
          <option value="admin" @selected($role === 'admin')>ผู้ดูแลระบบ</option>
          <option value="staff" @selected($role === 'staff')>เจ้าหน้าที่</option>
          <option value="member" @selected($role === 'member')>สมาชิก</option>
        </select>
      </div>
      <div class="col-lg-3">
        <label class="form-label">สถานะบัญชี</label>
        <select class="form-select" name="status">
          <option value="">ทุกสถานะ</option>
          <option value="active" @selected($status === 'active')>เปิดใช้งาน</option>
          <option value="inactive" @selected($status === 'inactive')>ปิดใช้งาน</option>
        </select>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">ล้าง</a>
        <button class="btn btn-primary">ค้นหา</button>
      </div>
    </div>
  </form>

  @include('admin.users.partials.users-table')
  @include('admin.users.partials.create-staff-modal', ['createStaffErrors' => $errors->getBag('createStaffAccount')])

  @if($errors->getBag('createStaffAccount')->any())
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('createStaffAccountModal')).show();
      });
    </script>
  @endif
</x-layouts.admin>

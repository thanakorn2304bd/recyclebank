<x-layouts.admin title="บัญชีผู้ใช้ทั้งหมด">
  @php
    $roleLabel = fn (string $role) => match ($role) {
        'admin' => 'ผู้ดูแลระบบ',
        'staff' => 'เจ้าหน้าที่',
        default => 'สมาชิก',
    };
    $createStaffErrors = $errors->getBag('createStaffAccount');
  @endphp

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

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการบัญชีผู้ใช้</h2>
        <p class="rb-card-subtitle">เปิดดูข้อมูลบัญชีและกดเข้าไปที่ Activity Log เพื่อไล่ตรวจสอบประวัติของแต่ละคนต่อได้ทันที</p>
      </div>
      <span class="rb-chip">
        {{ number_format($users->firstItem() ?? 0) }}-{{ number_format($users->lastItem() ?? 0) }}
        / {{ number_format($users->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:80px;" data-sort-type="number">ID</th>
            <th style="width:180px;">Username</th>
            <th style="width:130px;">สิทธิ์</th>
            <th>ข้อมูลอ้างอิง</th>
            <th style="width:120px;">สถานะ</th>
            <th style="width:150px;">สร้างเมื่อ</th>
            <th style="width:150px;">เข้าใช้ล่าสุด</th>
            <th style="width:120px;" class="text-end" data-sort-type="number">Logs</th>
            <th style="width:150px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr>
              <td>{{ $user->user_id }}</td>
              <td class="fw-semibold">{{ $user->username }}</td>
              <td>
                @if($user->role === 'admin')
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $roleLabel($user->role) }}</span>
                @elseif($user->role === 'staff')
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $roleLabel($user->role) }}</span>
                @else
                  <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $roleLabel($user->role) }}</span>
                @endif
              </td>
              <td>
                @if($user->household)
                  <div class="fw-semibold">{{ $user->household->account_no }} - {{ $user->household->contact_person }}</div>
                  <div class="text-muted small">
                    ชุมชน {{ $user->household->community?->community_name ?? '-' }} / บ้านเลขที่ {{ $user->household->house_no }}
                  </div>
                @elseif($user->staff)
                  <div class="fw-semibold">{{ $user->staff->full_name }}</div>
                  <div class="text-muted small">{{ $user->staff->position ?? 'เจ้าหน้าที่' }}</div>
                @else
                  <span class="text-muted">ไม่มีข้อมูลผูกกับครัวเรือนหรือเจ้าหน้าที่</span>
                @endif
              </td>
              <td>
                @if($user->is_active)
                  <span class="badge bg-success">เปิดใช้งาน</span>
                @else
                  <span class="badge bg-secondary">ปิดใช้งาน</span>
                @endif
              </td>
              <td>{{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
              <td>{{ $user->last_login?->format('d/m/Y H:i') ?? '-' }}</td>
              <td class="text-end">{{ number_format($user->logs_count) }}</td>
              <td class="text-end">
                @if($user->household)
                  <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('households.show', $user->household) }}">ครัวเรือน</a>
                @elseif($user->staff)
                  <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('admin.staff.show', $user->staff) }}">เจ้าหน้าที่</a>
                @endif
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.activity-logs.index', ['user_id' => $user->user_id]) }}">ดู log</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">ไม่พบบัญชีผู้ใช้ตามเงื่อนไขที่ค้นหา</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $users->links() }}
  </div>

  <div class="modal fade" id="createStaffAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title">เพิ่มบัญชี staff</h5>
            <div class="text-muted small">สร้างข้อมูลเจ้าหน้าที่และบัญชีเข้าสู่ระบบในขั้นตอนเดียว โดยสิทธิ์จะถูกกำหนดเป็นเจ้าหน้าที่อัตโนมัติ</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.users.store-staff') }}">
          @csrf
          <div class="modal-body pt-3">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">ชื่อเจ้าหน้าที่</label>
                <input
                  class="form-control @if($createStaffErrors->has('full_name')) is-invalid @endif"
                  name="full_name"
                  value="{{ old('full_name') }}"
                  placeholder="เช่น สมชาย ใจดี"
                  required
                >
                @if($createStaffErrors->has('full_name'))
                  <div class="invalid-feedback">{{ $createStaffErrors->first('full_name') }}</div>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">เบอร์โทร</label>
                <input
                  class="form-control @if($createStaffErrors->has('phone')) is-invalid @endif"
                  name="phone"
                  value="{{ old('phone') }}"
                  placeholder="กรอกหรือเว้นว่างได้"
                >
                @if($createStaffErrors->has('phone'))
                  <div class="invalid-feedback">{{ $createStaffErrors->first('phone') }}</div>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">ตำแหน่ง</label>
                <input
                  class="form-control @if($createStaffErrors->has('position')) is-invalid @endif"
                  name="position"
                  value="{{ old('position') }}"
                  placeholder="ค่าเริ่มต้น: เจ้าหน้าที่"
                >
                @if($createStaffErrors->has('position'))
                  <div class="invalid-feedback">{{ $createStaffErrors->first('position') }}</div>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">สถานะบัญชี</label>
                <select class="form-select @if($createStaffErrors->has('account_status')) is-invalid @endif" name="account_status" required>
                  <option value="active" @selected(old('account_status', 'active') === 'active')>เปิดใช้งานทันที</option>
                  <option value="inactive" @selected(old('account_status') === 'inactive')>สร้างไว้ก่อนและปิดใช้งาน</option>
                </select>
                @if($createStaffErrors->has('account_status'))
                  <div class="invalid-feedback">{{ $createStaffErrors->first('account_status') }}</div>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">Username</label>
                <input
                  class="form-control @if($createStaffErrors->has('username')) is-invalid @endif"
                  name="username"
                  value="{{ old('username') }}"
                  placeholder="เช่น staff.finance"
                  required
                >
                <div class="form-text">ใช้ได้เฉพาะตัวอักษรอังกฤษ ตัวเลข จุด ขีดล่าง และขีดกลาง</div>
                @if($createStaffErrors->has('username'))
                  <div class="invalid-feedback">{{ $createStaffErrors->first('username') }}</div>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">รหัสผ่าน</label>
                <input
                  class="form-control @if($createStaffErrors->has('password')) is-invalid @endif"
                  type="password"
                  name="password"
                  required
                >
                <div class="form-text">อย่างน้อย 8 ตัวอักษร</div>
                @if($createStaffErrors->has('password'))
                  <div class="invalid-feedback">{{ $createStaffErrors->first('password') }}</div>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">ยืนยันรหัสผ่าน</label>
                <input class="form-control" type="password" name="password_confirmation" required>
              </div>
              <div class="col-12">
                <div class="alert alert-light border mb-0">
                  บัญชีที่สร้างจากฟอร์มนี้จะผูกกับเจ้าหน้าที่ใหม่ 1 คน และกำหนดสิทธิ์เป็น <strong>staff</strong> อัตโนมัติ
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-primary">บันทึกบัญชี staff</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @if($createStaffErrors->any())
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('createStaffAccountModal')).show();
      });
    </script>
  @endif
</x-layouts.admin>

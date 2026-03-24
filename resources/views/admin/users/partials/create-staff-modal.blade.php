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

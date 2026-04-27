@php
  $oldStaffId = old('staff_id');
  $preselectedStaff = collect($staffOptions ?? [])->firstWhere('id', $oldStaffId !== null ? (int) $oldStaffId : null);
@endphp
<div class="modal fade" id="createStaffAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title">เพิ่มบัญชีเจ้าหน้าที่</h5>
          <div class="text-muted small">เลือกเจ้าหน้าที่ที่มีอยู่ในระบบเพื่อสร้างบัญชีเข้าสู่ระบบให้ โดยสิทธิ์จะถูกกำหนดเป็นเจ้าหน้าที่อัตโนมัติ</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('admin.users.store-staff') }}">
        @csrf
        <div class="modal-body pt-3">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="staffPickerInput">เจ้าหน้าที่</label>
              <div class="rb-staff-picker position-relative" data-staff-picker>
                <input
                  type="hidden"
                  name="staff_id"
                  data-staff-picker-value
                  value="{{ $preselectedStaff['id'] ?? '' }}"
                >
                <input
                  type="text"
                  id="staffPickerInput"
                  class="form-control @if($createStaffErrors->has('staff_id')) is-invalid @endif"
                  data-staff-picker-input
                  placeholder="พิมพ์เพื่อค้นหาเจ้าหน้าที่ (ชื่อ / ตำแหน่ง / เบอร์โทร)"
                  value="{{ $preselectedStaff['name'] ?? '' }}"
                  autocomplete="off"
                >
                <div class="rb-staff-picker__menu dropdown-menu w-100" data-staff-picker-menu></div>
                @if($createStaffErrors->has('staff_id'))
                  <div class="invalid-feedback d-block">{{ $createStaffErrors->first('staff_id') }}</div>
                @endif
                <div class="form-text">เลือกจากรายชื่อเจ้าหน้าที่ที่บันทึกไว้แล้ว ยังไม่มีรายชื่อ? <a href="{{ route('admin.staff.create') }}">เพิ่มเจ้าหน้าที่ก่อน</a></div>
              </div>
            </div>
            <div class="col-12">
              <div class="rb-info-panel" data-staff-picker-preview hidden>
                <div class="row g-2 small">
                  <div class="col-md-6"><span class="text-muted">ชื่อ:</span> <span data-staff-picker-preview-name></span></div>
                  <div class="col-md-3"><span class="text-muted">ตำแหน่ง:</span> <span data-staff-picker-preview-position></span></div>
                  <div class="col-md-3"><span class="text-muted">เบอร์โทร:</span> <span data-staff-picker-preview-phone></span></div>
                </div>
              </div>
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
                บัญชีที่สร้างจากฟอร์มนี้จะผูกกับเจ้าหน้าที่ที่เลือก และกำหนดสิทธิ์เป็น <strong>staff</strong> อัตโนมัติ
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึกบัญชีเจ้าหน้าที่</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .rb-staff-picker__menu {
    display: none;
    max-height: 240px;
    overflow-y: auto;
    margin-top: 0.25rem;
  }
  .rb-staff-picker__menu.show {
    display: block;
  }
  .rb-staff-picker__item {
    display: block;
    padding: 0.55rem 0.85rem;
    border-radius: 0.65rem;
    color: var(--rb-text);
    cursor: pointer;
  }
  .rb-staff-picker__item:hover,
  .rb-staff-picker__item.is-active {
    background: rgba(15, 109, 74, 0.08);
    color: #0d5134;
  }
  .rb-staff-picker__item-name {
    font-weight: 600;
  }
  .rb-staff-picker__item-meta {
    color: var(--rb-text-soft);
    font-size: 0.82rem;
  }
  .rb-staff-picker__empty {
    padding: 0.55rem 0.85rem;
    color: var(--rb-text-soft);
    font-size: 0.88rem;
  }
</style>

<script>
  (function () {
    const STAFFS = @json($staffOptions ?? []);

    function normalize(value) {
      return String(value ?? '').toLowerCase().trim();
    }

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    document.querySelectorAll('[data-staff-picker]').forEach(function (root) {
      const input = root.querySelector('[data-staff-picker-input]');
      const hidden = root.querySelector('[data-staff-picker-value]');
      const menu = root.querySelector('[data-staff-picker-menu]');
      const preview = document.querySelector('[data-staff-picker-preview]');
      const previewName = document.querySelector('[data-staff-picker-preview-name]');
      const previewPosition = document.querySelector('[data-staff-picker-preview-position]');
      const previewPhone = document.querySelector('[data-staff-picker-preview-phone]');

      let activeIndex = -1;
      let filtered = STAFFS.slice();

      function updatePreview() {
        if (!preview) return;
        const id = hidden.value;
        if (!id) {
          preview.hidden = true;
          return;
        }
        const staff = STAFFS.find(function (s) { return String(s.id) === String(id); });
        if (!staff) {
          preview.hidden = true;
          return;
        }
        preview.hidden = false;
        if (previewName) previewName.textContent = staff.name || '-';
        if (previewPosition) previewPosition.textContent = staff.position || '-';
        if (previewPhone) previewPhone.textContent = staff.phone || '-';
      }

      function render() {
        if (filtered.length === 0) {
          menu.innerHTML = '<div class="rb-staff-picker__empty">ไม่พบเจ้าหน้าที่ที่ตรงกับคำค้นหา</div>';
          return;
        }
        menu.innerHTML = filtered.map(function (staff, index) {
          const meta = [staff.position, staff.phone].filter(Boolean).join(' · ');
          return '<a href="#" class="rb-staff-picker__item' + (index === activeIndex ? ' is-active' : '') + '" data-staff-id="' + escapeHtml(staff.id) + '">' +
            '<div class="rb-staff-picker__item-name">' + escapeHtml(staff.name) + '</div>' +
            (meta ? '<div class="rb-staff-picker__item-meta">' + escapeHtml(meta) + '</div>' : '') +
            '</a>';
        }).join('');
      }

      function applyFilter() {
        const q = normalize(input.value);
        filtered = STAFFS.filter(function (staff) {
          if (!q) return true;
          return normalize(staff.name).includes(q) ||
            normalize(staff.position).includes(q) ||
            normalize(staff.phone).includes(q);
        });
        activeIndex = filtered.length > 0 ? 0 : -1;
        render();
      }

      function open() {
        applyFilter();
        menu.classList.add('show');
      }

      function close() {
        menu.classList.remove('show');
      }

      function select(staff) {
        hidden.value = staff.id;
        input.value = staff.name;
        updatePreview();
        close();
      }

      input.addEventListener('focus', open);
      input.addEventListener('input', function () {
        hidden.value = '';
        updatePreview();
        open();
      });
      input.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          if (filtered.length === 0) return;
          activeIndex = (activeIndex + 1) % filtered.length;
          render();
        } else if (event.key === 'ArrowUp') {
          event.preventDefault();
          if (filtered.length === 0) return;
          activeIndex = (activeIndex - 1 + filtered.length) % filtered.length;
          render();
        } else if (event.key === 'Enter') {
          if (menu.classList.contains('show') && activeIndex >= 0 && filtered[activeIndex]) {
            event.preventDefault();
            select(filtered[activeIndex]);
          }
        } else if (event.key === 'Escape') {
          close();
        }
      });

      menu.addEventListener('mousedown', function (event) {
        const item = event.target.closest('[data-staff-id]');
        if (!item) return;
        event.preventDefault();
        const staff = STAFFS.find(function (s) { return String(s.id) === item.dataset.staffId; });
        if (staff) select(staff);
      });

      document.addEventListener('mousedown', function (event) {
        if (!root.contains(event.target)) close();
      });

      updatePreview();
    });
  })();
</script>

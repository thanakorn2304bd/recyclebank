<x-layouts.admin title="เพิ่มครัวเรือน">
  <div class="mb-3">
    <h3 class="mb-0">เพิ่มครัวเรือน</h3>
    <div class="text-muted">ขั้นตอน 1 จาก 2: กรอกข้อมูลครัวเรือน ก่อนตั้งรหัสผ่านสำหรับเข้าใช้งาน</div>
  </div>

  <form method="POST" action="{{ route('households.store') }}" class="bg-white p-3 rounded">
    @csrf

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">เลขบัญชี</label>
        <input
          id="account_no"
          class="form-control"
          name="account_no"
          value="{{ old('account_no') }}"
          maxlength="10"
          inputmode="numeric"
        >
        <div class="form-text">ระบบสร้างอัตโนมัติให้ก่อน และสามารถแก้ไขเองได้หากต้องการ</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">เลขที่ชุมชน</label>
        <select id="community_id" class="form-select" name="community_id" required>
          <option value="">-- เลือกชุมชน --</option>
          @foreach($communities as $c)
            <option value="{{ $c->community_id }}" @selected(old('community_id') == $c->community_id)>
              {{ $c->community_id }} - {{ $c->community_name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">บ้านเลขที่</label>
        <input id="house_no" class="form-control" name="house_no" value="{{ old('house_no') }}" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">หมู่</label>
        <input class="form-control" name="village_no" value="{{ old('village_no') }}">
      </div>

      <div class="col-md-5">
        <label class="form-label">ชื่อผู้ติดต่อ</label>
        <input class="form-control" name="contact_person" value="{{ old('contact_person') }}" required>
      </div>

      <div class="col-md-5">
        <label class="form-label">เบอร์โทร</label>
        <input class="form-control" name="phone" value="{{ old('phone') }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">วันที่สมัคร</label>
        <input type="date" class="form-control" name="register_date" value="{{ old('register_date', now()->toDateString()) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="active_status" required>
          <option value="pending" @selected(old('active_status', 'active') === 'pending')>รออนุมัติ</option>
          <option value="active" @selected(old('active_status', 'active') === 'active')>ใช้งาน</option>
          <option value="inactive" @selected(old('active_status', 'active') === 'inactive')>ปิด</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">สะสมเดือน</label>
        <input type="number" class="form-control" name="accumulated_months" min="0" value="{{ old('accumulated_months', 0) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">ยอดคงเหลือ</label>
        <input class="form-control" value="0.00" readonly>
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-success">ถัดไป</button>
      <a class="btn btn-secondary" href="{{ route('households.index') }}">ยกเลิก</a>
    </div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const accountNoInput = document.getElementById('account_no');
      const communitySelect = document.getElementById('community_id');
      const houseNoInput = document.getElementById('house_no');
      const currentYear = @json(now()->format('Y'));

      function generateAccountNo() {
        const communityId = communitySelect.value.trim();
        const houseDigits = houseNoInput.value.replace(/\D/g, '');

        if (!communityId || !houseDigits) {
          return '';
        }

        const houseSuffix = houseDigits.slice(-4).padStart(4, '0');
        return `${currentYear}${communityId}${houseSuffix}`;
      }

      let manualOverride = accountNoInput.value.trim() !== '' && accountNoInput.value.trim() !== generateAccountNo();

      function updateAccountNo() {
        if (manualOverride) {
          return;
        }

        accountNoInput.value = generateAccountNo();
      }

      communitySelect.addEventListener('change', updateAccountNo);
      houseNoInput.addEventListener('input', updateAccountNo);
      accountNoInput.addEventListener('input', function () {
        const generated = generateAccountNo();
        manualOverride = accountNoInput.value.trim() !== '' && accountNoInput.value.trim() !== generated;
      });

      updateAccountNo();
    });
  </script>
</x-layouts.admin>

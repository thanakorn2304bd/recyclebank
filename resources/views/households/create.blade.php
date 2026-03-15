<x-layouts.admin title="เพิ่มครัวเรือน">
  @php
    $oldMembers = old('members', []);
    $oldMembers = is_array($oldMembers) ? array_values($oldMembers) : [];
  @endphp

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
        <input id="contact_person" class="form-control" name="contact_person" value="{{ old('contact_person') }}" required>
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

    <div class="mt-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
          <h5 class="mb-1">สมาชิกในครัวเรือน</h5>
          <div class="text-muted small">ต้องมีสมาชิกอย่างน้อย 1 คน โดยระบบจะใช้ชื่อผู้ติดต่อเป็นค่าเริ่มต้นของคนแรก และยังแก้ไขได้</div>
        </div>
        <button type="button" id="addHouseholdMemberBtn" class="btn btn-outline-primary">เพิ่มสมาชิกครัวเรือน</button>
      </div>

      <div id="householdMembersEmpty" class="alert alert-light border text-muted mb-3">
        ระบบจะเตรียมสมาชิกคนแรกให้อัตโนมัติจากชื่อผู้ติดต่อ
      </div>

      <div id="householdMembersContainer" class="d-grid gap-3"></div>
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
      const contactPersonInput = document.getElementById('contact_person');
      const addHouseholdMemberBtn = document.getElementById('addHouseholdMemberBtn');
      const householdMembersContainer = document.getElementById('householdMembersContainer');
      const householdMembersEmpty = document.getElementById('householdMembersEmpty');
      const currentYear = @json(now()->format('Y'));
      const oldMembers = @json($oldMembers);
      let memberIndex = 0;

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

      function contactPersonName() {
        return contactPersonInput.value.trim();
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }

      function toggleMembersEmptyState() {
        householdMembersEmpty.classList.toggle('d-none', householdMembersContainer.children.length > 0);
      }

      function syncMemberRowState() {
        const memberRows = householdMembersContainer.querySelectorAll('.household-member-row');

        memberRows.forEach(function (row, index) {
          const orderLabel = row.querySelector('.member-order-label');
          const removeButton = row.querySelector('.remove-household-member');

          if (orderLabel) {
            orderLabel.textContent = `สมาชิกคนที่ ${index + 1}`;
          }

          if (removeButton) {
            removeButton.disabled = memberRows.length === 1;
          }
        });

        if (memberRows.length === 0) {
          memberIndex = 0;
        }

        toggleMembersEmptyState();
      }

      function syncFirstMemberNameFromContact() {
        const firstRow = householdMembersContainer.querySelector('.household-member-row');

        if (!firstRow || firstRow.dataset.canAutoContactSync !== 'true' || firstRow.dataset.autoContactSync !== 'true') {
          return;
        }

        const fullNameInput = firstRow.querySelector('[data-member-field="full_name"]');

        if (fullNameInput) {
          fullNameInput.value = contactPersonName();
        }
      }

      function syncHeadSelection(activeCheckbox) {
        householdMembersContainer.querySelectorAll('[data-member-field="is_head"]').forEach(function (checkbox) {
          const row = checkbox.closest('.household-member-row');
          const relationInput = row ? row.querySelector('[data-member-field="relation"]') : null;

          if (checkbox === activeCheckbox) {
            if (checkbox.checked && relationInput) {
              relationInput.value = 'หัวหน้าครัวเรือน';
            } else if (relationInput && relationInput.value.trim() === 'หัวหน้าครัวเรือน') {
              relationInput.value = '';
            }

            return;
          }

          checkbox.checked = false;

          if (relationInput && relationInput.value.trim() === 'หัวหน้าครัวเรือน') {
            relationInput.value = '';
          }
        });
      }

      function addMemberRow(member = {}, options = {}) {
        const index = memberIndex++;
        const isHead = member.is_head === true || member.is_head === 1 || member.is_head === '1';
        const canAutoContactSync = options.autoSyncContactName === true;
        const incomingFullName = String(member.full_name ?? '').trim();
        const initialFullName = incomingFullName !== '' ? incomingFullName : (canAutoContactSync ? contactPersonName() : '');
        const initialRelation = String(member.relation ?? '').trim() || (isHead ? 'หัวหน้าครัวเรือน' : '');
        const wrapper = document.createElement('div');
        wrapper.className = 'household-member-row border rounded p-3 bg-light-subtle';
        wrapper.dataset.canAutoContactSync = canAutoContactSync ? 'true' : 'false';
        wrapper.dataset.autoContactSync = canAutoContactSync && (incomingFullName === '' || incomingFullName === contactPersonName()) ? 'true' : 'false';
        wrapper.innerHTML = `
          <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
              <div class="member-order-label fw-semibold"></div>
              <div class="text-muted small">กรอกข้อมูลสมาชิกถ้าต้องการบันทึกไปพร้อมกัน</div>
            </div>
            <button type="button" class="remove-household-member btn btn-sm btn-outline-danger">ลบ</button>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">ชื่อ-นามสกุล</label>
              <input class="form-control" name="members[${index}][full_name]" value="${escapeHtml(initialFullName)}" data-member-field="full_name">
            </div>

            <div class="col-md-3">
              <label class="form-label">เลขบัตรประชาชน</label>
              <input class="form-control" name="members[${index}][id_card]" value="${escapeHtml(member.id_card)}" inputmode="numeric" maxlength="13">
            </div>

            <div class="col-md-3">
              <label class="form-label">ความสัมพันธ์กับหัวหน้า</label>
              <input class="form-control" name="members[${index}][relation]" value="${escapeHtml(initialRelation)}" data-member-field="relation">
            </div>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" name="members[${index}][is_head]" value="1" id="member_head_${index}" ${isHead ? 'checked' : ''} data-member-field="is_head">
            <label class="form-check-label" for="member_head_${index}">
              เป็นหัวหน้าครัวเรือน
            </label>
          </div>
        `;

        const fullNameInput = wrapper.querySelector('[data-member-field="full_name"]');
        const headCheckbox = wrapper.querySelector('[data-member-field="is_head"]');
        const removeButton = wrapper.querySelector('.remove-household-member');

        fullNameInput.addEventListener('input', function () {
          if (wrapper.dataset.canAutoContactSync !== 'true') {
            return;
          }

          wrapper.dataset.autoContactSync = fullNameInput.value.trim() === contactPersonName() ? 'true' : 'false';
        });

        headCheckbox.addEventListener('change', function () {
          syncHeadSelection(headCheckbox);
        });

        removeButton.addEventListener('click', function () {
          if (removeButton.disabled) {
            return;
          }

          wrapper.remove();
          syncMemberRowState();
          syncFirstMemberNameFromContact();
        });

        householdMembersContainer.appendChild(wrapper);

        if (headCheckbox.checked) {
          syncHeadSelection(headCheckbox);
        }

        syncMemberRowState();
        syncFirstMemberNameFromContact();
      }

      addHouseholdMemberBtn.addEventListener('click', function () {
        addMemberRow({}, { autoSyncContactName: householdMembersContainer.children.length === 0 });
      });

      contactPersonInput.addEventListener('input', syncFirstMemberNameFromContact);

      if (oldMembers.length > 0) {
        oldMembers.forEach(function (member, index) {
          addMemberRow(member, { autoSyncContactName: index === 0 });
        });
      } else {
        addMemberRow({}, { autoSyncContactName: true });
      }

      syncMemberRowState();
	      updateAccountNo();
	    });
	  </script>
</x-layouts.admin>

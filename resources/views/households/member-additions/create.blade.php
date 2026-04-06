<x-layouts.admin title="ยื่นคำขอเพิ่มสมาชิกครัวเรือน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Household Member Addition</div>
      <h1 class="rb-page-title">ยื่นคำขอเพิ่มสมาชิกครัวเรือน</h1>
      <p class="rb-page-subtitle">
        บัญชี {{ $household->account_no }} | ผู้ติดต่อ {{ $household->contact_person }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('households.show', $household) }}">กลับหน้ารายละเอียดครัวเรือน</a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="d-grid gap-4">
        <div class="rb-surface p-4">
          <div class="rb-section-head">
            <div>
              <h2 class="rb-card-title">ข้อมูลครัวเรือน</h2>
              <p class="rb-card-subtitle">ตรวจสอบข้อมูลก่อนส่งคำขอเพิ่มสมาชิก</p>
            </div>
          </div>

          <dl class="row rb-detail-list mb-0">
            <dt class="col-sm-5 mb-3">เลขบัญชี</dt>
            <dd class="col-sm-7 mb-3">{{ $household->account_no }}</dd>

            <dt class="col-sm-5 mb-3">ชุมชน</dt>
            <dd class="col-sm-7 mb-3">{{ $household->community?->community_id }} - {{ $household->community?->community_name }}</dd>

            <dt class="col-sm-5 mb-3">บ้านเลขที่</dt>
            <dd class="col-sm-7 mb-3">{{ $household->house_no }}</dd>

            <dt class="col-sm-5 mb-3">ผู้ติดต่อ</dt>
            <dd class="col-sm-7 mb-3">{{ $household->contact_person }}</dd>

            <dt class="col-sm-5 mb-0">สมาชิกปัจจุบัน</dt>
            <dd class="col-sm-7 mb-0">{{ $household->members->count() }} คน</dd>
          </dl>
        </div>

        <div class="rb-surface p-4">
          <div class="rb-section-head">
            <div>
              <h2 class="rb-card-title">หลักฐานที่ต้องแนบ</h2>
              <p class="rb-card-subtitle">สมาชิกใหม่ทุกคนต้องแนบเอกสารให้ครบก่อนส่งคำขอ</p>
            </div>
          </div>

          <div class="d-grid gap-3">
            @foreach($documentRequirements as $requirement)
              <div class="border rounded-4 bg-light-subtle p-3">
                <div class="fw-semibold text-dark">{{ $requirement['label'] }}</div>
                <div class="text-muted small mt-1">{{ $requirement['description'] }}</div>
              </div>
            @endforeach
          </div>

          <div class="alert alert-warning mt-3 mb-0 small">
            เอกสารที่อัปโหลดจะถูกคาดข้อความความปลอดภัยก่อนบันทึกลงระบบ และเจ้าหน้าที่จะพิจารณาคำขอนี้ก่อนเพิ่มสมาชิกใหม่เข้าสู่ครัวเรือน
          </div>
        </div>

        @if($latestMemberAdditionRequest)
          <div class="rb-surface p-4">
            <div class="rb-section-head">
              <div>
                <h2 class="rb-card-title">คำขอล่าสุด</h2>
                <p class="rb-card-subtitle">สรุปผลล่าสุดของคำขอเพิ่มสมาชิกที่ผ่านมา</p>
              </div>
            </div>

            @php
              $latestStatusMap = [
                'pending' => ['label' => 'รอตรวจสอบ', 'class' => 'text-bg-warning'],
                'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'text-bg-success'],
                'rejected' => ['label' => 'ตีกลับ', 'class' => 'text-bg-danger'],
              ];
              $latestStatusUi = $latestStatusMap[$latestMemberAdditionRequest->status] ?? $latestStatusMap['pending'];
            @endphp

            <div class="d-flex align-items-center justify-content-between gap-3">
              <div>
                <div class="small text-muted">สถานะล่าสุด</div>
                <div class="fw-semibold">{{ $latestMemberAdditionRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
              </div>
              <span class="badge {{ $latestStatusUi['class'] }}">{{ $latestStatusUi['label'] }}</span>
            </div>

            <div class="text-muted small mt-3">
              หมายเหตุจากเจ้าหน้าที่: {{ $latestMemberAdditionRequest->review_notes ?: 'ยังไม่มีหมายเหตุเพิ่มเติม' }}
            </div>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-8">
      <form method="POST" action="{{ route('households.member-additions.store', $household) }}" enctype="multipart/form-data">
        @csrf

        <div class="rb-surface p-4">
          <div class="rb-section-head">
            <div>
              <h2 class="rb-card-title">สมาชิกใหม่ที่ต้องการเพิ่ม</h2>
              <p class="rb-card-subtitle">กรอกข้อมูลสมาชิกใหม่และแนบหลักฐานให้ครบทุกคน</p>
            </div>
            <button type="button" class="btn btn-outline-primary" id="addHouseholdMemberAdditionRow">
              เพิ่มสมาชิกใหม่
            </button>
          </div>

          @error('members')
            <div class="alert alert-danger">{{ $message }}</div>
          @enderror

          <div id="householdMemberAdditionRows" class="d-grid gap-3">
            @foreach($oldMembers as $index => $member)
              <div class="border rounded-4 p-3 bg-light-subtle household-member-addition-row">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                  <div>
                    <div class="fw-semibold text-dark">สมาชิกใหม่คนที่ <span class="member-row-number">{{ $index + 1 }}</span></div>
                    <div class="text-muted small">เจ้าหน้าที่จะใช้ข้อมูลชุดนี้ตรวจสอบก่อนเพิ่มเข้าสู่ครัวเรือน</div>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-danger remove-household-member-addition-row">
                    ลบรายการนี้
                  </button>
                </div>

                <div class="row g-3">
                  <div class="col-md-5">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input
                      type="text"
                      class="form-control @error('members.'.$index.'.full_name') is-invalid @enderror"
                      name="members[{{ $index }}][full_name]"
                      value="{{ old('members.'.$index.'.full_name', $member['full_name'] ?? '') }}"
                    >
                    @error('members.'.$index.'.full_name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">เลขบัตรประชาชน</label>
                    <input
                      type="text"
                      inputmode="numeric"
                      maxlength="13"
                      class="form-control @error('members.'.$index.'.id_card') is-invalid @enderror"
                      name="members[{{ $index }}][id_card]"
                      value="{{ old('members.'.$index.'.id_card', $member['id_card'] ?? '') }}"
                    >
                    @error('members.'.$index.'.id_card')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">ความสัมพันธ์</label>
                    <input
                      type="text"
                      class="form-control @error('members.'.$index.'.relation') is-invalid @enderror"
                      name="members[{{ $index }}][relation]"
                      value="{{ old('members.'.$index.'.relation', $member['relation'] ?? '') }}"
                    >
                    @error('members.'.$index.'.relation')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">{{ $documentRequirements['member_household_copies']['label'] }}</label>
                    <input
                      type="file"
                      class="form-control @error('member_household_copies.'.$index) is-invalid @enderror"
                      name="member_household_copies[{{ $index }}]"
                      accept=".jpg,.jpeg,.png,.pdf"
                    >
                    <div class="form-text">รองรับ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</div>
                    @error('member_household_copies.'.$index)
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">{{ $documentRequirements['member_national_id_copies']['label'] }}</label>
                    <input
                      type="file"
                      class="form-control @error('member_national_id_copies.'.$index) is-invalid @enderror"
                      name="member_national_id_copies[{{ $index }}]"
                      accept=".jpg,.jpeg,.png,.pdf"
                    >
                    <div class="form-text">รองรับ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</div>
                    @error('member_national_id_copies.'.$index)
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
            <a class="btn btn-outline-secondary" href="{{ route('households.show', $household) }}">ยกเลิก</a>
            <button class="btn btn-primary">ส่งคำขอเพิ่มสมาชิก</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const memberRowsContainer = document.getElementById('householdMemberAdditionRows');
      const addMemberButton = document.getElementById('addHouseholdMemberAdditionRow');

      if (! memberRowsContainer || ! addMemberButton) {
        return;
      }

      function memberRowTemplate(index) {
        const number = index + 1;

        return `
          <div class="border rounded-4 p-3 bg-light-subtle household-member-addition-row">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <div class="fw-semibold text-dark">สมาชิกใหม่คนที่ <span class="member-row-number">${number}</span></div>
                <div class="text-muted small">เจ้าหน้าที่จะใช้ข้อมูลชุดนี้ตรวจสอบก่อนเพิ่มเข้าสู่ครัวเรือน</div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger remove-household-member-addition-row">ลบรายการนี้</button>
            </div>

            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label">ชื่อ-นามสกุล</label>
                <input type="text" class="form-control" name="members[${index}][full_name]">
              </div>
              <div class="col-md-4">
                <label class="form-label">เลขบัตรประชาชน</label>
                <input type="text" inputmode="numeric" maxlength="13" class="form-control" name="members[${index}][id_card]">
              </div>
              <div class="col-md-3">
                <label class="form-label">ความสัมพันธ์</label>
                <input type="text" class="form-control" name="members[${index}][relation]">
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ $documentRequirements['member_household_copies']['label'] }}</label>
                <input type="file" class="form-control" name="member_household_copies[${index}]" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">รองรับ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ $documentRequirements['member_national_id_copies']['label'] }}</label>
                <input type="file" class="form-control" name="member_national_id_copies[${index}]" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">รองรับ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</div>
              </div>
            </div>
          </div>
        `;
      }

      function syncMemberRows() {
        const memberRows = Array.from(memberRowsContainer.querySelectorAll('.household-member-addition-row'));

        memberRows.forEach(function (row, index) {
          const numberNode = row.querySelector('.member-row-number');

          if (numberNode) {
            numberNode.textContent = String(index + 1);
          }

          row.querySelectorAll('input').forEach(function (input) {
            const name = input.getAttribute('name');

            if (! name) {
              return;
            }

            input.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
          });
        });
      }

      addMemberButton.addEventListener('click', function () {
        const nextIndex = memberRowsContainer.querySelectorAll('.household-member-addition-row').length;
        memberRowsContainer.insertAdjacentHTML('beforeend', memberRowTemplate(nextIndex));
        syncMemberRows();
      });

      memberRowsContainer.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-household-member-addition-row');

        if (! removeButton) {
          return;
        }

        const rows = memberRowsContainer.querySelectorAll('.household-member-addition-row');

        if (rows.length <= 1) {
          rows[0]?.querySelectorAll('input').forEach(function (input) {
            input.value = '';
          });

          return;
        }

        removeButton.closest('.household-member-addition-row')?.remove();
        syncMemberRows();
      });

      syncMemberRows();
    });
  </script>
</x-layouts.admin>

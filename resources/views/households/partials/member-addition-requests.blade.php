@php
  $currentRequest = $memberAdditionRequestForView;
  $currentStatusMap = [
    'pending' => [
      'label' => 'รอตรวจสอบ',
      'chip_class' => 'text-bg-warning',
      'summary_class' => 'alert alert-warning',
      'summary_text' => 'คำขอนี้อยู่ระหว่างรอเจ้าหน้าที่ตรวจสอบเอกสารและข้อมูลของสมาชิกใหม่',
    ],
    'approved' => [
      'label' => 'อนุมัติแล้ว',
      'chip_class' => 'text-bg-success',
      'summary_class' => 'alert alert-success',
      'summary_text' => 'คำขอนี้ได้รับการอนุมัติแล้ว และระบบได้เพิ่มสมาชิกใหม่เข้าในครัวเรือนเรียบร้อย',
    ],
    'rejected' => [
      'label' => 'ตีกลับ',
      'chip_class' => 'text-bg-danger',
      'summary_class' => 'alert alert-danger',
      'summary_text' => 'คำขอนี้ถูกตีกลับ กรุณาตรวจสอบหมายเหตุจากเจ้าหน้าที่ก่อนยื่นใหม่อีกครั้ง',
    ],
  ];
  $currentStatusUi = $currentRequest
    ? ($currentStatusMap[$currentRequest->status] ?? $currentStatusMap['pending'])
    : null;
  $canCreateNewRequest = ! $isPrivileged && $pendingMemberAdditionRequest === null;
@endphp

<div class="rb-surface p-4">
  <div class="rb-section-head">
    <div>
      <h2 class="rb-card-title">คำขอเพิ่มสมาชิกในครัวเรือน</h2>
      <p class="rb-card-subtitle">
        {{ $isPrivileged
          ? 'ตรวจสอบสมาชิกใหม่ที่ผู้ใช้ยื่นเข้ามา พร้อมเอกสารประกอบก่อนอนุมัติเพิ่มรายชื่อเข้าสู่ครัวเรือน'
          : 'ยื่นคำขอเพิ่มสมาชิกใหม่พร้อมแนบเอกสารประกอบ แล้วติดตามผลการพิจารณาจากหน้านี้ได้ตลอดเวลา' }}
      </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
      @if($currentRequest)
        <span class="badge {{ $currentStatusUi['chip_class'] }}">{{ $currentStatusUi['label'] }}</span>
      @endif

      @if($canCreateNewRequest)
        <a class="btn btn-outline-primary" href="{{ route('households.member-additions.create', $household) }}">
          ยื่นคำขอเพิ่มสมาชิก
        </a>
      @endif
    </div>
  </div>

  @if(! $currentRequest)
    <div class="rb-empty-state">
      ยังไม่มีคำขอเพิ่มสมาชิกในครัวเรือนนี้
    </div>

    @if($canCreateNewRequest)
      <div class="text-muted mt-3">
        หากต้องการเพิ่มสมาชิกใหม่ในครัวเรือน สามารถกดยื่นคำขอและแนบสำเนาทะเบียนบ้านกับสำเนาบัตรประชาชนของสมาชิกใหม่แต่ละคนได้จากปุ่มด้านบน
      </div>
    @endif
  @else
    <div class="{{ $currentStatusUi['summary_class'] }} mb-4">
      <div class="fw-semibold">{{ $currentStatusUi['summary_text'] }}</div>
      @if($currentRequest->review_notes)
        <div class="small mt-2">หมายเหตุจากเจ้าหน้าที่: {{ $currentRequest->review_notes }}</div>
      @endif
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
          <div class="small text-muted">วันที่ยื่นคำขอ</div>
          <div class="fw-semibold mt-1">{{ $currentRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
          <div class="text-muted small mt-2">จำนวนสมาชิกใหม่ {{ $currentRequest->requestedMembers->count() }} คน</div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
          <div class="small text-muted">{{ $isPrivileged ? 'ผู้ยื่นคำขอ' : 'ผู้พิจารณาล่าสุด' }}</div>
          <div class="fw-semibold mt-1">
            @if($isPrivileged)
              {{ $currentRequest->requestedByUser?->staff?->full_name ?? $currentRequest->requestedByUser?->username ?? '-' }}
            @else
              {{ $currentRequest->reviewedByUser?->staff?->full_name ?? $currentRequest->reviewedByUser?->username ?? '-' }}
            @endif
          </div>
          <div class="text-muted small mt-2">
            @if($isPrivileged)
              บัญชีผู้ใช้ {{ $currentRequest->requestedByUser?->username ?? '-' }}
            @else
              {{ $currentRequest->reviewed_at?->format('d/m/Y H:i') ?? 'ยังไม่พิจารณา' }}
            @endif
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
          <div class="small text-muted">เอกสารประกอบ</div>
          <div class="fw-semibold mt-1">{{ $currentRequest->documents->count() }} ไฟล์</div>
          <div class="text-muted small mt-2">
            {{ $currentRequest->reviewed_at?->format('d/m/Y H:i') ? 'พิจารณาล่าสุด '.$currentRequest->reviewed_at->format('d/m/Y H:i') : 'ยังไม่มีเวลาพิจารณา' }}
          </div>
        </div>
      </div>
    </div>

    <div class="rb-section-head">
      <div>
        <h3 class="h5 mb-1">สมาชิกใหม่ในคำขอ</h3>
        <p class="rb-card-subtitle mb-0">ดูรายชื่อสมาชิกใหม่ที่ผู้ใช้ยื่นเข้ามาในคำขอนี้</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th style="width:56px;">#</th>
            <th>ชื่อ-นามสกุล</th>
            <th style="width:160px;">เลขบัตรประชาชน</th>
            <th style="width:180px;">ความสัมพันธ์</th>
          </tr>
        </thead>
        <tbody>
          @foreach($memberAdditionRequestMembers as $member)
            <tr>
              <td>{{ $member['position'] }}</td>
              <td>{{ $member['display_name'] }}</td>
              <td>{{ $member['id_card_last4'] !== '' ? 'x-xxxx-xxxxx-'.$member['id_card_last4'] : '-' }}</td>
              <td>{{ $member['relation'] !== '' ? $member['relation'] : '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if($isPrivileged)
      @if($currentRequest->status === 'pending')
        <div class="mt-4 border rounded-4 p-4 bg-light-subtle">
          <div class="rb-section-head">
            <div>
              <h3 class="h5 mb-1">พิจารณาคำขอเพิ่มสมาชิก</h3>
              <p class="rb-card-subtitle mb-0">เมื่ออนุมัติแล้ว ระบบจะเพิ่มสมาชิกใหม่ทั้งหมดในคำขอนี้เข้าในครัวเรือนทันที</p>
            </div>
          </div>

          <form method="POST" action="{{ route('households.member-additions.review', [$household, $currentRequest]) }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
              <label class="form-label">ผลการพิจารณา</label>
              <select class="form-select @error('decision') is-invalid @enderror" name="decision" id="memberAdditionDecision" required>
                <option value="approved" @selected(old('decision', 'approved') === 'approved')>อนุมัติและเพิ่มสมาชิก</option>
                <option value="rejected" @selected(old('decision') === 'rejected')>ตีกลับคำขอ</option>
              </select>
              @error('decision')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">หมายเหตุการพิจารณา</label>
              <textarea
                class="form-control @error('review_notes') is-invalid @enderror"
                name="review_notes"
                id="memberAdditionReviewNotes"
                rows="4"
              >{{ old('review_notes') }}</textarea>
              <div class="form-text" id="memberAdditionReviewNotesHelp">
                ถ้าตีกลับคำขอ ควรระบุเหตุผลให้ชัดเจน เช่น เอกสารไม่ครบ ข้อมูลไม่ตรงกัน หรือไฟล์อ่านไม่ชัด
              </div>
              @error('review_notes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <button class="btn btn-primary">บันทึกผลการพิจารณาคำขอเพิ่มสมาชิก</button>
          </form>
        </div>
      @endif
    @else
      @if($canCreateNewRequest)
        <div class="d-flex justify-content-end mt-4">
          <a class="btn btn-outline-primary" href="{{ route('households.member-additions.create', $household) }}">
            ยื่นคำขอเพิ่มสมาชิกใหม่อีกครั้ง
          </a>
        </div>
      @endif
    @endif
  @endif
</div>

@if($isPrivileged && $currentRequest && $currentRequest->status === 'pending')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const decisionField = document.getElementById('memberAdditionDecision');
      const reviewNotesField = document.getElementById('memberAdditionReviewNotes');
      const reviewNotesHelp = document.getElementById('memberAdditionReviewNotesHelp');

      if (! decisionField || ! reviewNotesField || ! reviewNotesHelp) {
        return;
      }

      function syncReviewNotesRequirement() {
        const requiresReviewNotes = decisionField.value === 'rejected';

        if (requiresReviewNotes) {
          reviewNotesField.setAttribute('required', 'required');
          reviewNotesField.setAttribute('aria-required', 'true');
          reviewNotesHelp.textContent = 'กรณีตีกลับคำขอ ต้องระบุเหตุผลหรือหลักฐานที่ใช้พิจารณาให้สมาชิกเห็นอย่างชัดเจน';
        } else {
          reviewNotesField.removeAttribute('required');
          reviewNotesField.removeAttribute('aria-required');
          reviewNotesHelp.textContent = 'หากอนุมัติคำขอ สามารถเว้นหมายเหตุได้ หรือบันทึกคำอธิบายเพิ่มเติมไว้เพื่อตรวจสอบย้อนหลัง';
        }
      }

      decisionField.addEventListener('change', syncReviewNotesRequirement);
      syncReviewNotesRequirement();
    });
  </script>
@endif

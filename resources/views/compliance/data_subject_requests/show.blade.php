<x-layouts.admin :title="'คำขอเจ้าของข้อมูล '.$dsar->request_no">
  @php
    $statusLabels = [
      'submitted' => 'รับคำขอแล้ว',
      'in_review' => 'กำลังพิจารณา',
      'completed' => 'ดำเนินการเสร็จ',
      'rejected' => 'ปฏิเสธคำขอ',
    ];
    $typeLabels = [
      'access' => 'ขอเข้าถึงข้อมูล',
      'correction' => 'ขอแก้ไขข้อมูล',
      'deletion' => 'ขอลบข้อมูล',
      'restriction' => 'ขอจำกัดการใช้ข้อมูล',
      'objection' => 'คัดค้านการใช้ข้อมูล',
    ];
  @endphp

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">PDPA Workflow</div>
      <h1 class="rb-page-title">คำขอ {{ $dsar->request_no }}</h1>
      <p class="rb-page-subtitle">
        {{ $dsar->requester_name }} | {{ $typeLabels[$dsar->request_type] ?? $dsar->request_type }} | สถานะ {{ $statusLabels[$dsar->status] ?? $dsar->status }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">กลับทะเบียนคำขอ</a>
      <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">ดูเหตุการณ์ข้อมูล</a>
      <a class="btn btn-outline-secondary" href="{{ route('privacy-notice.show') }}" target="_blank">อ่าน Privacy Notice</a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">ข้อมูลคำขอ</h2>
            <p class="rb-card-subtitle">รายละเอียดผู้ยื่นคำขอ ขอบเขตข้อมูล และผู้รับผิดชอบปัจจุบัน</p>
          </div>
        </div>

        <dl class="row rb-detail-list mb-0">
          <dt class="col-sm-5 mb-3">เลขคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->request_no }}</dd>
          <dt class="col-sm-5 mb-3">ผู้ยื่นคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->requester_name }}</dd>
          <dt class="col-sm-5 mb-3">ช่องทางติดต่อ</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->requester_contact ?: '-' }}</dd>
          <dt class="col-sm-5 mb-3">ครัวเรือน</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->household?->account_no ? $dsar->household->account_no.' - '.$dsar->household->contact_person : '-' }}</dd>
          <dt class="col-sm-5 mb-3">ประเภทคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $typeLabels[$dsar->request_type] ?? $dsar->request_type }}</dd>
          <dt class="col-sm-5 mb-3">สถานะ</dt>
          <dd class="col-sm-7 mb-3">{{ $statusLabels[$dsar->status] ?? $dsar->status }}</dd>
          <dt class="col-sm-5 mb-3">วันที่รับคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->submitted_at?->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">ครบกำหนด</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->due_at?->format('d/m/Y') ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">ผู้รับผิดชอบ</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->assignedToUser?->staff?->full_name ?? $dsar->assignedToUser?->username ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">รายละเอียดคำขอ</dt>
          <dd class="col-sm-7 mb-3">{{ $dsar->request_details }}</dd>
          <dt class="col-sm-5 mb-0">หมายเหตุการดำเนินการ</dt>
          <dd class="col-sm-7 mb-0">{{ $dsar->resolution_notes ?: '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="rb-surface p-4">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">อัปเดตสถานะคำขอ</h2>
            <p class="rb-card-subtitle">ใช้ติดตามความคืบหน้า ระบุผู้รับผิดชอบ และบันทึกผลการดำเนินการ</p>
          </div>
        </div>

        <form method="POST" action="{{ route('compliance.dsars.update', $dsar) }}">
          @csrf
          @method('PUT')

          <div class="row g-3">
            <div class="col-lg-6">
              <label class="form-label">ครัวเรือนที่เกี่ยวข้อง</label>
              <select class="form-select" name="household_id">
                <option value="">ไม่ผูกกับครัวเรือน</option>
                @foreach($households as $household)
                  <option value="{{ $household->household_id }}" @selected(old('household_id', $dsar->household_id) == $household->household_id)>
                    {{ $household->account_no }} - {{ $household->contact_person }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-6">
              <label class="form-label">ประเภทคำขอ</label>
              <select class="form-select" name="request_type" required>
                @foreach($typeLabels as $typeKey => $typeLabel)
                  <option value="{{ $typeKey }}" @selected(old('request_type', $dsar->request_type) === $typeKey)>{{ $typeLabel }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">ชื่อผู้ยื่นคำขอ</label>
              <input class="form-control" name="requester_name" value="{{ old('requester_name', $dsar->requester_name) }}" required>
            </div>
            <div class="col-lg-6">
              <label class="form-label">ช่องทางติดต่อ</label>
              <input class="form-control" name="requester_contact" value="{{ old('requester_contact', $dsar->requester_contact) }}">
            </div>

            <div class="col-lg-4">
              <label class="form-label">วันที่รับคำขอ</label>
              <input class="form-control" type="date" name="submitted_at" value="{{ old('submitted_at', $dsar->submitted_at?->toDateString()) }}" required>
            </div>
            <div class="col-lg-4">
              <label class="form-label">ครบกำหนดติดตาม</label>
              <input class="form-control" type="date" name="due_at" value="{{ old('due_at', $dsar->due_at?->toDateString()) }}">
            </div>
            <div class="col-lg-4">
              <label class="form-label">สถานะ</label>
              <select class="form-select" name="status" required>
                @foreach($statusLabels as $statusKey => $statusLabel)
                  <option value="{{ $statusKey }}" @selected(old('status', $dsar->status) === $statusKey)>{{ $statusLabel }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">ผู้รับผิดชอบ</label>
              <select class="form-select" name="assigned_to">
                <option value="">ยังไม่กำหนด</option>
                @foreach($assignees as $assignee)
                  <option value="{{ $assignee->user_id }}" @selected(old('assigned_to', $dsar->assigned_to) == $assignee->user_id)>
                    {{ $assignee->staff?->full_name ?? $assignee->username }} ({{ $assignee->username }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">รายละเอียดคำขอ</label>
              <textarea class="form-control" name="request_details" rows="5" required>{{ old('request_details', $dsar->request_details) }}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label">หมายเหตุการดำเนินการ</label>
              <textarea class="form-control" name="resolution_notes" rows="4">{{ old('resolution_notes', $dsar->resolution_notes) }}</textarea>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <button class="btn btn-primary">บันทึกการอัปเดต</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-layouts.admin>

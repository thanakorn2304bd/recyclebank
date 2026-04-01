<x-layouts.admin title="เพิ่มคำขอเจ้าของข้อมูล">
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
      <h1 class="rb-page-title">เพิ่มคำขอเจ้าของข้อมูล</h1>
      <p class="rb-page-subtitle">บันทึกคำขอใหม่ของเจ้าของข้อมูล พร้อมกำหนดผู้รับผิดชอบและวันครบกำหนดติดตาม</p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">กลับทะเบียนคำขอ</a>
      <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">ดูเหตุการณ์ข้อมูล</a>
    </div>
  </div>

  <form method="POST" action="{{ route('compliance.dsars.store') }}" class="rb-surface p-4">
    @csrf

    <div class="row g-3">
      <div class="col-lg-6">
        <label class="form-label">ครัวเรือนที่เกี่ยวข้อง</label>
        <select class="form-select" name="household_id">
          <option value="">ไม่ผูกกับครัวเรือน</option>
          @foreach($households as $household)
            <option value="{{ $household->household_id }}" @selected(old('household_id') == $household->household_id)>
              {{ $household->account_no }} - {{ $household->contact_person }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-6">
        <label class="form-label">ประเภทคำขอ</label>
        <select class="form-select" name="request_type" required>
          @foreach($typeLabels as $typeKey => $typeLabel)
            <option value="{{ $typeKey }}" @selected(old('request_type', 'access') === $typeKey)>{{ $typeLabel }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-lg-6">
        <label class="form-label">ชื่อผู้ยื่นคำขอ</label>
        <input class="form-control" name="requester_name" value="{{ old('requester_name') }}" required>
      </div>
      <div class="col-lg-6">
        <label class="form-label">ช่องทางติดต่อ</label>
        <input class="form-control" name="requester_contact" value="{{ old('requester_contact') }}" placeholder="เบอร์โทร อีเมล หรือช่องทางติดต่อกลับ">
      </div>

      <div class="col-lg-4">
        <label class="form-label">วันที่รับคำขอ</label>
        <input class="form-control" type="date" name="submitted_at" value="{{ old('submitted_at', now()->toDateString()) }}" required>
      </div>
      <div class="col-lg-4">
        <label class="form-label">ครบกำหนดติดตาม</label>
        <input class="form-control" type="date" name="due_at" value="{{ old('due_at', now()->addDays(30)->toDateString()) }}">
      </div>
      <div class="col-lg-4">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="status" required>
          @foreach($statusLabels as $statusKey => $statusLabel)
            <option value="{{ $statusKey }}" @selected(old('status', 'submitted') === $statusKey)>{{ $statusLabel }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-lg-6">
        <label class="form-label">ผู้รับผิดชอบ</label>
        <select class="form-select" name="assigned_to">
          <option value="">ยังไม่กำหนด</option>
          @foreach($assignees as $assignee)
            <option value="{{ $assignee->user_id }}" @selected(old('assigned_to') == $assignee->user_id)>
              {{ $assignee->staff?->full_name ?? $assignee->username }} ({{ $assignee->username }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">รายละเอียดคำขอ</label>
        <textarea class="form-control" name="request_details" rows="5" required>{{ old('request_details') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">หมายเหตุการดำเนินการ</label>
        <textarea class="form-control" name="resolution_notes" rows="4">{{ old('resolution_notes') }}</textarea>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-end gap-2">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">ยกเลิก</a>
      <button class="btn btn-primary">บันทึกคำขอ</button>
    </div>
  </form>
</x-layouts.admin>

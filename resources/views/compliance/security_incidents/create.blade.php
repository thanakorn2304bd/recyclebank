<x-layouts.admin title="เพิ่มเหตุการณ์ข้อมูลส่วนบุคคล">
  @php
    $statusLabels = [
      'open' => 'เปิดเหตุการณ์',
      'contained' => 'ควบคุมเหตุแล้ว',
      'reported' => 'แจ้งหน่วยงาน/ผู้เกี่ยวข้องแล้ว',
      'closed' => 'ปิดเหตุการณ์',
    ];
    $severityLabels = [
      'low' => 'ต่ำ',
      'medium' => 'กลาง',
      'high' => 'สูง',
      'critical' => 'วิกฤต',
    ];
  @endphp

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">PDPA Workflow</div>
      <h1 class="rb-page-title">เพิ่มเหตุการณ์ข้อมูลส่วนบุคคล</h1>
      <p class="rb-page-subtitle">ใช้บันทึกเหตุผิดปกติ ตั้งระดับความรุนแรง และติดตามการแจ้งเตือนหรือการควบคุมเหตุ</p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">กลับทะเบียนเหตุการณ์</a>
      <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">ดูคำขอเจ้าของข้อมูล</a>
    </div>
  </div>

  <form method="POST" action="{{ route('compliance.incidents.store') }}" class="rb-surface p-4">
    @csrf

    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label">ความรุนแรง</label>
        <select class="form-select" name="severity" required>
          @foreach($severityLabels as $severityKey => $severityLabel)
            <option value="{{ $severityKey }}" @selected(old('severity', 'medium') === $severityKey)>{{ $severityLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="status" required>
          @foreach($statusLabels as $statusKey => $statusLabel)
            <option value="{{ $statusKey }}" @selected(old('status', 'open') === $statusKey)>{{ $statusLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-4">
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

      <div class="col-lg-6">
        <label class="form-label">เกิดเหตุเมื่อ</label>
        <input class="form-control" type="datetime-local" name="occurred_at" value="{{ old('occurred_at') }}">
      </div>
      <div class="col-lg-6">
        <label class="form-label">ตรวจพบเมื่อ</label>
        <input class="form-control" type="datetime-local" name="detected_at" value="{{ old('detected_at', now()->format('Y-m-d\TH:i')) }}" required>
      </div>

      <div class="col-12">
        <label class="form-label">สรุปเหตุการณ์</label>
        <input class="form-control" name="summary" value="{{ old('summary') }}" required>
      </div>

      <div class="col-lg-8">
        <label class="form-label">ขอบเขตผลกระทบ</label>
        <input class="form-control" name="affected_scope" value="{{ old('affected_scope') }}" placeholder="เช่น ข้อมูลสมาชิกครัวเรือนชุมชน 01">
      </div>
      <div class="col-lg-4">
        <label class="form-label">จำนวนระเบียนที่ได้รับผลกระทบ</label>
        <input class="form-control" type="number" min="0" name="affected_record_count" value="{{ old('affected_record_count') }}">
      </div>

      <div class="col-12">
        <label class="form-check">
          <input class="form-check-input" type="checkbox" name="notification_required" value="1" @checked(old('notification_required'))>
          <span class="form-check-label">ต้องมีการแจ้งหน่วยงานหรือเจ้าของข้อมูล</span>
        </label>
      </div>

      <div class="col-lg-6">
        <label class="form-label">แจ้งหน่วยงานเมื่อ</label>
        <input class="form-control" type="datetime-local" name="authority_notified_at" value="{{ old('authority_notified_at') }}">
      </div>
      <div class="col-lg-6">
        <label class="form-label">แจ้งเจ้าของข้อมูลเมื่อ</label>
        <input class="form-control" type="datetime-local" name="subject_notified_at" value="{{ old('subject_notified_at') }}">
      </div>

      <div class="col-12">
        <label class="form-label">ผลกระทบที่พบ</label>
        <textarea class="form-control" name="impact_details" rows="4">{{ old('impact_details') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">การควบคุมและแก้ไขเบื้องต้น</label>
        <textarea class="form-control" name="containment_actions" rows="4">{{ old('containment_actions') }}</textarea>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-end gap-2">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">ยกเลิก</a>
      <button class="btn btn-primary">บันทึกเหตุการณ์</button>
    </div>
  </form>
</x-layouts.admin>

<x-layouts.admin :title="'เหตุการณ์ '.$incident->incident_no">
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
      <h1 class="rb-page-title">เหตุการณ์ {{ $incident->incident_no }}</h1>
      <p class="rb-page-subtitle">
        {{ $incident->summary }} | ระดับ {{ $severityLabels[$incident->severity] ?? $incident->severity }} | สถานะ {{ $statusLabels[$incident->status] ?? $incident->status }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">กลับทะเบียนเหตุการณ์</a>
      <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">ดูคำขอเจ้าของข้อมูล</a>
      <a class="btn btn-outline-secondary" href="{{ route('privacy-notice.show') }}" target="_blank">อ่าน Privacy Notice</a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">ข้อมูลเหตุการณ์</h2>
            <p class="rb-card-subtitle">สรุปเหตุ ผู้แจ้ง ผู้รับผิดชอบ และการแจ้งเตือนที่เกี่ยวข้อง</p>
          </div>
        </div>

        <dl class="row rb-detail-list mb-0">
          <dt class="col-sm-5 mb-3">เลขเหตุการณ์</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->incident_no }}</dd>
          <dt class="col-sm-5 mb-3">ความรุนแรง</dt>
          <dd class="col-sm-7 mb-3">{{ $severityLabels[$incident->severity] ?? $incident->severity }}</dd>
          <dt class="col-sm-5 mb-3">สถานะ</dt>
          <dd class="col-sm-7 mb-3">{{ $statusLabels[$incident->status] ?? $incident->status }}</dd>
          <dt class="col-sm-5 mb-3">ผู้รายงาน</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->reportedByUser?->staff?->full_name ?? $incident->reportedByUser?->username ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">ผู้รับผิดชอบ</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->assignedToUser?->staff?->full_name ?? $incident->assignedToUser?->username ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">เกิดเหตุเมื่อ</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->occurred_at?->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">ตรวจพบเมื่อ</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->detected_at?->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">ผลกระทบ</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->affected_scope ?: '-' }}</dd>
          <dt class="col-sm-5 mb-3">จำนวนระเบียน</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->affected_record_count !== null ? number_format($incident->affected_record_count) : '-' }}</dd>
          <dt class="col-sm-5 mb-3">แจ้งหน่วยงาน</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->authority_notified_at?->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">แจ้งเจ้าของข้อมูล</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->subject_notified_at?->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-5 mb-3">ผลกระทบที่พบ</dt>
          <dd class="col-sm-7 mb-3">{{ $incident->impact_details ?: '-' }}</dd>
          <dt class="col-sm-5 mb-0">การควบคุมเหตุ</dt>
          <dd class="col-sm-7 mb-0">{{ $incident->containment_actions ?: '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="rb-surface p-4">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">อัปเดตเหตุการณ์</h2>
            <p class="rb-card-subtitle">ปรับสถานะ ระบุการแจ้งเตือน และบันทึกมาตรการควบคุมหรือผลกระทบเพิ่มเติม</p>
          </div>
        </div>

        <form method="POST" action="{{ route('compliance.incidents.update', $incident) }}">
          @csrf
          @method('PUT')

          <div class="row g-3">
            <div class="col-lg-4">
              <label class="form-label">ความรุนแรง</label>
              <select class="form-select" name="severity" required>
                @foreach($severityLabels as $severityKey => $severityLabel)
                  <option value="{{ $severityKey }}" @selected(old('severity', $incident->severity) === $severityKey)>{{ $severityLabel }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-4">
              <label class="form-label">สถานะ</label>
              <select class="form-select" name="status" required>
                @foreach($statusLabels as $statusKey => $statusLabel)
                  <option value="{{ $statusKey }}" @selected(old('status', $incident->status) === $statusKey)>{{ $statusLabel }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-4">
              <label class="form-label">ผู้รับผิดชอบ</label>
              <select class="form-select" name="assigned_to">
                <option value="">ยังไม่กำหนด</option>
                @foreach($assignees as $assignee)
                  <option value="{{ $assignee->user_id }}" @selected(old('assigned_to', $incident->assigned_to) == $assignee->user_id)>
                    {{ $assignee->staff?->full_name ?? $assignee->username }} ({{ $assignee->username }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6">
              <label class="form-label">เกิดเหตุเมื่อ</label>
              <input class="form-control" type="datetime-local" name="occurred_at" value="{{ old('occurred_at', $incident->occurred_at?->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-lg-6">
              <label class="form-label">ตรวจพบเมื่อ</label>
              <input class="form-control" type="datetime-local" name="detected_at" value="{{ old('detected_at', $incident->detected_at?->format('Y-m-d\TH:i')) }}" required>
            </div>

            <div class="col-12">
              <label class="form-label">สรุปเหตุการณ์</label>
              <input class="form-control" name="summary" value="{{ old('summary', $incident->summary) }}" required>
            </div>

            <div class="col-lg-8">
              <label class="form-label">ขอบเขตผลกระทบ</label>
              <input class="form-control" name="affected_scope" value="{{ old('affected_scope', $incident->affected_scope) }}">
            </div>
            <div class="col-lg-4">
              <label class="form-label">จำนวนระเบียนที่ได้รับผลกระทบ</label>
              <input class="form-control" type="number" min="0" name="affected_record_count" value="{{ old('affected_record_count', $incident->affected_record_count) }}">
            </div>

            <div class="col-12">
              <label class="form-check">
                <input class="form-check-input" type="checkbox" name="notification_required" value="1" @checked(old('notification_required', $incident->notification_required))>
                <span class="form-check-label">ต้องมีการแจ้งหน่วยงานหรือเจ้าของข้อมูล</span>
              </label>
            </div>

            <div class="col-lg-6">
              <label class="form-label">แจ้งหน่วยงานเมื่อ</label>
              <input class="form-control" type="datetime-local" name="authority_notified_at" value="{{ old('authority_notified_at', $incident->authority_notified_at?->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-lg-6">
              <label class="form-label">แจ้งเจ้าของข้อมูลเมื่อ</label>
              <input class="form-control" type="datetime-local" name="subject_notified_at" value="{{ old('subject_notified_at', $incident->subject_notified_at?->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="col-12">
              <label class="form-label">ผลกระทบที่พบ</label>
              <textarea class="form-control" name="impact_details" rows="4">{{ old('impact_details', $incident->impact_details) }}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label">การควบคุมและแก้ไขเบื้องต้น</label>
              <textarea class="form-control" name="containment_actions" rows="4">{{ old('containment_actions', $incident->containment_actions) }}</textarea>
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

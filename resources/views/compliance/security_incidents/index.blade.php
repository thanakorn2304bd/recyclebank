<x-layouts.admin title="เหตุการณ์ข้อมูลส่วนบุคคล">
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
      <h1 class="rb-page-title">เหตุการณ์ข้อมูลส่วนบุคคล</h1>
      <p class="rb-page-subtitle">ใช้บันทึกเหตุผิดปกติหรือข้อมูลรั่วไหล ประเมินความรุนแรง ระบุผู้รับผิดชอบ และติดตามการแจ้งหน่วยงานหรือเจ้าของข้อมูล</p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">ดูคำขอเจ้าของข้อมูล</a>
      <a class="btn btn-outline-secondary" href="{{ route('privacy-notice.show') }}" target="_blank">อ่าน Privacy Notice</a>
      <a class="btn btn-primary" href="{{ route('compliance.incidents.create') }}">+ เพิ่มเหตุการณ์</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">เหตุการณ์ทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['total']) }}</div>
      <div class="rb-stat-meta">ผลลัพธ์ตามตัวกรองปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยังเปิดอยู่</div>
      <div class="rb-stat-value">{{ number_format($summary['open']) }}</div>
      <div class="rb-stat-meta">สถานะเปิดเหตุการณ์หรือควบคุมเหตุแล้ว</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">รายงานแล้ว</div>
      <div class="rb-stat-value">{{ number_format($summary['reported']) }}</div>
      <div class="rb-stat-meta">รายการที่มีการแจ้งผู้เกี่ยวข้องแล้ว</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">วิกฤต</div>
      <div class="rb-stat-value">{{ number_format($summary['critical']) }}</div>
      <div class="rb-stat-meta">เหตุการณ์ระดับ critical ในผลลัพธ์นี้</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและกรองเหตุการณ์</h2>
        <p class="rb-card-subtitle">ค้นจากเลขเหตุการณ์ สรุปเหตุ หรือขอบเขตผลกระทบ และกรองตามสถานะหรือระดับความรุนแรงได้</p>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-6">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาเลขเหตุการณ์ สรุปเหตุ หรือขอบเขตผลกระทบ">
      </div>
      <div class="col-lg-3">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="status">
          <option value="">ทุกสถานะ</option>
          @foreach($statusLabels as $statusKey => $statusLabel)
            <option value="{{ $statusKey }}" @selected($status === $statusKey)>{{ $statusLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-3">
        <label class="form-label">ความรุนแรง</label>
        <select class="form-select" name="severity">
          <option value="">ทุกระดับ</option>
          @foreach($severityLabels as $severityKey => $severityLabel)
            <option value="{{ $severityKey }}" @selected($severity === $severityKey)>{{ $severityLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">ล้าง</a>
        <button class="btn btn-primary">ค้นหา</button>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ทะเบียนเหตุการณ์</h2>
        <p class="rb-card-subtitle">เรียงจากเหตุการณ์ล่าสุด เพื่อให้เจ้าหน้าที่ติดตามเคสค้างและเคสเสี่ยงสูงได้ง่าย</p>
      </div>
      <span class="rb-chip">
        {{ number_format($incidents->firstItem() ?? 0) }}-{{ number_format($incidents->lastItem() ?? 0) }}
        / {{ number_format($incidents->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:170px;">เลขเหตุการณ์</th>
            <th style="width:150px;">ตรวจพบเมื่อ</th>
            <th>สรุปเหตุ</th>
            <th style="width:120px;">ระดับ</th>
            <th style="width:160px;">สถานะ</th>
            <th style="width:110px;">แจ้งหน่วยงาน</th>
            <th style="width:170px;">ผู้รับผิดชอบ</th>
            <th style="width:130px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($incidents as $item)
            <tr>
              <td class="fw-semibold">{{ $item->incident_no }}</td>
              <td>{{ $item->detected_at?->format('d/m/Y H:i') ?? '-' }}</td>
              <td>
                <div class="fw-semibold">{{ $item->summary }}</div>
                <div class="text-muted small">{{ $item->affected_scope ?: '-' }}</div>
              </td>
              <td>{{ $severityLabels[$item->severity] ?? $item->severity }}</td>
              <td>{{ $statusLabels[$item->status] ?? $item->status }}</td>
              <td>{{ $item->authority_notified_at ? 'แล้ว' : 'ยังไม่แจ้ง' }}</td>
              <td>{{ $item->assignedToUser?->staff?->full_name ?? $item->assignedToUser?->username ?? '-' }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('compliance.incidents.show', $item) }}">ดูรายละเอียด</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">ยังไม่พบเหตุการณ์ข้อมูลส่วนบุคคลตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $incidents->links() }}
  </div>
</x-layouts.admin>

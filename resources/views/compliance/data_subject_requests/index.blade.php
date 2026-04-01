<x-layouts.admin title="คำขอเจ้าของข้อมูล">
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
      <h1 class="rb-page-title">คำขอเจ้าของข้อมูล</h1>
      <p class="rb-page-subtitle">ใช้ติดตามคำขอเข้าถึง แก้ไข ลบ หรือคัดค้านการใช้ข้อมูลส่วนบุคคล พร้อมผู้รับผิดชอบและสถานะการดำเนินการ</p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('compliance.incidents.index') }}">ดูเหตุการณ์ข้อมูล</a>
      <a class="btn btn-outline-secondary" href="{{ route('privacy-notice.show') }}" target="_blank">อ่าน Privacy Notice</a>
      <a class="btn btn-primary" href="{{ route('compliance.dsars.create') }}">+ เพิ่มคำขอ</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">คำขอทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['total']) }}</div>
      <div class="rb-stat-meta">ผลลัพธ์ตามตัวกรองปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">กำลังดำเนินการ</div>
      <div class="rb-stat-value">{{ number_format($summary['open']) }}</div>
      <div class="rb-stat-meta">สถานะรับคำขอแล้วหรือกำลังพิจารณา</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">เสร็จสิ้น</div>
      <div class="rb-stat-value">{{ number_format($summary['completed']) }}</div>
      <div class="rb-stat-meta">คำขอที่ปิดงานแล้ว</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ปฏิเสธ</div>
      <div class="rb-stat-value">{{ number_format($summary['rejected']) }}</div>
      <div class="rb-stat-meta">คำขอที่ไม่สามารถดำเนินการได้</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและกรองคำขอ</h2>
        <p class="rb-card-subtitle">ค้นจากเลขคำขอ ชื่อผู้ยื่น ครัวเรือน หรือเนื้อหาคำขอ และกรองตามสถานะได้</p>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-8">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาเลขคำขอ ชื่อผู้ยื่น ผู้ติดต่อ หรือเลขบัญชี">
      </div>
      <div class="col-lg-4">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="status">
          <option value="">ทุกสถานะ</option>
          @foreach($statusLabels as $statusKey => $statusLabel)
            <option value="{{ $statusKey }}" @selected($status === $statusKey)>{{ $statusLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('compliance.dsars.index') }}">ล้าง</a>
        <button class="btn btn-primary">ค้นหา</button>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ทะเบียนคำขอ</h2>
        <p class="rb-card-subtitle">เรียงจากคำขอล่าสุดเพื่อให้ติดตามงานค้างและคำขอเร่งด่วนได้ง่าย</p>
      </div>
      <span class="rb-chip">
        {{ number_format($requests->firstItem() ?? 0) }}-{{ number_format($requests->lastItem() ?? 0) }}
        / {{ number_format($requests->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:170px;">เลขคำขอ</th>
            <th style="width:140px;">วันที่รับคำขอ</th>
            <th>ผู้ยื่นคำขอ</th>
            <th style="width:170px;">ประเภท</th>
            <th style="width:150px;">สถานะ</th>
            <th style="width:130px;">ครบกำหนด</th>
            <th style="width:170px;">ผู้รับผิดชอบ</th>
            <th style="width:130px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($requests as $item)
            <tr>
              <td class="fw-semibold">{{ $item->request_no }}</td>
              <td>{{ $item->submitted_at?->format('d/m/Y') ?? '-' }}</td>
              <td>
                <div class="fw-semibold">{{ $item->requester_name }}</div>
                <div class="text-muted small">{{ $item->requester_contact ?: '-' }}</div>
                @if($item->household)
                  <div class="text-muted small">{{ $item->household->account_no }} - {{ $item->household->contact_person }}</div>
                @endif
              </td>
              <td>{{ $typeLabels[$item->request_type] ?? $item->request_type }}</td>
              <td>{{ $statusLabels[$item->status] ?? $item->status }}</td>
              <td>{{ $item->due_at?->format('d/m/Y') ?? '-' }}</td>
              <td>{{ $item->assignedToUser?->staff?->full_name ?? $item->assignedToUser?->username ?? '-' }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('compliance.dsars.show', $item) }}">ดูรายละเอียด</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">ยังไม่มีคำขอเจ้าของข้อมูลตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $requests->links() }}
  </div>
</x-layouts.admin>

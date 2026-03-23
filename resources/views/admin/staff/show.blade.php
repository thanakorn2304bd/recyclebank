<x-layouts.admin title="รายละเอียดเจ้าหน้าที่">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Staff Detail</div>
      <h1 class="rb-page-title">รายละเอียดเจ้าหน้าที่ #{{ $staff->staff_id }}</h1>
      <p class="rb-page-subtitle">
        {{ $staff->full_name }} |
        {{ $staff->position ?: 'เจ้าหน้าที่' }} |
        โทร {{ $staff->phone ?: '-' }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">กลับหน้ารายการเจ้าหน้าที่</a>
      <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">ดูบัญชีผู้ใช้</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">บัญชีที่ผูกไว้</div>
      <div class="rb-stat-value">{{ number_format($summary['accounts']) }}</div>
      <div class="rb-stat-meta">จำนวนบัญชีผู้ใช้ที่เชื่อมกับเจ้าหน้าที่คนนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">บัญชีเปิดใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($summary['active_accounts']) }}</div>
      <div class="rb-stat-meta">จำนวนบัญชีที่ยังใช้งานเข้าสู่ระบบได้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">Log ทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['logs']) }}</div>
      <div class="rb-stat-meta">รวม log ของทุกบัญชีที่ผูกกับเจ้าหน้าที่คนนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">เข้าใช้ล่าสุด</div>
      <div class="rb-stat-value">{{ $summary['last_login']?->format('d/m/Y H:i') ?? '-' }}</div>
      <div class="rb-stat-meta">อ้างอิงจากบัญชีที่เข้าสู่ระบบล่าสุด</div>
    </div>
  </div>

  <div class="rb-surface p-3 p-lg-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ข้อมูลพื้นฐาน</h2>
        <p class="rb-card-subtitle">ข้อมูลประจำตัวของเจ้าหน้าที่จากตาราง staff</p>
      </div>
      <span class="rb-chip">Staff #{{ $staff->staff_id }}</span>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label small text-muted">ชื่อเจ้าหน้าที่</label>
        <input class="form-control" value="{{ $staff->full_name }}" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label small text-muted">ตำแหน่ง</label>
        <input class="form-control" value="{{ $staff->position ?: 'เจ้าหน้าที่' }}" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label small text-muted">เบอร์โทร</label>
        <input class="form-control" value="{{ $staff->phone ?: '-' }}" readonly>
      </div>
    </div>
  </div>

  <div class="rb-surface p-3 p-lg-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">บัญชีผู้ใช้ที่เชื่อม</h2>
        <p class="rb-card-subtitle">บัญชีเข้าสู่ระบบทั้งหมดที่อ้างอิงเจ้าหน้าที่คนนี้</p>
      </div>
      <span class="rb-chip">{{ number_format($staff->userAccounts->count()) }} บัญชี</span>
    </div>

    @if($staff->userAccounts->isEmpty())
      <div class="alert alert-warning mb-0">เจ้าหน้าที่คนนี้ยังไม่มีบัญชีผู้ใช้ที่เชื่อมไว้</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped align-middle" data-sortable-table>
          <thead>
            <tr>
              <th style="width:180px;">Username</th>
              <th style="width:120px;">สิทธิ์</th>
              <th style="width:120px;">สถานะ</th>
              <th style="width:150px;">สร้างเมื่อ</th>
              <th style="width:150px;">เข้าใช้ล่าสุด</th>
              <th style="width:110px;" class="text-end" data-sort-type="number">Logs</th>
              <th style="width:160px;" data-sortable="false"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($staff->userAccounts as $account)
              <tr>
                <td class="fw-semibold">{{ $account->username }}</td>
                <td>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ strtoupper($account->role) }}</span>
                </td>
                <td>
                  @if($account->is_active)
                    <span class="badge bg-success">เปิดใช้งาน</span>
                  @else
                    <span class="badge bg-secondary">ปิดใช้งาน</span>
                  @endif
                </td>
                <td>{{ $account->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                <td>{{ $account->last_login?->format('d/m/Y H:i') ?? '-' }}</td>
                <td class="text-end">{{ number_format($account->logs_count) }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.activity-logs.index', ['user_id' => $account->user_id]) }}">ดู log</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">Activity Log ล่าสุด</h2>
        <p class="rb-card-subtitle">ดึง log ล่าสุดจากทุกบัญชีที่เชื่อมกับเจ้าหน้าที่คนนี้ เพื่อดูภาพรวมการใช้งาน</p>
      </div>
      <span class="rb-chip">{{ number_format($recentLogs->count()) }} รายการล่าสุด</span>
    </div>

    @if($recentLogs->isEmpty())
      <div class="alert alert-info mb-0">ยังไม่พบ Activity Log สำหรับเจ้าหน้าที่คนนี้</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped align-middle" data-sortable-table>
          <thead>
            <tr>
              <th style="width:180px;">เวลา</th>
              <th style="width:170px;">Username</th>
              <th style="width:150px;">โมดูล</th>
              <th>รายละเอียด</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentLogs as $log)
              <tr>
                <td>{{ $log->timestamp?->format('d/m/Y H:i:s') ?? '-' }}</td>
                <td>{{ $log->user?->username ?? '-' }}</td>
                <td>{{ $log->module }}</td>
                <td>{{ $log->action }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</x-layouts.admin>

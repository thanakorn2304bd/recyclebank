<x-layouts.admin title="Activity Log">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Audit Trail</div>
      <h1 class="rb-page-title">Activity Log</h1>
      <p class="rb-page-subtitle">
        ตรวจสอบย้อนหลังได้ว่าใครทำอะไร เมื่อไร และในโมดูลไหนของระบบ เหมาะสำหรับติดตามการใช้งาน ตรวจสอบข้อผิดพลาด และดูประวัติการแก้ไขข้อมูล
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">ดูบัญชีผู้ใช้</a>
      <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">Log ทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['total']) }}</div>
      <div class="rb-stat-meta">ผลลัพธ์หลังใช้ตัวกรองปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">วันนี้</div>
      <div class="rb-stat-value">{{ number_format($summary['today']) }}</div>
      <div class="rb-stat-meta">รายการที่เกิดขึ้นในวันที่ {{ now()->format('d/m/Y') }}</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ผู้ใช้ที่เกี่ยวข้อง</div>
      <div class="rb-stat-value">{{ number_format($summary['users']) }}</div>
      <div class="rb-stat-meta">จำนวนบัญชีผู้ใช้ที่มีรายการในผลลัพธ์นี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">โมดูล</div>
      <div class="rb-stat-value">{{ number_format($summary['modules']) }}</div>
      <div class="rb-stat-meta">จำนวนหมวดการทำงานที่พบใน log</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและกรอง Activity Log</h2>
        <p class="rb-card-subtitle">กรองตามคำค้นหา ผู้ใช้ role โมดูล และช่วงวันที่ได้ เพื่อไล่ดูสาเหตุหรือประวัติการทำงานได้เร็วขึ้น</p>
      </div>
      @if($selectedUser)
        <span class="rb-chip">กำลังดูของ {{ $selectedUser->username }}</span>
      @elseif($q !== '' || $module !== '' || $role !== '' || $from || $to)
        <span class="rb-chip">กำลังใช้ตัวกรอง</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหา action / username / ผู้ติดต่อ / เจ้าหน้าที่">
      </div>
      <div class="col-lg-2">
        <label class="form-label">โมดูล</label>
        <select class="form-select" name="module">
          <option value="">ทุกโมดูล</option>
          @foreach($modules as $moduleOption)
            <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>{{ $moduleLabels[$moduleOption] ?? $moduleOption }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">สิทธิ์ผู้ใช้</label>
        <select class="form-select" name="role">
          <option value="">ทุกสิทธิ์</option>
          <option value="admin" @selected($role === 'admin')>ผู้ดูแลระบบ</option>
          <option value="staff" @selected($role === 'staff')>เจ้าหน้าที่</option>
          <option value="member" @selected($role === 'member')>สมาชิก</option>
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">วันที่เริ่ม</label>
        <input class="form-control" type="date" name="from" value="{{ $from }}">
      </div>
      <div class="col-lg-2">
        <label class="form-label">วันที่สิ้นสุด</label>
        <input class="form-control" type="date" name="to" value="{{ $to }}">
      </div>
      @if($userId)
        <input type="hidden" name="user_id" value="{{ $userId }}">
      @endif
      <div class="col-12 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.activity-logs.index') }}">ล้าง</a>
        <button class="btn btn-primary">ค้นหา</button>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการ Activity Log</h2>
        <p class="rb-card-subtitle">เรียงจากรายการล่าสุดลงไปด้านล่าง เพื่อให้ตามเหตุการณ์ที่เพิ่งเกิดขึ้นได้ง่าย</p>
      </div>
      <span class="rb-chip">
        {{ number_format($logs->firstItem() ?? 0) }}-{{ number_format($logs->lastItem() ?? 0) }}
        / {{ number_format($logs->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:170px;">เวลา</th>
            <th style="width:180px;">ผู้ใช้</th>
            <th style="width:130px;">สิทธิ์</th>
            <th style="width:160px;">โมดูล</th>
            <th>รายละเอียด</th>
            <th style="width:160px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td>{{ $log->timestamp?->format('d/m/Y H:i:s') ?? '-' }}</td>
              <td>
                <div class="fw-semibold">{{ $log->user?->username ?? 'ไม่พบบัญชีผู้ใช้' }}</div>
                @if($log->user?->household)
                  <div class="text-muted small">{{ $log->user->household->account_no }} - {{ $log->user->household->contact_person }}</div>
                @elseif($log->user?->staff)
                  <div class="text-muted small">{{ $log->user->staff->full_name }}</div>
                @endif
              </td>
              <td>
                @if($log->user)
                  @if($log->user->role === 'admin')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $roleLabels[$log->user->role] ?? $roleLabels['member'] }}</span>
                  @elseif($log->user->role === 'staff')
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $roleLabels[$log->user->role] ?? $roleLabels['member'] }}</span>
                  @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $roleLabels[$log->user->role] ?? $roleLabels['member'] }}</span>
                  @endif
                @else
                  <span class="badge bg-secondary">ไม่ทราบสิทธิ์</span>
                @endif
              </td>
              <td>
                <span class="badge bg-light text-dark border">{{ $moduleLabels[$log->module] ?? $log->module }}</span>
              </td>
              <td>
                <div class="fw-semibold">{{ $log->action }}</div>

                @if($log->entity_type || $log->entity_id)
                  <div class="text-muted small mt-1">
                    อ้างอิง {{ $entityLabels[$log->entity_type] ?? $log->entity_type ?? 'รายการ' }}
                    @if($log->entity_id)
                      #{{ $log->entity_id }}
                    @endif
                  </div>
                @endif

                @if($log->ip_address || $log->user_agent)
                  <div class="text-muted small mt-1">
                    @if($log->ip_address)
                      IP {{ $log->ip_address }}
                    @endif
                    @if($log->ip_address && $log->user_agent)
                      |
                    @endif
                    @if($log->user_agent)
                      {{ \Illuminate\Support\Str::limit($log->user_agent, 90) }}
                    @endif
                  </div>
                @endif

                @if($log->before_values || $log->after_values || $log->metadata)
                  <details class="mt-2">
                    <summary class="small text-primary">ดูข้อมูล audit</summary>

                    @if($log->before_values)
                      <div class="small text-muted mt-2">ก่อนแก้ไข</div>
                      <pre class="small bg-light border rounded p-2 mb-2">{{ json_encode($log->before_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif

                    @if($log->after_values)
                      <div class="small text-muted">หลังแก้ไข</div>
                      <pre class="small bg-light border rounded p-2 mb-2">{{ json_encode($log->after_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif

                    @if($log->metadata)
                      <div class="small text-muted">ข้อมูลประกอบ</div>
                      <pre class="small bg-light border rounded p-2 mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif
                  </details>
                @endif
              </td>
              <td class="text-end">
                @if($log->user)
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.activity-logs.index', ['user_id' => $log->user_id]) }}">ดูของผู้ใช้นี้</a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">ยังไม่พบประวัติการใช้งานตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $logs->links() }}
  </div>
</x-layouts.admin>

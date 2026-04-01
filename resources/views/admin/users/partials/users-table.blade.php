<div class="rb-surface p-3 p-lg-4">
  <div class="rb-section-head">
    <div>
      <h2 class="rb-card-title">รายการบัญชีผู้ใช้</h2>
      <p class="rb-card-subtitle">เปิดดูข้อมูลบัญชีและกดเข้าไปที่ Activity Log เพื่อไล่ตรวจสอบประวัติของแต่ละคนต่อได้ทันที</p>
    </div>
    <span class="rb-chip">
      {{ number_format($users->firstItem() ?? 0) }}-{{ number_format($users->lastItem() ?? 0) }}
      / {{ number_format($users->total()) }}
    </span>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle" data-sortable-table>
      <thead>
        <tr>
          <th style="width:80px;" data-sort-type="number">ID</th>
          <th style="width:180px;">Username</th>
          <th style="width:130px;">สิทธิ์</th>
          <th>ข้อมูลอ้างอิง</th>
          <th style="width:120px;">สถานะ</th>
          <th style="width:150px;">สร้างเมื่อ</th>
          <th style="width:150px;">เข้าใช้ล่าสุด</th>
          <th style="width:120px;" class="text-end" data-sort-type="number">Logs</th>
          <th style="width:150px;" data-sortable="false"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>{{ $user->user_id }}</td>
            <td class="fw-semibold">{{ $user->username }}</td>
            <td>
              @if($user->role === 'admin')
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $roleLabels[$user->role] }}</span>
              @elseif($user->role === 'staff')
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $roleLabels[$user->role] }}</span>
              @else
                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $roleLabels[$user->role] ?? $roleLabels['member'] }}</span>
              @endif
            </td>
            <td>
              @if($user->household)
                <div class="fw-semibold">{{ $user->household->account_no }} - {{ $user->household->contact_person }}</div>
                <div class="text-muted small">
                  ชุมชน {{ $user->household->community?->community_name ?? '-' }} / บ้านเลขที่ {{ $user->household->house_no }}
                </div>
              @elseif($user->staff)
                <div class="fw-semibold">{{ $user->staff->full_name }}</div>
                <div class="text-muted small">{{ $user->staff->position ?? 'เจ้าหน้าที่' }}</div>
              @else
                <span class="text-muted">ไม่มีข้อมูลผูกกับครัวเรือนหรือเจ้าหน้าที่</span>
              @endif
            </td>
            <td>
              @if($user->is_active)
                <span class="badge bg-success">เปิดใช้งาน</span>
              @else
                <span class="badge bg-secondary">ปิดใช้งาน</span>
              @endif
              @if($user->force_password_reset)
                <div class="small text-warning-emphasis mt-1">ต้องเปลี่ยนรหัสก่อนใช้งานต่อ</div>
              @endif
              @if($user->locked_until && $user->locked_until->isFuture())
                <div class="small text-danger mt-1">ล็อกถึง {{ $user->locked_until->format('d/m/Y H:i') }}</div>
              @endif
            </td>
            <td>{{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
            <td>{{ $user->last_login?->format('d/m/Y H:i') ?? '-' }}</td>
            <td class="text-end">{{ number_format($user->logs_count) }}</td>
            <td class="text-end">
              @if($user->household)
                <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('households.show', $user->household) }}">ครัวเรือน</a>
              @elseif($user->staff)
                <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('admin.staff.show', $user->staff) }}">เจ้าหน้าที่</a>
              @endif
              <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.activity-logs.index', ['user_id' => $user->user_id]) }}">ดู log</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-4">ไม่พบบัญชีผู้ใช้ตามเงื่อนไขที่ค้นหา</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $users->links() }}
</div>

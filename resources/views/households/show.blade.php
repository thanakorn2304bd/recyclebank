<x-layouts.admin title="รายละเอียดครัวเรือน">
  @php
    $isPrivileged = in_array(auth()->user()->role, ['admin', 'staff'], true);
  @endphp

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Household Profile</div>
      <h1 class="rb-page-title">รายละเอียดครัวเรือน</h1>
      <p class="rb-page-subtitle">
        บัญชี {{ $household->account_no }} | ผู้ติดต่อ {{ $household->contact_person }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('households.index') }}">กลับ</a>
      @if($isPrivileged)
        <a class="btn btn-outline-warning" href="{{ route('households.credentials.create', $household) }}">
          {{ $memberAccount ? 'รีเซ็ตรหัสผ่าน' : 'ตั้งรหัสผ่าน' }}
        </a>
        <a class="btn btn-outline-primary" href="{{ route('households.edit', $household) }}">แก้ไข</a>
      @endif
      <a class="btn btn-primary" href="{{ route('transactions.household', $household) }}">ดู statement</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดคงเหลือ</div>
      <div class="rb-stat-value">{{ number_format((float)$household->total_balance, 2) }}</div>
      <div class="rb-stat-meta">ยอดเงินสะสมล่าสุดของครัวเรือนนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">สมาชิก</div>
      <div class="rb-stat-value">{{ number_format($household->members->count()) }}</div>
      <div class="rb-stat-meta">จำนวนสมาชิกที่ผูกกับครัวเรือน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">สถานะ</div>
      <div class="rb-stat-value">
        @if($household->active_status === 'active')
          ใช้งาน
        @elseif($household->active_status === 'pending')
          รออนุมัติ
        @else
          ปิด
        @endif
      </div>
      <div class="rb-stat-meta">สถานะการใช้งานของบัญชีครัวเรือน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">บัญชีเข้าใช้</div>
      <div class="rb-stat-value">{{ $memberAccount ? 'พร้อมใช้' : 'ยังไม่มี' }}</div>
      <div class="rb-stat-meta">
        {{ $memberAccount?->username ? 'ชื่อผู้ใช้ ' . $memberAccount->username : 'ยังไม่ได้ตั้งรหัสผ่านสำหรับสมาชิก' }}
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">ข้อมูลครัวเรือน</h2>
            <p class="rb-card-subtitle">ข้อมูลบัญชี ชุมชน ผู้ติดต่อ และสถานะการใช้งาน</p>
          </div>
        </div>

        <dl class="row rb-detail-list mb-0">
          <dt class="col-sm-5 mb-3">เลขบัญชี</dt>
          <dd class="col-sm-7 mb-3">{{ $household->account_no }}</dd>

          <dt class="col-sm-5 mb-3">ชุมชน</dt>
          <dd class="col-sm-7 mb-3">{{ $household->community?->community_id }} - {{ $household->community?->community_name }}</dd>

          <dt class="col-sm-5 mb-3">บ้านเลขที่</dt>
          <dd class="col-sm-7 mb-3">{{ $household->house_no }}</dd>

          <dt class="col-sm-5 mb-3">หมู่</dt>
          <dd class="col-sm-7 mb-3">{{ $household->village_no ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">ผู้ติดต่อ</dt>
          <dd class="col-sm-7 mb-3">{{ $household->contact_person }}</dd>

          <dt class="col-sm-5 mb-3">โทรศัพท์</dt>
          <dd class="col-sm-7 mb-3">{{ $household->phone ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">วันที่สมัคร</dt>
          <dd class="col-sm-7 mb-3">{{ $household->register_date?->format('d/m/Y') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">สถานะ</dt>
          <dd class="col-sm-7 mb-3">
            @if($household->active_status === 'active')
              <span class="badge bg-success">ใช้งาน</span>
            @elseif($household->active_status === 'pending')
              <span class="badge bg-warning text-dark">รออนุมัติ</span>
            @else
              <span class="badge bg-secondary">ปิด</span>
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">สะสม (เดือน)</dt>
          <dd class="col-sm-7 mb-3">{{ $household->accumulated_months }}</dd>

          <dt class="col-sm-5 mb-3">ผู้บันทึก</dt>
          <dd class="col-sm-7 mb-3">
            @if($household->createdByUser)
              {{ $household->createdByUser->username }} ({{ $household->created_by }})
            @else
              {{ $household->created_by ?? '-' }}
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">ชื่อผู้ใช้</dt>
          <dd class="col-sm-7 mb-3">{{ $memberAccount?->username ?? 'ยังไม่ได้ตั้งรหัสผ่าน' }}</dd>

          <dt class="col-sm-5 mb-3">บัญชีเข้าใช้</dt>
          <dd class="col-sm-7 mb-3">
            @if($memberAccount)
              @if($memberAccount->is_active)
                <span class="badge bg-success">พร้อมใช้งาน</span>
              @else
                <span class="badge bg-secondary">ปิดการเข้าใช้งาน</span>
              @endif
            @else
              <span class="text-muted">ยังไม่มีบัญชีเข้าใช้</span>
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">เข้าใช้ล่าสุด</dt>
          <dd class="col-sm-7 mb-3">{{ $memberAccount?->last_login?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-0">อัปเดตล่าสุด</dt>
          <dd class="col-sm-7 mb-0">{{ $household->updated_at?->format('d/m/Y H:i') ?? '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">สมาชิกในครัวเรือน</h2>
            <p class="rb-card-subtitle">ดูรายชื่อสมาชิก ความสัมพันธ์ และผู้เป็นหัวหน้าครัวเรือน</p>
          </div>
          <span class="rb-chip">{{ $household->members->count() }} คน</span>
        </div>

        @if($household->members->isEmpty())
          <div class="rb-empty-state">ยังไม่มีสมาชิกในครัวเรือนนี้</div>
        @else
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0" data-sortable-table>
              <thead>
                <tr>
                  <th style="width:50px;" data-sort-type="number">#</th>
                  <th>ชื่อ-นามสกุล</th>
                  <th style="width:160px;">เลขบัตรประชาชน</th>
                  <th style="width:140px;">ความสัมพันธ์</th>
                  <th style="width:90px;">หัวหน้า</th>
                </tr>
              </thead>
              <tbody>
                @foreach($household->members as $index => $member)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $member->full_name }}</td>
                    <td>{{ $member->id_card ?? '-' }}</td>
                    <td>{{ $member->relation ?? '-' }}</td>
                    <td>
                      @if($member->is_head)
                        <span class="badge bg-success">หัวหน้า</span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</x-layouts.admin>

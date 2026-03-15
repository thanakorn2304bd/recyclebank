<x-layouts.admin title="รายละเอียดครัวเรือน">
  @php
    $isPrivileged = in_array(auth()->user()->role, ['admin', 'staff'], true);
  @endphp
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h3 class="mb-0">รายละเอียดครัวเรือน</h3>
      <div class="text-muted">บัญชี {{ $household->account_no }} | ผู้ติดต่อ {{ $household->contact_person }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-secondary" href="{{ route('households.index') }}">กลับ</a>
      @if($isPrivileged)
        <a class="btn btn-outline-warning" href="{{ route('households.credentials.create', $household) }}">
          {{ $memberAccount ? 'รีเซ็ตรหัสผ่าน' : 'ตั้งรหัสผ่าน' }}
        </a>
        <a class="btn btn-outline-primary" href="{{ route('households.edit', $household) }}">แก้ไข</a>
      @endif
      <a class="btn btn-outline-success" href="{{ route('transactions.household', $household) }}">ดู statement</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="bg-white p-3">
        <h5 class="mb-3">ข้อมูลครัวเรือน</h5>
        <dl class="row mb-0">
          <dt class="col-5 text-muted mb-2">เลขบัญชี</dt>
          <dd class="col-7 mb-2">{{ $household->account_no }}</dd>

          <dt class="col-5 text-muted mb-2">ชุมชน</dt>
          <dd class="col-7 mb-2">
            {{ $household->community?->community_id }} - {{ $household->community?->community_name }}
          </dd>

          <dt class="col-5 text-muted mb-2">บ้านเลขที่</dt>
          <dd class="col-7 mb-2">{{ $household->house_no }}</dd>

          <dt class="col-5 text-muted mb-2">หมู่</dt>
          <dd class="col-7 mb-2">{{ $household->village_no ?? '-' }}</dd>

          <dt class="col-5 text-muted mb-2">ผู้ติดต่อ</dt>
          <dd class="col-7 mb-2">{{ $household->contact_person }}</dd>

          <dt class="col-5 text-muted mb-2">โทรศัพท์</dt>
          <dd class="col-7 mb-2">{{ $household->phone ?? '-' }}</dd>

          <dt class="col-5 text-muted mb-2">วันที่สมัคร</dt>
          <dd class="col-7 mb-2">{{ $household->register_date?->format('d/m/Y') ?? '-' }}</dd>

          <dt class="col-5 text-muted mb-2">สถานะ</dt>
          <dd class="col-7 mb-2">
            @if($household->active_status === 'active')
              <span class="badge bg-success">ใช้งาน</span>
            @elseif($household->active_status === 'pending')
              <span class="badge bg-warning text-dark">รออนุมัติ</span>
            @else
              <span class="badge bg-secondary">ปิด</span>
            @endif
          </dd>

          <dt class="col-5 text-muted mb-2">สะสม (เดือน)</dt>
          <dd class="col-7 mb-2">{{ $household->accumulated_months }}</dd>

          <dt class="col-5 text-muted mb-2">ยอดคงเหลือ</dt>
          <dd class="col-7 mb-2"><strong>{{ number_format((float)$household->total_balance, 2) }}</strong></dd>

          <dt class="col-5 text-muted mb-2">ผู้บันทึก</dt>
          <dd class="col-7 mb-2">
            @if($household->createdByUser)
              {{ $household->createdByUser->username }} ({{ $household->created_by }})
            @else
              {{ $household->created_by ?? '-' }}
            @endif
          </dd>

          <dt class="col-5 text-muted mb-2">ชื่อผู้ใช้</dt>
          <dd class="col-7 mb-2">{{ $memberAccount?->username ?? 'ยังไม่ได้ตั้งรหัสผ่าน' }}</dd>

          <dt class="col-5 text-muted mb-2">บัญชีเข้าใช้</dt>
          <dd class="col-7 mb-2">
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

          <dt class="col-5 text-muted mb-2">เข้าใช้ล่าสุด</dt>
          <dd class="col-7 mb-2">{{ $memberAccount?->last_login?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-5 text-muted mb-0">อัปเดตล่าสุด</dt>
          <dd class="col-7 mb-0">{{ $household->updated_at?->format('d/m/Y H:i') ?? '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="bg-white p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">สมาชิกในครัวเรือน</h5>
          <span class="badge text-bg-secondary">{{ $household->members->count() }} คน</span>
        </div>

        @if($household->members->isEmpty())
          <div class="text-muted text-center py-4">ยังไม่มีสมาชิกในครัวเรือนนี้</div>
        @else
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0" data-sortable-table>
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

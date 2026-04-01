<x-layouts.admin title="ครัวเรือน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Member Directory</div>
      <h1 class="rb-page-title">ครัวเรือน</h1>
      <p class="rb-page-subtitle">
        จัดการรายชื่อสมาชิก ค้นหาบัญชี ตรวจสอบสถานะ และเปิดดูข้อมูลของแต่ละครัวเรือนได้จากหน้านี้
      </p>
    </div>
    <div class="rb-page-actions">
      @if($isPrivileged)
        <a class="btn btn-primary" href="{{ route('households.create') }}">+ เพิ่มครัวเรือน</a>
      @endif
      <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">รายการทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($households->total()) }}</div>
      <div class="rb-stat-meta">แสดง {{ number_format($households->count()) }} รายการในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($activeCount) }}</div>
      <div class="rb-stat-meta">ครัวเรือนที่พร้อมทำรายการในหน้านี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">รออนุมัติ</div>
      <div class="rb-stat-value">{{ number_format($pendingCount) }}</div>
      <div class="rb-stat-meta">คำขอสมาชิกที่ยังรอตรวจสอบ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ปิดใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($inactiveCount) }}</div>
      <div class="rb-stat-meta">บัญชีที่ยังไม่เปิดใช้งานหรือถูกระงับ</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและกรองข้อมูล</h2>
        <p class="rb-card-subtitle">ค้นหาจากเลขบัญชี บ้านเลขที่ ผู้ติดต่อ สมาชิกในครัวเรือน โทรศัพท์ ชุมชน หรือสถานะ</p>
      </div>
      @if($q || $communityId || $status)
        <span class="rb-chip">กำลังใช้ตัวกรอง</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-5">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาเลขบัญชี / บ้านเลขที่ / ผู้ติดต่อ / สมาชิก / โทรศัพท์">
      </div>
      <div class="col-lg-3">
        <label class="form-label">ชุมชน</label>
        <select class="form-select" name="community_id">
          <option value="">ทุกชุมชน</option>
          @foreach($communities as $c)
            <option value="{{ $c->community_id }}" @selected((string)$communityId === (string)$c->community_id)>
              {{ $c->community_id }} - {{ $c->community_name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="status">
          <option value="">ทุกสถานะ</option>
          <option value="pending" @selected($status === 'pending')>รออนุมัติ</option>
          <option value="active" @selected($status === 'active')>ใช้งาน</option>
          <option value="inactive" @selected($status === 'inactive')>ปิด</option>
        </select>
      </div>
      <div class="col-lg-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100">ค้นหา</button>
        <a class="btn btn-outline-secondary w-100" href="{{ route('households.index') }}">ล้าง</a>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการครัวเรือน</h2>
        <p class="rb-card-subtitle">กดหัวตารางเพื่อเรียงลำดับข้อมูลและเปิดดูรายละเอียดหรือแก้ไขต่อได้ทันที</p>
      </div>
      <span class="rb-chip">
        {{ number_format($households->firstItem() ?? 0) }}-{{ number_format($households->lastItem() ?? 0) }}
        / {{ number_format($households->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:80px;" data-sort-type="number">ID</th>
            <th style="width:140px;">เลขบัญชี</th>
            <th>ชุมชน</th>
            <th style="width:120px;">บ้านเลขที่</th>
            <th style="width:90px;">หมู่</th>
            <th>ผู้ติดต่อ</th>
            <th style="width:140px;">โทรศัพท์</th>
            <th style="width:110px;">สถานะ</th>
            <th style="width:130px;" class="text-end" data-sort-type="number">ยอดคงเหลือ</th>
            <th style="width:320px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($households as $h)
            <tr>
              <td>{{ $h->household_id }}</td>
              <td>{{ $h->account_no }}</td>
              <td>{{ $h->community?->community_id }} - {{ $h->community?->community_name }}</td>
              <td>{{ $h->house_no }}</td>
              <td>{{ $h->village_no ?? '-' }}</td>
              <td>{{ $h->contact_person }}</td>
              <td>{{ $h->phone ?? '-' }}</td>
              <td>
                @if($h->active_status === 'active')
                  <span class="badge bg-success">ใช้งาน</span>
                @elseif($h->active_status === 'pending')
                  <span class="badge bg-warning text-dark">รออนุมัติ</span>
                @else
                  <span class="badge bg-secondary">ปิด</span>
                @endif
              </td>
              <td class="text-end">{{ number_format((float) $h->total_balance, 2) }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('households.show', $h) }}">ดูรายละเอียด</a>
                @if($isPrivileged)
                  <a class="btn btn-sm btn-outline-warning me-1" href="{{ route('households.credentials.create', $h) }}">รหัสผ่าน</a>
                  <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('households.edit', $h) }}">แก้ไข</a>
                  <form class="d-inline" method="POST" action="{{ route('households.destroy', $h) }}" onsubmit="return confirm('ลบครัวเรือนนี้?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">ลบ</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center text-muted py-4">ไม่พบข้อมูลครัวเรือนตามเงื่อนไขที่ค้นหา</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $households->links() }}
  </div>
</x-layouts.admin>

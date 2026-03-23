<x-layouts.admin title="ข้อมูลเจ้าหน้าที่">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Staff Directory</div>
      <h1 class="rb-page-title">ข้อมูลเจ้าหน้าที่</h1>
      <p class="rb-page-subtitle">
        ดูรายชื่อเจ้าหน้าที่ ตำแหน่ง เบอร์โทร และบัญชีผู้ใช้ที่ผูกไว้จากหน้าเดียว พร้อมค้นหาและเปิดดูรายละเอียดรายคนได้ทันที
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">ดูบัญชีผู้ใช้</a>
      <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">เจ้าหน้าที่ทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($summary['total']) }}</div>
      <div class="rb-stat-meta">ผลลัพธ์หลังใช้ตัวกรองปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">มีบัญชีผู้ใช้</div>
      <div class="rb-stat-value">{{ number_format($summary['with_accounts']) }}</div>
      <div class="rb-stat-meta">เจ้าหน้าที่ที่มีบัญชีเชื่อมกับระบบแล้ว</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยังไม่มีบัญชี</div>
      <div class="rb-stat-value">{{ number_format($summary['without_accounts']) }}</div>
      <div class="rb-stat-meta">เจ้าหน้าที่ที่ยังไม่ได้ผูกบัญชีเข้าสู่ระบบ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">บัญชีที่ใช้งานได้</div>
      <div class="rb-stat-value">{{ number_format($summary['active_accounts']) }}</div>
      <div class="rb-stat-meta">จำนวนเจ้าหน้าที่ที่มีบัญชีเปิดใช้งานอย่างน้อย 1 บัญชี</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและกรองเจ้าหน้าที่</h2>
        <p class="rb-card-subtitle">ค้นหาจากชื่อ เบอร์โทร ตำแหน่ง หรือ username แล้วกรองตามตำแหน่งและสถานะการผูกบัญชี</p>
      </div>
      @if($q !== '' || $position !== '' || $status !== '')
        <span class="rb-chip">กำลังใช้ตัวกรอง</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-5">
        <label class="form-label">คำค้นหา</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาชื่อเจ้าหน้าที่ / เบอร์โทร / username">
      </div>
      <div class="col-lg-4">
        <label class="form-label">ตำแหน่ง</label>
        <select class="form-select" name="position">
          <option value="">ทุกตำแหน่ง</option>
          @foreach($positions as $positionOption)
            <option value="{{ $positionOption }}" @selected($position === $positionOption)>{{ $positionOption }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-3">
        <label class="form-label">สถานะบัญชี</label>
        <select class="form-select" name="status">
          <option value="">ทุกสถานะ</option>
          <option value="active" @selected($status === 'active')>มีบัญชีเปิดใช้งาน</option>
          <option value="inactive" @selected($status === 'inactive')>มีบัญชีปิดใช้งาน</option>
          <option value="no_account" @selected($status === 'no_account')>ยังไม่มีบัญชี</option>
        </select>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">ล้าง</a>
        <button class="btn btn-primary">ค้นหา</button>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการเจ้าหน้าที่</h2>
        <p class="rb-card-subtitle">แสดงข้อมูลพื้นฐานพร้อมบัญชีผู้ใช้ที่เชื่อมไว้ เพื่อเปิดดูรายละเอียดต่อได้เร็ว</p>
      </div>
      <span class="rb-chip">
        {{ number_format($staffMembers->firstItem() ?? 0) }}-{{ number_format($staffMembers->lastItem() ?? 0) }}
        / {{ number_format($staffMembers->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:90px;" data-sort-type="number">ID</th>
            <th style="width:220px;">ชื่อเจ้าหน้าที่</th>
            <th style="width:180px;">ตำแหน่ง</th>
            <th style="width:160px;">เบอร์โทร</th>
            <th>บัญชีผู้ใช้ที่เชื่อม</th>
            <th style="width:170px;">สถานะ</th>
            <th style="width:140px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($staffMembers as $staff)
            <tr>
              <td>{{ $staff->staff_id }}</td>
              <td class="fw-semibold">{{ $staff->full_name }}</td>
              <td>{{ $staff->position ?: 'เจ้าหน้าที่' }}</td>
              <td>{{ $staff->phone ?: '-' }}</td>
              <td>
                @forelse($staff->userAccounts as $account)
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="fw-semibold">{{ $account->username }}</span>
                    @if($account->is_active)
                      <span class="badge bg-success-subtle text-success border border-success-subtle">ใช้งานได้</span>
                    @else
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">ปิดใช้งาน</span>
                    @endif
                  </div>
                @empty
                  <span class="text-muted">ยังไม่มีบัญชีผู้ใช้ที่เชื่อม</span>
                @endforelse
              </td>
              <td>
                @if($staff->user_accounts_count === 0)
                  <span class="badge bg-secondary">ยังไม่มีบัญชี</span>
                @elseif($staff->active_accounts_count > 0)
                  <span class="badge bg-success">มีบัญชีเปิดใช้งาน</span>
                @else
                  <span class="badge bg-warning text-dark">มีแต่บัญชีปิดใช้งาน</span>
                @endif
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.staff.show', $staff) }}">ดูรายละเอียด</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">ยังไม่พบข้อมูลเจ้าหน้าที่ตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $staffMembers->links() }}
  </div>
</x-layouts.admin>

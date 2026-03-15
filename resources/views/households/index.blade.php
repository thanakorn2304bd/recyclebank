<x-layouts.admin title="ครัวเรือน">
  @php
    $isPrivileged = in_array(auth()->user()->role, ['admin', 'staff'], true);
  @endphp
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ครัวเรือน</h3>
    @if($isPrivileged)
      <a class="btn btn-primary" href="{{ route('households.create') }}">+ เพิ่มครัวเรือน</a>
    @endif
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-5">
      <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาเลขบัญชี/บ้านเลขที่/ผู้ติดต่อ/โทรศัพท์...">
    </div>
    <div class="col-md-3">
      <select class="form-select" name="community_id">
        <option value="">ทุกชุมชน</option>
        @foreach($communities as $c)
          <option value="{{ $c->community_id }}" @selected((string)$communityId === (string)$c->community_id)>
            {{ $c->community_id }} - {{ $c->community_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="status">
        <option value="">ทุกสถานะ</option>
        <option value="pending" @selected($status === 'pending')>รออนุมัติ</option>
        <option value="active" @selected($status === 'active')>ใช้งาน</option>
        <option value="inactive" @selected($status === 'inactive')>ปิด</option>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-outline-primary w-100">ค้นหา</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('households.index') }}">ล้าง</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped bg-white">
      <thead>
        <tr>
          <th style="width:80px;">ID</th>
          <th style="width:140px;">เลขบัญชี</th>
          <th>ชุมชน</th>
          <th style="width:120px;">บ้านเลขที่</th>
          <th style="width:90px;">หมู่</th>
          <th>ผู้ติดต่อ</th>
          <th style="width:140px;">โทรศัพท์</th>
          <th style="width:110px;">สถานะ</th>
          <th style="width:130px;" class="text-end">ยอดคงเหลือ</th>
          <th style="width:240px;"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($households as $h)
          <tr>
            <td>{{ $h->household_id }}</td>
            <td>{{ $h->account_no }}</td>
            <td>
              {{ $h->community?->community_id }} - {{ $h->community?->community_name }}
            </td>
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
            <td class="text-end">{{ number_format((float)$h->total_balance, 2) }}</td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('households.show', $h) }}">ดูรายละเอียด</a>
              @if($isPrivileged)
                <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('households.edit', $h) }}">แก้ไข</a>
                <form class="d-inline" method="POST" action="{{ route('households.destroy', $h) }}"
                      onsubmit="return confirm('ลบครัวเรือนนี้?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">ลบ</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center text-muted">ไม่พบข้อมูลครัวเรือน</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $households->links() }}
</x-layouts.admin>

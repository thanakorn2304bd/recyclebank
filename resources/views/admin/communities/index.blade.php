<x-layouts.admin title="ชุมชน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Community Management</div>
      <h1 class="rb-page-title">ชุมชน</h1>
      <p class="rb-page-subtitle">จัดการข้อมูลชุมชนที่ใช้เป็นข้อมูลพื้นฐานในการลงทะเบียนครัวเรือน</p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-primary" href="{{ route('admin.communities.create') }}">+ เพิ่มชุมชน</a>
      <a class="btn btn-outline-secondary" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">ชุมชนทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($communities->total()) }}</div>
      <div class="rb-stat-meta">ชุมชนที่ลงทะเบียนในระบบ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ครัวเรือนรวม</div>
      <div class="rb-stat-value">{{ number_format($communities->sum('households_count')) }}</div>
      <div class="rb-stat-meta">จากชุมชนที่แสดงในหน้านี้</div>
    </div>
  </div>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head mb-3">
      <div>
        <h2 class="rb-card-title">รายการชุมชน</h2>
        <p class="rb-card-subtitle">คลิก "แก้ไข" เพื่อเปลี่ยนชื่อชุมชน หรือ "ลบ" หากยังไม่มีครัวเรือนในชุมชนนั้น</p>
      </div>
      <span class="rb-chip">{{ number_format($communities->firstItem() ?? 0) }}-{{ number_format($communities->lastItem() ?? 0) }} / {{ number_format($communities->total()) }}</span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:100px;">รหัส</th>
            <th>ชื่อชุมชน</th>
            <th class="text-center" style="width:120px;">ครัวเรือน</th>
            <th style="width:160px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($communities as $community)
            <tr>
              <td><span class="badge bg-secondary font-monospace">{{ $community->community_id }}</span></td>
              <td>{{ $community->community_name }}</td>
              <td class="text-center text-muted">{{ number_format($community->households_count) }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.communities.edit', $community) }}">แก้ไข</a>
                <form class="d-inline" method="POST" action="{{ route('admin.communities.destroy', $community) }}"
                      onsubmit="return confirm('ลบชุมชน {{ $community->community_name }}?\nไม่สามารถลบได้หากมีครัวเรือนอยู่')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger" {{ $community->households_count > 0 ? 'disabled' : '' }}>ลบ</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">ยังไม่มีชุมชนในระบบ</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $communities->links() }}
  </div>
</x-layouts.admin>

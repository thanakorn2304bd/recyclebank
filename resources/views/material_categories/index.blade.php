<x-layouts.admin title="หมวดวัสดุ">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Material Categories</div>
      <h1 class="rb-page-title">หมวดวัสดุ</h1>
      <p class="rb-page-subtitle">
        จัดกลุ่มวัสดุให้ค้นหาและดูรายงานได้ง่ายขึ้น โดยแต่ละหมวดสามารถนำไปใช้กับรายการวัสดุและสรุปรายงานต่อได้ทันที
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-primary" href="{{ route('material-categories.create') }}">+ เพิ่มหมวด</a>
      <a class="btn btn-outline-secondary" href="{{ route('materials.index') }}">ดูรายการวัสดุ</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">หมวดทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($categories->total()) }}</div>
      <div class="rb-stat-meta">แสดง {{ number_format($categories->count()) }} รายการในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">พร้อมใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($categories->count()) }}</div>
      <div class="rb-stat-meta">หมวดที่เปิดให้ใช้งานในหน้านี้</div>
    </div>
  </div>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการหมวดวัสดุ</h2>
        <p class="rb-card-subtitle">ใช้สำหรับตรวจสอบชื่อหมวด แก้ไขข้อมูล หรือจัดระเบียบวัสดุภายในระบบ</p>
      </div>
      <span class="rb-chip">
        {{ number_format($categories->firstItem() ?? 0) }}-{{ number_format($categories->lastItem() ?? 0) }}
        / {{ number_format($categories->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:120px;">ID</th>
            <th>ชื่อหมวด</th>
            <th style="width:200px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($categories as $c)
            <tr>
              <td>{{ $c->category_id }}</td>
              <td>{{ $c->category_name }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('material-categories.edit', $c) }}">แก้ไข</a>
                <form class="d-inline" method="POST" action="{{ route('material-categories.destroy', $c) }}" onsubmit="return confirm('ลบหมวดนี้?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">ลบ</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted py-4">ยังไม่มีหมวดวัสดุในระบบ</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $categories->links() }}
  </div>
</x-layouts.admin>

<x-layouts.admin title="วัสดุ">
  @php
    $pageMaterials = $materials->getCollection();
    $activeCount = $pageMaterials->where('is_active', true)->count();
    $inactiveCount = $pageMaterials->where('is_active', false)->count();
  @endphp

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Materials Catalog</div>
      <h1 class="rb-page-title">วัสดุ</h1>
      <p class="rb-page-subtitle">
        จัดการรายการวัสดุ แยกตามหมวดหมู่ ตรวจสอบสถานะการใช้งาน และเปิดดูประวัติราคาของแต่ละวัสดุได้จากจุดเดียว
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-primary" href="{{ route('materials.create') }}">+ เพิ่มวัสดุ</a>
      <a class="btn btn-outline-secondary" href="{{ route('material-categories.index') }}">ดูหมวดวัสดุ</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">วัสดุทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($materials->total()) }}</div>
      <div class="rb-stat-meta">ในระบบทั้งหมด {{ number_format($categories->count()) }} หมวด</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">พร้อมใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($activeCount) }}</div>
      <div class="rb-stat-meta">วัสดุที่ใช้งานได้ในหน้าปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ปิดใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($inactiveCount) }}</div>
      <div class="rb-stat-meta">วัสดุที่ยังไม่เปิดให้ใช้งาน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">กำลังแสดง</div>
      <div class="rb-stat-value">{{ number_format($materials->count()) }}</div>
      <div class="rb-stat-meta">รายการตามตัวกรองในหน้าปัจจุบัน</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาและจัดเรียง</h2>
        <p class="rb-card-subtitle">เลือกหมวด คำค้นหา และลำดับการเรียงเพื่อหาแต่ละวัสดุได้เร็วขึ้น</p>
      </div>
      @if($q || $categoryId || $sort || $dir !== 'asc')
        <span class="rb-chip">กำลังใช้ตัวกรอง</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label">ชื่อวัสดุ</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาชื่อวัสดุ">
      </div>
      <div class="col-lg-3">
        <label class="form-label">หมวด</label>
        <select class="form-select" name="category_id">
          <option value="">ทุกหมวด</option>
          @foreach($categories as $c)
            <option value="{{ $c->category_id }}" @selected((string)$categoryId === (string)$c->category_id)>
              {{ $c->category_name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-3">
        <label class="form-label">เรียงลำดับ</label>
        <div class="input-group">
          <select class="form-select" name="sort">
            <option value="">ค่าเริ่มต้น</option>
            <option value="id" @selected($sort === 'id')>ID</option>
            <option value="name" @selected($sort === 'name')>ชื่อวัสดุ</option>
            <option value="category" @selected($sort === 'category')>หมวด</option>
            <option value="unit" @selected($sort === 'unit')>หน่วย</option>
            <option value="status" @selected($sort === 'status')>สถานะ</option>
          </select>
          <select class="form-select" name="dir">
            <option value="asc" @selected($dir === 'asc')>น้อย → มาก</option>
            <option value="desc" @selected($dir === 'desc')>มาก → น้อย</option>
          </select>
        </div>
      </div>
      <div class="col-lg-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100">ค้นหา</button>
        <a class="btn btn-outline-secondary w-100" href="{{ route('materials.index') }}">ล้าง</a>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการวัสดุ</h2>
        <p class="rb-card-subtitle">เปิดดูราคาย้อนหลัง แก้ไขข้อมูล หรือปิดใช้งานวัสดุที่ไม่ต้องการใช้ต่อได้จากตารางนี้</p>
      </div>
      <span class="rb-chip">
        {{ number_format($materials->firstItem() ?? 0) }}-{{ number_format($materials->lastItem() ?? 0) }}
        / {{ number_format($materials->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th style="width:90px;" data-sort-type="number">ID</th>
            <th>ชื่อวัสดุ</th>
            <th>หมวด</th>
            <th style="width:90px;">หน่วย</th>
            <th style="width:110px;">สถานะ</th>
            <th style="width:260px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($materials as $m)
            <tr>
              <td>{{ $m->material_id }}</td>
              <td>{{ $m->material_name }}</td>
              <td>{{ $m->category?->category_name }}</td>
              <td>{{ $m->unit }}</td>
              <td>
                @if($m->is_active)
                  <span class="badge bg-success">ใช้งาน</span>
                @else
                  <span class="badge bg-secondary">ปิด</span>
                @endif
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('materials.prices', $m) }}">ดูราคา</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('materials.edit', $m) }}">แก้ไข</a>
                <form class="d-inline" method="POST" action="{{ route('materials.destroy', $m) }}" onsubmit="return confirm('ลบวัสดุนี้?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">ลบ</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">ไม่พบวัสดุตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $materials->links() }}
  </div>
</x-layouts.admin>

<x-layouts.admin title="วัสดุ">
  @php
    $pageMaterials = $materials->getCollection();
    $activeCount = $pageMaterials->where('is_active', true)->count();
    $inactiveCount = $pageMaterials->where('is_active', false)->count();
    $sortUrl = function (string $column) use ($sort, $dir) {
        return route('materials.index', array_merge(
            request()->except('page'),
            [
                'sort' => $column,
                'dir' => $sort === $column && $dir === 'asc' ? 'desc' : 'asc',
            ],
        ));
    };
    $sortIndicator = function (string $column) use ($sort, $dir) {
        if ($sort !== $column) {
            return '↕';
        }

        return $dir === 'asc' ? '▲' : '▼';
    };
    $sortAria = function (string $column) use ($sort, $dir) {
        if ($sort !== $column) {
            return 'none';
        }

        return $dir === 'asc' ? 'ascending' : 'descending';
    };
  @endphp

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Materials Catalog</div>
      <h1 class="rb-page-title">วัสดุ</h1>
      <p class="rb-page-subtitle">
        ศูนย์รวมสำหรับจัดการวัสดุ หมวดหมู่ และดูราคาปัจจุบันของแต่ละรายการได้จากจุดเดียว
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-primary" href="{{ route('materials.create') }}">+ เพิ่มวัสดุ</a>
      <a class="btn btn-outline-secondary" href="{{ route('material-categories.index') }}">จัดการหมวด</a>
      <a class="btn btn-outline-secondary" href="{{ route('material-prices.index') }}">จัดการราคา</a>
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
            <option value="price" @selected($sort === 'price')>ราคา</option>
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
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th style="width:90px;" @class(['rb-sort-active' => $sort === 'id']) aria-sort="{{ $sortAria('id') }}">
              <a class="rb-sort-button" href="{{ $sortUrl('id') }}">
                <span class="rb-sort-label">ID</span>
                <span class="rb-sort-indicator" aria-hidden="true">{{ $sortIndicator('id') }}</span>
              </a>
            </th>
            <th @class(['rb-sort-active' => $sort === 'name']) aria-sort="{{ $sortAria('name') }}">
              <a class="rb-sort-button" href="{{ $sortUrl('name') }}">
                <span class="rb-sort-label">ชื่อวัสดุ</span>
                <span class="rb-sort-indicator" aria-hidden="true">{{ $sortIndicator('name') }}</span>
              </a>
            </th>
            <th @class(['rb-sort-active' => $sort === 'category']) aria-sort="{{ $sortAria('category') }}">
              <a class="rb-sort-button" href="{{ $sortUrl('category') }}">
                <span class="rb-sort-label">หมวด</span>
                <span class="rb-sort-indicator" aria-hidden="true">{{ $sortIndicator('category') }}</span>
              </a>
            </th>
            <th style="width:90px;" @class(['rb-sort-active' => $sort === 'unit']) aria-sort="{{ $sortAria('unit') }}">
              <a class="rb-sort-button" href="{{ $sortUrl('unit') }}">
                <span class="rb-sort-label">หน่วย</span>
                <span class="rb-sort-indicator" aria-hidden="true">{{ $sortIndicator('unit') }}</span>
              </a>
            </th>
            <th style="width:160px;" @class(['text-end', 'rb-sort-active' => $sort === 'price']) aria-sort="{{ $sortAria('price') }}">
              <a class="rb-sort-button justify-content-end" href="{{ $sortUrl('price') }}">
                <span class="rb-sort-label">ราคา</span>
                <span class="rb-sort-indicator" aria-hidden="true">{{ $sortIndicator('price') }}</span>
              </a>
            </th>
            <th style="width:110px;" @class(['rb-sort-active' => $sort === 'status']) aria-sort="{{ $sortAria('status') }}">
              <a class="rb-sort-button" href="{{ $sortUrl('status') }}">
                <span class="rb-sort-label">สถานะ</span>
                <span class="rb-sort-indicator" aria-hidden="true">{{ $sortIndicator('status') }}</span>
              </a>
            </th>
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
              <td class="text-end">
                @if($m->current_price !== null)
                  <div class="fw-semibold">{{ number_format((float) $m->current_price, 2) }}</div>
                  <div class="text-muted small">บาท/{{ $m->unit }}</div>
                @else
                  <span class="text-muted">ยังไม่มีราคา</span>
                @endif
              </td>
              <td>
                @if($m->is_active)
                  <span class="badge bg-success">ใช้งาน</span>
                @else
                  <span class="badge bg-secondary">ปิด</span>
                @endif
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('material-prices.index', ['material_id' => $m->material_id]) }}">แก้ราคา</a>
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
              <td colspan="7" class="text-center text-muted py-4">ไม่พบวัสดุตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $materials->links() }}
  </div>
</x-layouts.admin>

<x-layouts.admin title="วัสดุ">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">วัสดุ</h3>
    <a class="btn btn-primary" href="{{ route('materials.create') }}">+ เพิ่มวัสดุ</a>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-4">
      <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาชื่อวัสดุ...">
    </div>
    <div class="col-md-3">
      <select class="form-select" name="category_id">
        <option value="">ทุกหมวด</option>
        @foreach($categories as $c)
          <option value="{{ $c->category_id }}" @selected((string)$categoryId === (string)$c->category_id)>
            {{ $c->category_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <div class="input-group">
        <select class="form-select" name="sort">
          <option value="">เรียงตาม (ค่าเริ่มต้น)</option>
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
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-outline-primary w-100">ค้นหา</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('materials.index') }}">ล้าง</a>
    </div>
  </form>

  <table class="table table-striped bg-white">
    @php
      $baseQuery = request()->except('page');
      $sortUrl = function (string $key) use ($baseQuery, $sort, $dir) {
        $nextDir = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
        $query = array_merge($baseQuery, ['sort' => $key, 'dir' => $nextDir]);
        $qs = http_build_query($query);
        return url()->current() . ($qs ? ('?' . $qs) : '');
      };
      $sortIcon = function (string $key) use ($sort, $dir) {
        if ($sort !== $key) return '';
        return $dir === 'asc' ? ' ▲' : ' ▼';
      };
    @endphp
    <thead>
      <tr>
        <th style="width:90px;">
          <a class="text-decoration-none text-reset" href="{{ $sortUrl('id') }}">ID{{ $sortIcon('id') }}</a>
        </th>
        <th>
          <a class="text-decoration-none text-reset" href="{{ $sortUrl('name') }}">ชื่อวัสดุ{{ $sortIcon('name') }}</a>
        </th>
        <th>
          <a class="text-decoration-none text-reset" href="{{ $sortUrl('category') }}">หมวด{{ $sortIcon('category') }}</a>
        </th>
        <th style="width:90px;">
          <a class="text-decoration-none text-reset" href="{{ $sortUrl('unit') }}">หน่วย{{ $sortIcon('unit') }}</a>
        </th>
        <th style="width:110px;">
          <a class="text-decoration-none text-reset" href="{{ $sortUrl('status') }}">สถานะ{{ $sortIcon('status') }}</a>
        </th>
        <th style="width:260px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($materials as $m)
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
            <form class="d-inline" method="POST" action="{{ route('materials.destroy', $m) }}"
                  onsubmit="return confirm('ลบวัสดุนี้?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">ลบ</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $materials->links() }}
</x-layouts.admin>

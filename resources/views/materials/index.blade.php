<x-layouts.admin title="วัสดุ">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">วัสดุ</h3>
    <a class="btn btn-primary" href="{{ route('materials.create') }}">+ เพิ่มวัสดุ</a>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-5">
      <input class="form-control" name="q" value="{{ $q }}" placeholder="ค้นหาชื่อวัสดุ...">
    </div>
    <div class="col-md-4">
      <select class="form-select" name="category_id">
        <option value="">ทุกหมวด</option>
        @foreach($categories as $c)
          <option value="{{ $c->category_id }}" @selected((string)$categoryId === (string)$c->category_id)>
            {{ $c->category_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-outline-primary w-100">ค้นหา</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('materials.index') }}">ล้าง</a>
    </div>
  </form>

  <table class="table table-striped bg-white">
    <thead>
      <tr>
        <th style="width:90px;">ID</th>
        <th>ชื่อวัสดุ</th>
        <th>หมวด</th>
        <th style="width:90px;">หน่วย</th>
        <th style="width:110px;">สถานะ</th>
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

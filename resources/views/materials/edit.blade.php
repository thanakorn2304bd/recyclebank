<x-layouts.admin title="แก้ไขวัสดุ">
  <h3 class="mb-3">แก้ไขวัสดุ</h3>

  <form method="POST" action="{{ route('materials.update', $material) }}" class="bg-white p-3 rounded">
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">หมวด</label>
      <select class="form-select" name="category_id" required>
        @foreach($categories as $c)
          <option value="{{ $c->category_id }}" @selected(old('category_id', $material->category_id) == $c->category_id)>
            {{ $c->category_name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">ชื่อวัสดุ</label>
      <input class="form-control" name="material_name" value="{{ old('material_name', $material->material_name) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">หน่วย</label>
      <input class="form-control" name="unit" value="{{ old('unit', $material->unit) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">รายละเอียด</label>
      <input class="form-control" name="description" value="{{ old('description', $material->description) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">สถานะ</label>
      <select class="form-select" name="is_active" required>
        <option value="1" @selected((string)old('is_active', $material->is_active) === '1')>ใช้งาน</option>
        <option value="0" @selected((string)old('is_active', $material->is_active) === '0')>ปิด</option>
      </select>
    </div>

    <button class="btn btn-primary">บันทึก</button>
    <a class="btn btn-secondary" href="{{ route('materials.index') }}">ยกเลิก</a>
  </form>
</x-layouts.admin>

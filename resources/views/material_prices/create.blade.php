<x-layouts.admin title="เพิ่มราคา">
  <h3 class="mb-3">เพิ่มราคาวัสดุ</h3>

  <form method="POST" action="{{ route('material-prices.store') }}" class="bg-white p-3 rounded">
    @csrf

    <div class="mb-3">
      <label class="form-label">วัสดุ</label>
      <select class="form-select" name="material_id" required>
        @foreach($materials as $m)
          <option value="{{ $m->material_id }}" @selected((string)old('material_id', $materialId) === (string)$m->material_id)>
            {{ $m->material_name }}
          </option>
        @endforeach
      </select>
      <div class="form-text">หมายเหตุ: เมื่อเพิ่มราคาใหม่ ระบบจะ “ปิดราคาเดิม” ให้อัตโนมัติ (expired_date = วันก่อนเริ่มใช้ราคาใหม่)</div>
    </div>

    <div class="mb-3">
      <label class="form-label">ราคา</label>
      <input class="form-control" type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">เริ่มใช้ (effective_date)</label>
      <input class="form-control" type="date" name="effective_date" value="{{ old('effective_date', now()->toDateString()) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">หมดอายุ (expired_date) (เว้นว่างได้)</label>
      <input class="form-control" type="date" name="expired_date" value="{{ old('expired_date') }}">
    </div>

    <button class="btn btn-primary">บันทึก</button>
    <a class="btn btn-secondary" href="{{ route('material-prices.index') }}">ยกเลิก</a>
  </form>
</x-layouts.admin>

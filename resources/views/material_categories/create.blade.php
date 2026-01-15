<x-layouts.admin title="เพิ่มหมวดวัสดุ">
  <h3 class="mb-3">เพิ่มหมวดวัสดุ</h3>

  <form method="POST" action="{{ route('material-categories.store') }}" class="bg-white p-3 rounded">
    @csrf
    <div class="mb-3">
      <label class="form-label">ชื่อหมวด</label>
      <input class="form-control" name="category_name" value="{{ old('category_name') }}" required>
    </div>

    <button class="btn btn-primary">บันทึก</button>
    <a class="btn btn-secondary" href="{{ route('material-categories.index') }}">ยกเลิก</a>
  </form>
</x-layouts.admin>

<x-layouts.admin title="แก้ไขหมวดวัสดุ">
  <h3 class="mb-3">แก้ไขหมวดวัสดุ</h3>

  <form method="POST" action="{{ route('material-categories.update', $category) }}" class="bg-white p-3 rounded">
    @csrf @method('PUT')
    <div class="mb-3">
      <label class="form-label">ชื่อหมวด</label>
      <input class="form-control" name="category_name" value="{{ old('category_name', $category->category_name) }}" required>
    </div>

    <button class="btn btn-primary">บันทึก</button>
    <a class="btn btn-secondary" href="{{ route('material-categories.index') }}">ยกเลิก</a>
  </form>
</x-layouts.admin>

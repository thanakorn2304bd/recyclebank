<form class="rb-surface p-4 mb-4">
  <div class="rb-section-head">
    <div>
      <h2 class="rb-card-title">ค้นหาและกรองก่อนแก้</h2>
      <p class="rb-card-subtitle">เลือกเฉพาะหมวดหรือวัสดุที่ต้องการแก้ จะช่วยให้บันทึกราคาหลายรายการได้เร็วขึ้นและพลาดน้อยลง</p>
    </div>
    @if($q !== '' || $categoryId || $materialId)
      <span class="rb-chip">กำลังใช้ตัวกรอง</span>
    @endif
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <label class="form-label">ค้นหาชื่อวัสดุ</label>
      <input class="form-control" name="q" value="{{ $q }}" placeholder="พิมพ์ชื่อวัสดุที่ต้องการแก้ราคา">
    </div>
    <div class="col-lg-3">
      <label class="form-label">หมวดวัสดุ</label>
      <select class="form-select" name="category_id">
        <option value="">ทุกหมวด</option>
        @foreach($categories as $category)
          <option value="{{ $category->category_id }}" @selected((string) $categoryId === (string) $category->category_id)>
            {{ $category->category_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-lg-3">
      <label class="form-label">วัสดุ</label>
      <select class="form-select" name="material_id">
        <option value="">ทุกวัสดุ</option>
        @foreach($materialOptions as $materialOption)
          <option value="{{ $materialOption->material_id }}" @selected((string) $materialId === (string) $materialOption->material_id)>
            {{ $materialOption->material_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-lg-2 d-flex align-items-end gap-2">
      <button class="btn btn-primary w-100">กรอง</button>
      <a class="btn btn-outline-secondary w-100" href="{{ route('material-prices.index') }}">ล้าง</a>
    </div>
  </div>
</form>

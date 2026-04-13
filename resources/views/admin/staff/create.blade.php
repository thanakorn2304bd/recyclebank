<x-layouts.admin title="เพิ่มเจ้าหน้าที่">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Staff Management</div>
      <h1 class="rb-page-title">เพิ่มเจ้าหน้าที่</h1>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">ยกเลิก</a>
    </div>
  </div>

  <div class="rb-surface p-4" style="max-width:480px;">
    <form method="POST" action="{{ route('admin.staff.store') }}">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-semibold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
        <input class="form-control @error('full_name') is-invalid @enderror"
               name="full_name" value="{{ old('full_name') }}"
               maxlength="100" placeholder="เช่น สมชาย ใจดี" required>
        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">เบอร์โทร</label>
        <input class="form-control @error('phone') is-invalid @enderror"
               name="phone" value="{{ old('phone') }}"
               maxlength="20" placeholder="เช่น 081-234-5678">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">ตำแหน่ง</label>
        <input class="form-control @error('position') is-invalid @enderror"
               name="position" value="{{ old('position') }}"
               maxlength="50" placeholder="เช่น เจ้าหน้าที่รับซื้อ">
        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn btn-primary">บันทึก</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">ยกเลิก</a>
    </form>
  </div>
</x-layouts.admin>

<x-layouts.admin title="แก้ไขข้อมูลเจ้าหน้าที่">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Staff Management</div>
      <h1 class="rb-page-title">แก้ไขข้อมูลเจ้าหน้าที่</h1>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.staff.show', $staff) }}">ยกเลิก</a>
    </div>
  </div>

  <div class="rb-surface p-4" style="max-width:480px;">
    <form method="POST" action="{{ route('admin.staff.update', $staff) }}">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label fw-semibold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
        <input class="form-control @error('full_name') is-invalid @enderror"
               name="full_name" value="{{ old('full_name', $staff->full_name) }}"
               maxlength="100" required>
        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">เบอร์โทร</label>
        <input class="form-control @error('phone') is-invalid @enderror"
               name="phone" value="{{ old('phone', $staff->phone) }}"
               maxlength="20">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">ตำแหน่ง</label>
        <input class="form-control @error('position') is-invalid @enderror"
               name="position" value="{{ old('position', $staff->position) }}"
               maxlength="50">
        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn btn-primary">บันทึก</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.staff.show', $staff) }}">ยกเลิก</a>
    </form>
  </div>
</x-layouts.admin>

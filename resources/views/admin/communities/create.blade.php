<x-layouts.admin title="เพิ่มชุมชน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Community Management</div>
      <h1 class="rb-page-title">เพิ่มชุมชน</h1>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.communities.index') }}">ยกเลิก</a>
    </div>
  </div>

  <div class="rb-surface p-4" style="max-width:480px;">
    <form method="POST" action="{{ route('admin.communities.store') }}">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-semibold">รหัสชุมชน <span class="text-danger">*</span></label>
        <input class="form-control font-monospace @error('community_id') is-invalid @enderror"
               name="community_id" value="{{ old('community_id') }}"
               maxlength="2" placeholder="เช่น 01" required style="text-transform:uppercase;">
        <div class="form-text">ตัวอักษรหรือตัวเลข 2 ตัว (ใช้เป็น ID ไม่สามารถแก้ไขได้ภายหลัง)</div>
        @error('community_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">ชื่อชุมชน <span class="text-danger">*</span></label>
        <input class="form-control @error('community_name') is-invalid @enderror"
               name="community_name" value="{{ old('community_name') }}"
               maxlength="100" placeholder="เช่น ชุมชนบ้านเขียว" required>
        @error('community_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn btn-primary">บันทึก</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.communities.index') }}">ยกเลิก</a>
    </form>
  </div>
</x-layouts.admin>

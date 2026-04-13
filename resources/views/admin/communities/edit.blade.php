<x-layouts.admin title="แก้ไขชุมชน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Community Management</div>
      <h1 class="rb-page-title">แก้ไขชุมชน</h1>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('admin.communities.index') }}">ยกเลิก</a>
    </div>
  </div>

  <div class="rb-surface p-4" style="max-width:480px;">
    <form method="POST" action="{{ route('admin.communities.update', $community) }}">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label fw-semibold">รหัสชุมชน</label>
        <input class="form-control font-monospace bg-light" value="{{ $community->community_id }}" readonly>
        <div class="form-text text-muted">รหัสชุมชนไม่สามารถเปลี่ยนแปลงได้</div>
        <input type="hidden" name="community_id" value="{{ $community->community_id }}">
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">ชื่อชุมชน <span class="text-danger">*</span></label>
        <input class="form-control @error('community_name') is-invalid @enderror"
               name="community_name" value="{{ old('community_name', $community->community_name) }}"
               maxlength="100" required>
        @error('community_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn btn-primary">บันทึก</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.communities.index') }}">ยกเลิก</a>
    </form>
  </div>
</x-layouts.admin>

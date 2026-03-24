<form class="row g-2 mb-3">
  <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }}">
    <label class="form-label">จากวันที่</label>
    <input type="date" class="form-control" name="from" value="{{ $from }}">
  </div>
  <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }}">
    <label class="form-label">ถึงวันที่</label>
    <input type="date" class="form-control" name="to" value="{{ $to }}">
  </div>
  @if($isPrivileged)
    <div class="col-md-2">
      <label class="form-label">ชุมชน</label>
      <select class="form-select" name="community_id">
        <option value="">ทุกชุมชน</option>
        @foreach($communities as $community)
          <option value="{{ $community->community_id }}" @selected($communityId === $community->community_id)>
            {{ $community->community_id }} - {{ $community->community_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">สถานะครัวเรือน</label>
      <select class="form-select" name="household_status">
        <option value="">ทุกสถานะ</option>
        <option value="active" @selected($householdStatus === 'active')>ใช้งาน</option>
        <option value="pending" @selected($householdStatus === 'pending')>รออนุมัติ</option>
        <option value="inactive" @selected($householdStatus === 'inactive')>ปิดใช้งาน</option>
      </select>
    </div>
  @endif
  <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }}">
    <label class="form-label">หมวดวัสดุ</label>
    <select class="form-select" name="category_id">
      <option value="">ทุกหมวด</option>
      @foreach($materialCategories as $category)
        <option value="{{ $category->category_id }}" @selected((string) $categoryId === (string) $category->category_id)>
          {{ $category->category_name }}
        </option>
      @endforeach
    </select>
  </div>
  <div class="{{ $isPrivileged ? 'col-md-2' : 'col-md-3' }} d-flex align-items-end gap-2">
    <button class="btn btn-primary w-100">ออกรายงาน</button>
    <a class="btn btn-outline-secondary w-100" href="{{ route('reports.index') }}">ล้าง</a>
  </div>
</form>

<div class="rb-note mb-3">
  หมวดวัสดุจะใช้กับข้อมูลรับซื้อ เช่น ยอดรับซื้อ, กราฟวัสดุ, น้ำหนัก, Top วัสดุ และอันดับครัวเรือนที่ขายวัสดุ ขณะที่ข้อมูลถอนยังแสดงตามช่วงวันที่และสิทธิ์ผู้ใช้ตามปกติ
</div>

@if($filterSummary !== [])
  <div class="d-flex flex-wrap gap-2 mb-4">
    @foreach($filterSummary as $item)
      <span class="rb-filter-chip">{{ $item }}</span>
    @endforeach
  </div>
@endif

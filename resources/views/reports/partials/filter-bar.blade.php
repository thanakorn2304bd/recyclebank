<form class="rb-surface p-4 mb-3">
  @if($isPrivileged)
    <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
      <div class="rb-section-head">
        <div>
          <h2 class="rb-card-title">ค้นหาครัวเรือน</h2>
          <p class="rb-card-subtitle">ค้นหาเฉพาะครัวเรือนก่อนดูสรุปรายงาน โดยใช้ Quick Search หรือระบุชุมชนกับบ้านเลขที่</p>
        </div>
        @if($householdQuery || $householdSearchCommunityId || $householdSearchHouseNo)
          <span class="rb-chip">กำลังค้นหาครัวเรือน</span>
        @endif
      </div>

      <div class="row g-3">
        <div class="col-lg-5">
          <label class="form-label">Quick Search</label>
          <input class="form-control" name="household_q" value="{{ $householdQuery }}" placeholder="เลขบัญชี / ผู้ติดต่อ / บ้านเลขที่">
        </div>
        <div class="col-lg-3">
          <label class="form-label">ชุมชนของครัวเรือน</label>
          <select class="form-select" name="household_search_community_id">
            <option value="">ทุกชุมชน</option>
            @foreach($communities as $community)
              <option value="{{ $community->community_id }}" @selected($householdSearchCommunityId === $community->community_id)>
                {{ $community->community_id }} - {{ $community->community_name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-2">
          <label class="form-label">บ้านเลขที่</label>
          <input class="form-control" name="household_search_house_no" value="{{ $householdSearchHouseNo }}" placeholder="เช่น 55">
        </div>
        <div class="col-lg-2 d-flex align-items-end gap-2">
          <button class="btn btn-outline-primary w-100">ค้นหาครัวเรือน</button>
        </div>
      </div>
    </div>
  @endif

  <div class="border rounded-4 p-3 bg-white">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">กรองรายงาน</h2>
        <p class="rb-card-subtitle">ใช้ตัวกรองส่วนนี้เพื่อปรับช่วงข้อมูลและมุมมองของรายงาน โดยไม่ต้องแก้ช่องค้นหาครัวเรือนทุกครั้ง</p>
      </div>
    </div>

    <div class="row g-3">
      <div class="{{ $isPrivileged ? 'col-md-3 col-xl-2' : 'col-md-3' }}">
        <label class="form-label">จากวันที่</label>
        <input type="date" class="form-control" name="from" value="{{ $from }}">
      </div>
      <div class="{{ $isPrivileged ? 'col-md-3 col-xl-2' : 'col-md-3' }}">
        <label class="form-label">ถึงวันที่</label>
        <input type="date" class="form-control" name="to" value="{{ $to }}">
      </div>
      @if($isPrivileged)
        <div class="col-md-3 col-xl-2">
          <label class="form-label">กรองชุมชน</label>
          <select class="form-select" name="community_id">
            <option value="">ทุกชุมชน</option>
            @foreach($communities as $community)
              <option value="{{ $community->community_id }}" @selected($communityId === $community->community_id)>
                {{ $community->community_id }} - {{ $community->community_name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-xl-2">
          <label class="form-label">สถานะครัวเรือน</label>
          <select class="form-select" name="household_status">
            <option value="">ทุกสถานะ</option>
            <option value="active" @selected($householdStatus === 'active')>ใช้งาน</option>
            <option value="pending" @selected($householdStatus === 'pending')>รออนุมัติ</option>
            <option value="inactive" @selected($householdStatus === 'inactive')>ปิดใช้งาน</option>
          </select>
        </div>
      @endif
      <div class="{{ $isPrivileged ? 'col-md-3 col-xl-2' : 'col-md-3' }}">
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
      <div class="{{ $isPrivileged ? 'col-md-6 col-xl-4' : 'col-md-3' }} d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100">ออกรายงาน</button>
        <a class="btn btn-outline-secondary w-100" href="{{ route('reports.index') }}">ล้าง</a>
      </div>
    </div>
  </div>
</form>

<div class="rb-note mb-3">
  โซนค้นหาครัวเรือนจะช่วยเจาะจง household ที่ต้องการก่อน ส่วนโซนกรองรายงานใช้จำกัดช่วงข้อมูล ชุมชน สถานะ และหมวดวัสดุของรายงานโดยรวม
</div>

@if($filterSummary !== [])
  <div class="d-flex flex-wrap gap-2 mb-4">
    @foreach($filterSummary as $item)
      <span class="rb-filter-chip">{{ $item }}</span>
    @endforeach
  </div>
@endif

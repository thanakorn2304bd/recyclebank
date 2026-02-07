<x-layouts.admin title="แก้ไขครัวเรือน">
  <h3 class="mb-3">แก้ไขครัวเรือน</h3>

  <form method="POST" action="{{ route('households.update', $household) }}" class="bg-white p-3 rounded">
    @csrf @method('PUT')

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">เลขบัญชี</label>
        <input class="form-control" name="account_no" value="{{ old('account_no', $household->account_no) }}" maxlength="10" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">เลขที่ชุมชน</label>
        <select class="form-select" name="community_id" required>
          <option value="">-- เลือกชุมชน --</option>
          @foreach($communities as $c)
            <option value="{{ $c->community_id }}" @selected(old('community_id', $household->community_id) == $c->community_id)>
              {{ $c->community_id }} - {{ $c->community_name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">บ้านเลขที่</label>
        <input class="form-control" name="house_no" value="{{ old('house_no', $household->house_no) }}" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">หมู่</label>
        <input class="form-control" name="village_no" value="{{ old('village_no', $household->village_no) }}">
      </div>

      <div class="col-md-5">
        <label class="form-label">ชื่อผู้ติดต่อ</label>
        <input class="form-control" name="contact_person" value="{{ old('contact_person', $household->contact_person) }}" required>
      </div>

      <div class="col-md-5">
        <label class="form-label">เบอร์โทร</label>
        <input class="form-control" name="phone" value="{{ old('phone', $household->phone) }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">วันที่สมัคร</label>
        <input type="date" class="form-control" name="register_date" value="{{ old('register_date', optional($household->register_date)->toDateString()) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">สถานะ</label>
        <select class="form-select" name="active_status" required>
          <option value="pending" @selected(old('active_status', $household->active_status) === 'pending')>รออนุมัติ</option>
          <option value="active" @selected(old('active_status', $household->active_status) === 'active')>ใช้งาน</option>
          <option value="inactive" @selected(old('active_status', $household->active_status) === 'inactive')>ปิด</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">สะสมเดือน</label>
        <input type="number" class="form-control" name="accumulated_months" min="0" value="{{ old('accumulated_months', $household->accumulated_months) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">ยอดคงเหลือ</label>
        <input class="form-control" value="{{ number_format((float)$household->total_balance, 2) }}" readonly>
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary">บันทึกการแก้ไข</button>
      <a class="btn btn-secondary" href="{{ route('households.index') }}">ยกเลิก</a>
    </div>
  </form>
</x-layouts.admin>

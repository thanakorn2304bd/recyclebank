<form method="POST" action="{{ route('material-prices.bulk-update') }}" class="rb-surface p-3 p-lg-4" id="rbBulkPriceForm">
  @csrf
  <input type="hidden" name="q" value="{{ $q }}">
  <input type="hidden" name="category_id" value="{{ $categoryId }}">
  <input type="hidden" name="material_id" value="{{ $materialId }}">

  <div class="rb-section-head">
    <div>
      <h2 class="rb-card-title">ตารางแก้ไขราคาแบบหลายรายการ</h2>
      <p class="rb-card-subtitle">แก้ค่าในหลายแถวแล้วกดบันทึกครั้งเดียว ระบบจะอัปเดตเฉพาะรายการที่เปลี่ยนจริงเท่านั้น</p>
    </div>
    <div class="rb-page-actions">
      <span class="rb-chip" id="rbDirtyPriceSummary">{{ $dirtySummary }}</span>
      <button type="button" class="btn btn-outline-secondary" id="rbResetPriceEditor">รีเซ็ตค่าที่แก้</button>
      <button type="submit" class="btn btn-primary" id="rbSubmitPriceEditor">บันทึกการแก้ไข</button>
    </div>
  </div>

  <div class="rb-note mb-3">
    ถ้าวัสดุบางรายการยังไม่มีราคาปัจจุบัน ให้กรอกราคาและวันที่เริ่มใช้ในแถวนั้นได้เลย ส่วนรายการที่ไม่แก้ ระบบจะไม่แตะต้องข้อมูลเดิม
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th style="min-width:220px;">วัสดุ</th>
          <th style="min-width:160px;">หมวด</th>
          <th style="width:180px;">ราคา (บาท)</th>
          <th style="width:180px;">เริ่มใช้</th>
          <th style="width:180px;">หมดอายุ</th>
          <th style="width:160px;">สถานะราคา</th>
          <th style="width:120px;" data-sortable="false"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($priceEditorRows as $row)
          <tr data-material-row>
            <td>
              <div class="fw-semibold">{{ $row['material_name'] }}</div>
              <div class="rb-price-meta">รหัส #{{ $row['material_id'] }} · หน่วย {{ $row['unit'] }}</div>
              @if(! $row['is_active'])
                <div class="rb-price-meta text-danger mt-1">วัสดุนี้ถูกปิดใช้งานอยู่</div>
              @endif
              <input type="hidden" name="rows[{{ $row['material_id'] }}][price_id]" value="{{ $row['current_price_id'] }}">
            </td>
            <td>{{ $row['category_name'] ?? '-' }}</td>
            <td>
              <input
                class="form-control @error("rows.{$row['material_key']}.price") is-invalid @enderror"
                type="number"
                step="0.01"
                min="0"
                name="rows[{{ $row['material_id'] }}][price]"
                value="{{ $row['row_price'] }}"
                placeholder="เช่น 12.50"
                data-initial-value="{{ $row['initial_price'] }}"
              >
              @error("rows.{$row['material_key']}.price")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </td>
            <td>
              <input
                class="form-control @error("rows.{$row['material_key']}.effective_date") is-invalid @enderror"
                type="date"
                name="rows[{{ $row['material_id'] }}][effective_date]"
                value="{{ $row['row_effective_date'] }}"
                data-initial-value="{{ $row['initial_effective_date'] }}"
              >
              @error("rows.{$row['material_key']}.effective_date")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </td>
            <td>
              <input
                class="form-control @error("rows.{$row['material_key']}.expired_date") is-invalid @enderror"
                type="date"
                name="rows[{{ $row['material_id'] }}][expired_date]"
                value="{{ $row['row_expired_date'] }}"
                data-initial-value="{{ $row['initial_expired_date'] }}"
              >
              @error("rows.{$row['material_key']}.expired_date")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </td>
            <td>
              @if($row['has_current_price'])
                <span class="rb-price-status rb-price-status-current">มีราคาปัจจุบัน</span>
              @else
                <span class="rb-price-status rb-price-status-missing">ยังไม่มีราคา</span>
              @endif
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('materials.prices', ['material' => $row['material_id']]) }}">ประวัติ</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">ไม่พบวัสดุตามเงื่อนไขที่เลือก</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($priceEditorRows->isNotEmpty())
    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
      <button type="button" class="btn btn-outline-secondary" id="rbResetPriceEditorBottom">รีเซ็ตค่าที่แก้</button>
      <button type="submit" class="btn btn-primary">บันทึกการแก้ไขทั้งหมด</button>
    </div>
  @endif
</form>

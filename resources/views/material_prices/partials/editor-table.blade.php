<form method="POST" action="{{ route('material-prices.bulk-update') }}" class="rb-surface p-3 p-lg-4" id="rbBulkPriceForm">
  @csrf
  <input type="hidden" name="target_month" value="{{ $targetMonth }}">
  <input type="hidden" name="q" value="{{ $q }}">
  <input type="hidden" name="category_id" value="{{ $categoryId }}">
  <input type="hidden" name="material_id" value="{{ $materialId }}">

  <div class="rb-section-head">
    <div>
      <h2 class="rb-card-title">ตารางจัดชุดราคาเดือน {{ $targetMonthLabel }}</h2>
      <p class="rb-card-subtitle">ค่าที่เห็นในช่องราคาคือราคาของเดือนนี้ถ้ามีอยู่แล้ว หรือค่าที่คัดมาจากเดือนก่อนเพื่อใช้เป็นจุดเริ่มต้น</p>
    </div>
    <div class="rb-page-actions">
      <span class="rb-chip" id="rbDirtyPriceSummary">{{ $dirtySummary }}</span>
      <button type="button" class="btn btn-outline-secondary" id="rbResetPriceEditor">รีเซ็ตค่าที่แก้</button>
      <button type="submit" class="btn btn-primary" id="rbSubmitPriceEditor">เผยแพร่ชุดราคาเดือนนี้</button>
    </div>
  </div>

  <div class="rb-note mb-3">
    เมื่อบันทึก ระบบจะเผยแพร่ราคาของวัสดุที่แสดงทั้งหมดให้เริ่มใช้วันที่ {{ \Carbon\Carbon::parse($monthStart)->format('d/m/Y') }}
    รายการที่ไม่แก้จะใช้ค่าที่แสดงอยู่ในช่องราคาเป็นค่าของเดือนนี้ทันที และจะไม่อนุญาตให้บันทึกย้อนหลังไปก่อนเดือนปัจจุบัน
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th style="min-width:220px;">วัสดุ</th>
          <th style="min-width:160px;">หมวด</th>
          <th style="width:180px;">ราคาเดือน {{ $targetMonthLabel }} (บาท)</th>
          <th style="min-width:260px;">ที่มาของค่าตั้งต้น</th>
          <th style="width:200px;">สถานะเดือน {{ $targetMonthLabel }}</th>
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
              <div class="fw-semibold">{{ $row['source_label'] }}</div>
              <div class="rb-price-meta">{{ $row['source_meta'] }}</div>
              @if($row['selected_month_price'] !== '')
                <div class="rb-price-meta mt-1">ราคาเดือนนี้เดิม {{ $row['selected_month_price'] }} บาท</div>
              @elseif($row['carry_forward_price'] !== '')
                <div class="rb-price-meta mt-1">ราคาที่จะคัดลอก {{ $row['carry_forward_price'] }} บาท</div>
              @endif
            </td>
            <td>
              @if($row['status_variant'] === 'current')
                <span class="rb-price-status rb-price-status-current">เผยแพร่แล้ว</span>
              @elseif($row['status_variant'] === 'carry')
                <span class="rb-price-status rb-price-status-carry">พร้อมคัดลอก</span>
              @else
                <span class="rb-price-status rb-price-status-missing">ยังไม่มีราคา</span>
              @endif
              <div class="rb-price-meta mt-2">{{ $row['status_label'] }}</div>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('materials.prices', ['material' => $row['material_id']]) }}">ประวัติ</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">ไม่พบวัสดุตามเงื่อนไขที่เลือก</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($priceEditorRows->isNotEmpty())
    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
      <button type="button" class="btn btn-outline-secondary" id="rbResetPriceEditorBottom">รีเซ็ตค่าที่แก้</button>
      <button type="submit" class="btn btn-primary">เผยแพร่ชุดราคาเดือน {{ $targetMonthLabel }}</button>
    </div>
  @endif
</form>

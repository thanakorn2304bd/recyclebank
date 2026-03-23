<x-layouts.admin title="แก้ไขราคาวัสดุ">
  @php
    $dirtySummary = 'ยังไม่มีรายการแก้ไข';
  @endphp

  <style>
    .rb-price-editor-row-dirty > * {
      background: linear-gradient(180deg, rgba(255, 251, 235, 0.95) 0%, rgba(255, 255, 255, 0.96) 100%) !important;
    }

    .rb-price-editor-grid {
      display: grid;
      gap: 1rem;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .rb-price-status {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      border-radius: 999px;
      padding: 0.4rem 0.75rem;
      font-size: 0.82rem;
      font-weight: 600;
    }

    .rb-price-status-current {
      background: #eaf8f1;
      color: #116149;
      border: 1px solid #b8e4cc;
    }

    .rb-price-status-missing {
      background: #fff7e8;
      color: #9a5b00;
      border: 1px solid #f7ddb0;
    }

    .rb-price-meta {
      color: #5f766a;
      font-size: 0.82rem;
    }
  </style>

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Bulk Price Editor</div>
      <h1 class="rb-page-title">แก้ไขราคาวัสดุ</h1>
      <p class="rb-page-subtitle">
        ปรับราคาหลายรายการพร้อมกันได้จากหน้าเดียว เหมาะกับวันเปลี่ยนราคารอบใหญ่ โดยดูวัสดุ หมวด ราคาเริ่มใช้ และสถานะได้ครบในตารางเดียว
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('materials.index') }}">ดูวัสดุ</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">วัสดุที่แสดง</div>
      <div class="rb-stat-value">{{ number_format($summary['materials']) }}</div>
      <div class="rb-stat-meta">รายการที่ตรงกับตัวกรองปัจจุบัน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">มีราคาปัจจุบัน</div>
      <div class="rb-stat-value">{{ number_format($summary['priced']) }}</div>
      <div class="rb-stat-meta">แก้ไขราคาเดิมได้ทันทีในตาราง</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยังไม่มีราคา</div>
      <div class="rb-stat-value">{{ number_format($summary['missing']) }}</div>
      <div class="rb-stat-meta">กรอกแถวว่างแล้วบันทึกเพื่อสร้างราคาแรกได้เลย</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">วัสดุเปิดใช้งาน</div>
      <div class="rb-stat-value">{{ number_format($summary['active']) }}</div>
      <div class="rb-stat-meta">อ้างอิงจากสถานะวัสดุในระบบ</div>
    </div>
  </div>

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
          @forelse($materials as $material)
            @php
              $materialKey = (string) $material->material_id;
              $initialPrice = $material->current_price_value !== null ? number_format((float) $material->current_price_value, 2, '.', '') : '';
              $initialEffectiveDate = $material->current_effective_date ?? '';
              $initialExpiredDate = $material->current_expired_date ?? '';
              $rowPrice = old("rows.$materialKey.price", $initialPrice);
              $rowEffectiveDate = old("rows.$materialKey.effective_date", $initialEffectiveDate);
              $rowExpiredDate = old("rows.$materialKey.expired_date", $initialExpiredDate);
              $hasCurrentPrice = $material->current_price_id !== null;
            @endphp
            <tr data-material-row>
              <td>
                <div class="fw-semibold">{{ $material->material_name }}</div>
                <div class="rb-price-meta">รหัส #{{ $material->material_id }} · หน่วย {{ $material->unit }}</div>
                @if(! $material->is_active)
                  <div class="rb-price-meta text-danger mt-1">วัสดุนี้ถูกปิดใช้งานอยู่</div>
                @endif
                <input type="hidden" name="rows[{{ $material->material_id }}][price_id]" value="{{ $material->current_price_id }}">
              </td>
              <td>{{ $material->category_name ?? '-' }}</td>
              <td>
                <input
                  class="form-control @error("rows.$materialKey.price") is-invalid @enderror"
                  type="number"
                  step="0.01"
                  min="0"
                  name="rows[{{ $material->material_id }}][price]"
                  value="{{ $rowPrice }}"
                  placeholder="เช่น 12.50"
                  data-initial-value="{{ $initialPrice }}"
                >
                @error("rows.$materialKey.price")
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </td>
              <td>
                <input
                  class="form-control @error("rows.$materialKey.effective_date") is-invalid @enderror"
                  type="date"
                  name="rows[{{ $material->material_id }}][effective_date]"
                  value="{{ $rowEffectiveDate }}"
                  data-initial-value="{{ $initialEffectiveDate }}"
                >
                @error("rows.$materialKey.effective_date")
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </td>
              <td>
                <input
                  class="form-control @error("rows.$materialKey.expired_date") is-invalid @enderror"
                  type="date"
                  name="rows[{{ $material->material_id }}][expired_date]"
                  value="{{ $rowExpiredDate }}"
                  data-initial-value="{{ $initialExpiredDate }}"
                >
                @error("rows.$materialKey.expired_date")
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </td>
              <td>
                @if($hasCurrentPrice)
                  <span class="rb-price-status rb-price-status-current">มีราคาปัจจุบัน</span>
                @else
                  <span class="rb-price-status rb-price-status-missing">ยังไม่มีราคา</span>
                @endif
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('materials.prices', ['material' => $material->material_id]) }}">ประวัติ</a>
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

    @if($materials->isNotEmpty())
      <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-outline-secondary" id="rbResetPriceEditorBottom">รีเซ็ตค่าที่แก้</button>
        <button type="submit" class="btn btn-primary">บันทึกการแก้ไขทั้งหมด</button>
      </div>
    @endif
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('rbBulkPriceForm');

      if (! form) {
        return;
      }

      const rows = Array.from(form.querySelectorAll('[data-material-row]'));
      const summary = document.getElementById('rbDirtyPriceSummary');
      const submitButton = document.getElementById('rbSubmitPriceEditor');
      const resetButtons = [
        document.getElementById('rbResetPriceEditor'),
        document.getElementById('rbResetPriceEditorBottom'),
      ].filter(Boolean);

      function rowInputs(row) {
        return Array.from(row.querySelectorAll('input[type="number"], input[type="date"]'));
      }

      function isDirty(row) {
        return rowInputs(row).some(function (input) {
          return String(input.value ?? '') !== String(input.dataset.initialValue ?? '');
        });
      }

      function refreshDirtyState() {
        let dirtyCount = 0;

        rows.forEach(function (row) {
          const dirty = isDirty(row);

          row.classList.toggle('rb-price-editor-row-dirty', dirty);

          if (dirty) {
            dirtyCount += 1;
          }
        });

        if (summary) {
          summary.textContent = dirtyCount > 0
            ? 'แก้ไขแล้ว ' + dirtyCount + ' รายการ'
            : 'ยังไม่มีรายการแก้ไข';
        }

        if (submitButton) {
          submitButton.textContent = dirtyCount > 0
            ? 'บันทึก ' + dirtyCount + ' รายการ'
            : 'บันทึกการแก้ไข';
        }
      }

      function resetChanges() {
        rows.forEach(function (row) {
          rowInputs(row).forEach(function (input) {
            input.value = input.dataset.initialValue ?? '';
          });
        });

        refreshDirtyState();
      }

      rows.forEach(function (row) {
        rowInputs(row).forEach(function (input) {
          input.addEventListener('input', refreshDirtyState);
          input.addEventListener('change', refreshDirtyState);
        });
      });

      resetButtons.forEach(function (button) {
        button.addEventListener('click', resetChanges);
      });

      refreshDirtyState();
    });
  </script>
</x-layouts.admin>

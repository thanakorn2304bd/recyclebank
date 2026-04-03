<x-layouts.admin title="ฝาก/รับซื้อ (Deposit)">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Daily Intake</div>
      <h1 class="rb-page-title">ฝาก/รับซื้อวัสดุ</h1>
      <p class="rb-page-subtitle">
        ค้นหาครัวเรือน เพิ่มรายการวัสดุหลายชนิดในครั้งเดียว และตรวจสอบยอดรวมก่อนบันทึกรายการได้จากหน้านี้
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-dark" href="{{ route('withdraws.create') }}">ไปหน้า “ถอน”</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">วันที่ทำรายการ</div>
      <div class="rb-stat-value" id="transactionDateDisplay">{{ old('transaction_date', now()->toDateString()) }}</div>
      <div class="rb-stat-meta">เปลี่ยนค่าได้จากฟอร์มด้านล่าง</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดรวมประมาณการ</div>
      <div class="rb-stat-value" id="grandTotalDisplay">0.00</div>
      <div class="rb-stat-meta">คำนวณอัตโนมัติจากรายการวัสดุ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">จำนวนรายการ</div>
      <div class="rb-stat-value" id="itemCountDisplay">0</div>
      <div class="rb-stat-meta">จำนวนชนิดวัสดุในรายการนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ครัวเรือนที่เลือก</div>
      <div class="rb-stat-value rb-stat-value-sm" id="householdNameDisplay">ยังไม่ได้ค้นหา</div>
      <div class="rb-stat-meta" id="householdMetaDisplay">ค้นหาจากเลขที่ชุมชนและบ้านเลขที่</div>
    </div>
  </div>

  <form method="POST" action="{{ route('deposits.store') }}" class="rb-surface p-4 p-lg-4" id="depositForm">
    @csrf

    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาครัวเรือนและข้อมูลพื้นฐาน</h2>
        <p class="rb-card-subtitle">ระบุชุมชน บ้านเลขที่ และวันที่ทำรายการก่อนเพิ่มวัสดุลงในบิล</p>
      </div>
      <span class="rb-chip">Step 1</span>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">ค้นหาด่วน</label>
        <div class="row g-2">
          <div class="col-md-9">
            <input
              class="form-control"
              id="quickSearchInput"
              type="search"
              placeholder="เลขบัญชี / ชื่อผู้ติดต่อ / สมาชิก / เบอร์โทร / บ้านเลขที่"
            >
          </div>
          <div class="col-md-3 d-grid">
            <button type="button" class="btn btn-outline-dark" id="quickSearchBtn">ค้นหาแบบเร็ว</button>
          </div>
        </div>
        <div class="form-text">ค้นหาแล้วกดเลือกผลลัพธ์เพื่อเติมเลขที่ชุมชนและบ้านเลขที่ให้อัตโนมัติ</div>
        <div id="quickSearchResults" class="mt-3 d-none"></div>
      </div>

      <div class="col-lg-6">
        <label class="form-label">ค้นหาครัวเรือน</label>
        <div class="row g-2">
          <div class="col-md-4">
            <input class="form-control" id="communityIdInput" name="community_id" value="{{ old('community_id') }}" placeholder="เลขที่ชุมชน" inputmode="numeric" maxlength="2" required>
          </div>
          <div class="col-md-5">
            <input class="form-control" id="houseNoInput" name="house_no" value="{{ old('house_no') }}" placeholder="บ้านเลขที่" required>
          </div>
          <div class="col-md-3 d-grid">
            <button type="button" class="btn btn-outline-primary" id="searchHouseholdBtn">ค้นหา</button>
          </div>
        </div>
        <div class="form-text">ระบบจะค้นหาครัวเรือนจากเลขที่ชุมชน + บ้านเลขที่</div>
      </div>

      <div class="col-lg-3">
        <label class="form-label">วันที่ทำรายการ</label>
        <input type="date" class="form-control" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" required>
      </div>

      <div class="col-lg-3">
        <label class="form-label">ยอดรวม (คำนวณ)</label>
        <input type="text" class="form-control" id="grandTotal" value="0.00" readonly>
      </div>

      <div class="col-12">
        <div id="householdInfo" class="rb-info-panel d-none">
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label small">บัญชี</label>
              <input class="form-control" id="infoAccountNo" readonly>
            </div>
            <div class="col-md-8">
              <label class="form-label small">ชื่อผู้ติดต่อ</label>
              <input class="form-control" id="infoContactPerson" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label small">ชุมชน</label>
              <input class="form-control" id="infoCommunity" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label small">บ้านเลขที่</label>
              <input class="form-control" id="infoHouseNo" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label small">สถานะ</label>
              <input class="form-control" id="infoStatus" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label small">ยอดคงเหลือ</label>
              <input class="form-control" id="infoBalance" readonly>
            </div>
          </div>
        </div>
        <div id="householdError" class="alert alert-warning mt-3 d-none" role="alert"></div>
      </div>
    </div>

    <div class="border-top my-4"></div>

    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการวัสดุ</h2>
        <p class="rb-card-subtitle">เพิ่มวัสดุทีละรายการหรือเลือกหลายรายการพร้อมกันจากตัวเลือกด้านล่าง</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary" id="openMaterialModalBtn">+ เพิ่มรายการ</button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0" id="itemsTable">
        <thead>
          <tr>
            <th style="width:35%;">วัสดุ</th>
            <th style="width:15%;">หน่วย</th>
            <th style="width:15%;">น้ำหนัก</th>
            <th style="width:15%;">ราคา/หน่วย</th>
            <th style="width:15%;">จำนวนเงิน</th>
            <th style="width:5%;"></th>
          </tr>
        </thead>
        <tbody id="itemsBody"></tbody>
      </table>
    </div>

    <div class="mt-3 d-flex justify-content-end">
      <div class="rb-total-summary">
        <div class="rb-total-summary__label">ยอดรวมรายการนี้</div>
        <div class="rb-total-summary__value" id="grandTotalFooterDisplay">0.00 บาท</div>
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
      <button class="btn btn-success" type="submit" id="saveDepositBtn">บันทึกฝาก/รับซื้อ</button>
      <a class="btn btn-outline-secondary" href="{{ route('materials.index') }}">กลับหน้า “วัสดุ”</a>
    </div>
    <div class="form-text mt-2">ระบบจะคำนวณยอดรวมอัตโนมัติจากน้ำหนักและราคาปัจจุบันของวัสดุแต่ละชนิด</div>
  </form>

  <div class="modal fade" id="materialPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title">เลือกวัสดุ</h5>
            <div class="text-muted small">เลือกหลายรายการได้ในครั้งเดียวแล้วกดยืนยัน</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-3">
          <div class="d-flex flex-wrap gap-2 mb-3" id="materialCategoryFilters"></div>
          <div class="row g-2" id="materialPickerList"></div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="confirmMaterialSelection">ยืนยัน</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="confirmDepositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title">ยืนยันการบันทึกฝาก/รับซื้อ</h5>
            <div class="text-muted small">ตรวจสอบข้อมูลอีกครั้งก่อนบันทึกรายการนี้</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-3">
          <div class="rb-confirm-summary">
            <div class="rb-confirm-summary__row">
              <span>ครัวเรือน</span>
              <strong id="confirmHouseholdDisplay">-</strong>
            </div>
            <div class="rb-confirm-summary__row">
              <span>วันที่ทำรายการ</span>
              <strong id="confirmDateDisplay">-</strong>
            </div>
            <div class="rb-confirm-summary__row">
              <span>จำนวนรายการ</span>
              <strong id="confirmItemCountDisplay">0 รายการ</strong>
            </div>
            <div class="rb-confirm-summary__row rb-confirm-summary__row--total">
              <span>ยอดรวม</span>
              <strong id="confirmTotalDisplay">0.00 บาท</strong>
            </div>
          </div>
          <div class="alert alert-light border mt-3 mb-0">
            เมื่อกดยืนยัน ระบบจะบันทึกรายการและพาไปหน้าสรุปรายการทันที
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ตรวจสอบอีกครั้ง</button>
          <button type="button" class="btn btn-success" id="confirmDepositSubmitBtn">ยืนยันและบันทึก</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .rb-stat-value-sm {
      font-size: 1.35rem;
    }

    .rb-total-summary {
      min-width: min(100%, 280px);
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      background: linear-gradient(180deg, #fbfffc 0%, #f1fbf5 100%);
      box-shadow: 0 10px 24px rgba(15, 109, 74, 0.08);
      padding: 0.95rem 1.1rem;
      text-align: right;
    }

    .rb-total-summary__label {
      color: #4c6658;
      font-size: 0.82rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .rb-total-summary__value {
      margin-top: 0.35rem;
      color: #0b4d32;
      font-size: 1.75rem;
      font-weight: 700;
      line-height: 1.1;
    }

    .material-card {
      width: 100%;
      text-align: left;
      border: 1px solid #d0e7dc;
      border-radius: 1rem;
      padding: 0.9rem 1rem;
      background: #ffffff;
      color: #1f2937;
      transition: all 0.15s ease-in-out;
    }

    .material-card:hover {
      border-color: #74c7a2;
      box-shadow: 0 10px 24px rgba(15, 109, 74, 0.08);
    }

    .material-card.is-selected {
      border-color: #198754;
      background: #e9f7ef;
      box-shadow: inset 0 0 0 1px #198754;
    }

    .material-card-title {
      font-weight: 600;
    }

    .material-card-unit {
      color: #6b7280;
      font-size: 0.85rem;
    }

    .material-category-title {
      font-weight: 600;
      color: #0f6d4a;
      padding: 0.4rem 0.6rem;
      border-left: 4px solid #0f6d4a;
      background: #f3fbf7;
      border-radius: 0.6rem;
    }

    .rb-confirm-summary {
      display: grid;
      gap: 0.75rem;
      padding: 1rem;
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      background: linear-gradient(180deg, #fbfffc 0%, #f1fbf5 100%);
    }

    .rb-confirm-summary__row {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      color: #496457;
    }

    .rb-confirm-summary__row strong {
      color: #0b4d32;
      text-align: right;
    }

    .rb-confirm-summary__row--total {
      padding-top: 0.75rem;
      border-top: 1px dashed #b7e5ce;
      font-size: 1.05rem;
    }
  </style>

  <script>
    const materials = @json($materials);
    const currentPrices = @json($currentPrices);
    const lookupUrl = @json(route('deposits.lookup-household'));
    const quickSearchUrl = @json(route('households.quick-search'));

    const depositForm = document.getElementById('depositForm');
    const itemsBody = document.getElementById('itemsBody');
    const openMaterialModalBtn = document.getElementById('openMaterialModalBtn');
    const materialCategoryFilters = document.getElementById('materialCategoryFilters');
    const materialPickerList = document.getElementById('materialPickerList');
    const materialPickerModalEl = document.getElementById('materialPickerModal');
    const confirmMaterialSelection = document.getElementById('confirmMaterialSelection');
    const confirmDepositModalEl = document.getElementById('confirmDepositModal');
    const confirmDepositSubmitBtn = document.getElementById('confirmDepositSubmitBtn');
    const grandTotalEl = document.getElementById('grandTotal');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');
    const grandTotalFooterDisplay = document.getElementById('grandTotalFooterDisplay');
    const itemCountDisplay = document.getElementById('itemCountDisplay');
    const transactionDateDisplay = document.getElementById('transactionDateDisplay');
    const householdNameDisplay = document.getElementById('householdNameDisplay');
    const householdMetaDisplay = document.getElementById('householdMetaDisplay');
    const transactionDateInput = depositForm.elements.namedItem('transaction_date');
    const communityIdInput = document.getElementById('communityIdInput');
    const houseNoInput = document.getElementById('houseNoInput');
    const quickSearchInput = document.getElementById('quickSearchInput');
    const quickSearchBtn = document.getElementById('quickSearchBtn');
    const quickSearchResults = document.getElementById('quickSearchResults');
    const searchHouseholdBtn = document.getElementById('searchHouseholdBtn');
    const householdInfo = document.getElementById('householdInfo');
    const householdError = document.getElementById('householdError');
    const infoAccountNo = document.getElementById('infoAccountNo');
    const infoContactPerson = document.getElementById('infoContactPerson');
    const infoCommunity = document.getElementById('infoCommunity');
    const infoHouseNo = document.getElementById('infoHouseNo');
    const infoStatus = document.getElementById('infoStatus');
    const infoBalance = document.getElementById('infoBalance');
    const confirmHouseholdDisplay = document.getElementById('confirmHouseholdDisplay');
    const confirmDateDisplay = document.getElementById('confirmDateDisplay');
    const confirmItemCountDisplay = document.getElementById('confirmItemCountDisplay');
    const confirmTotalDisplay = document.getElementById('confirmTotalDisplay');

    let isDepositSubmissionConfirmed = false;
    let quickSearchMatches = [];

    function formatDateDisplay(value) {
      if (!value || typeof value !== 'string') {
        return '-';
      }

      const parts = value.split('-');

      if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
      }

      return value;
    }

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function statusLabel(status) {
      switch (status) {
        case 'active':
          return 'ใช้งาน';
        case 'pending':
          return 'รออนุมัติ';
        default:
          return 'ปิดใช้งาน';
      }
    }

    function updateHeaderSummary() {
      const rowCount = getDataRows().length;
      itemCountDisplay.textContent = rowCount.toString();
      grandTotalDisplay.textContent = grandTotalEl.value;
      grandTotalFooterDisplay.textContent = `${grandTotalEl.value} บาท`;
      transactionDateDisplay.textContent = formatDateDisplay(transactionDateInput.value);
    }

    function buildHouseholdConfirmationText() {
      const householdName = householdNameDisplay.textContent.trim();
      const accountNo = infoAccountNo.value.trim();
      const communityId = communityIdInput.value.trim();
      const houseNo = houseNoInput.value.trim();

      if (householdName && householdName !== 'ยังไม่ได้ค้นหา') {
        return accountNo ? `${householdName} (${accountNo})` : householdName;
      }

      if (communityId || houseNo) {
        return `ชุมชน ${communityId || '-'} บ้านเลขที่ ${houseNo || '-'}`;
      }

      return '-';
    }

    function syncConfirmationModal() {
      confirmHouseholdDisplay.textContent = buildHouseholdConfirmationText();
      confirmDateDisplay.textContent = formatDateDisplay(transactionDateInput.value);
      confirmItemCountDisplay.textContent = `${getDataRows().length} รายการ`;
      confirmTotalDisplay.textContent = `${grandTotalEl.value} บาท`;
    }

    function materialOptionsHtml(selectedId = '') {
      return materials.map(m => {
        const sel = String(m.material_id) === String(selectedId) ? 'selected' : '';
        return `<option value="${m.material_id}" ${sel}>${m.material_name}</option>`;
      }).join('');
    }

    function getDataRows() {
      return Array.from(itemsBody.querySelectorAll('tr')).filter(tr => tr.querySelector('.material-select'));
    }

    function ensureEmptyRow() {
      if (getDataRows().length === 0) {
        itemsBody.innerHTML = `
          <tr class="empty-row">
            <td colspan="6" class="text-center text-muted py-4">ยังไม่มีรายการวัสดุ</td>
          </tr>
        `;
      }

      updateHeaderSummary();
    }

    function addRow(prefill = {}) {
      const rowIndex = getDataRows().length;
      const materialId = prefill.material_id || (materials[0]?.material_id ?? '');
      const unit = materials.find(m => String(m.material_id) === String(materialId))?.unit ?? '';
      const ppu = currentPrices[String(materialId)] ?? 0;
      const weight = prefill.weight ?? '';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>
          <select class="form-select material-select" name="items[${rowIndex}][material_id]" required>
            ${materialOptionsHtml(materialId)}
          </select>
        </td>
        <td><input class="form-control unit" value="${unit}" readonly></td>
        <td><input class="form-control weight" type="number" step="0.01" min="0.01" max="99999999.99" name="items[${rowIndex}][weight]" value="${weight}" required></td>
        <td><input class="form-control ppu" value="${Number(ppu).toFixed(2)}" readonly></td>
        <td><input class="form-control amount" value="0.00" readonly></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove">x</button></td>
      `;
      const emptyRow = itemsBody.querySelector('.empty-row');
      if (emptyRow) emptyRow.remove();
      itemsBody.appendChild(tr);
      recalcRow(tr);
      recalcGrandTotal();
    }

    function recalcRow(tr) {
      const materialId = tr.querySelector('.material-select').value;
      const weight = parseFloat(tr.querySelector('.weight').value || '0');
      const mat = materials.find(m => String(m.material_id) === String(materialId));
      tr.querySelector('.unit').value = mat?.unit ?? '';

      const ppu = parseFloat(currentPrices[String(materialId)] ?? '0');
      tr.querySelector('.ppu').value = ppu.toFixed(2);

      const amount = weight * ppu;
      tr.querySelector('.amount').value = isFinite(amount) ? amount.toFixed(2) : '0.00';
    }

    function recalcGrandTotal() {
      let sum = 0;
      document.querySelectorAll('#itemsBody .amount').forEach(a => {
        sum += parseFloat(a.value || '0');
      });
      grandTotalEl.value = sum.toFixed(2);
      updateHeaderSummary();
    }

    itemsBody.addEventListener('change', (e) => {
      const tr = e.target.closest('tr');
      if (!tr) return;

      if (e.target.classList.contains('material-select')) {
        recalcRow(tr);
        recalcGrandTotal();
      }
    });

    itemsBody.addEventListener('input', (e) => {
      const tr = e.target.closest('tr');
      if (!tr) return;

      if (e.target.classList.contains('weight')) {
        recalcRow(tr);
        recalcGrandTotal();
      }
    });

    itemsBody.addEventListener('click', (e) => {
      if (e.target.classList.contains('remove')) {
        e.target.closest('tr').remove();
        getDataRows().forEach((tr, i) => {
          tr.querySelector('.material-select').name = `items[${i}][material_id]`;
          tr.querySelector('.weight').name = `items[${i}][weight]`;
        });
        recalcGrandTotal();
        ensureEmptyRow();
      }
    });

    function getCategoryName(m) {
      return m?.category?.category_name || m?.category_name || 'อื่นๆ';
    }

    const categories = Array.from(new Set(materials.map(getCategoryName)))
      .filter(Boolean)
      .sort((a, b) => a.localeCompare(b, 'th'));
    let activeCategory = 'all';
    let pendingSelectedMaterialIds = new Set();

    function renderCategoryFilters() {
      const buttons = ['all', ...categories].map((cat) => {
        const label = cat === 'all' ? 'ทั้งหมด' : cat;
        const activeClass = activeCategory === cat ? 'btn-success' : 'btn-outline-success';
        return `<button type="button" class="btn btn-sm ${activeClass}" data-category="${cat}">${label}</button>`;
      }).join('');
      materialCategoryFilters.innerHTML = buttons;
    }

    function materialCardHtml(m) {
      return `
        <div class="col-md-6 col-lg-4">
          <button type="button" class="material-card" data-id="${m.material_id}">
            <div class="material-card-title">${m.material_name}</div>
            <div class="material-card-unit">${m.unit ?? ''}</div>
          </button>
        </div>
      `;
    }

    function renderMaterialPicker() {
      if (activeCategory === 'all') {
        let html = '';
        categories.forEach((cat) => {
          const group = materials.filter(m => getCategoryName(m) === cat);
          if (!group.length) return;
          html += `<div class="col-12"><div class="material-category-title">${cat}</div></div>`;
          html += group.map(materialCardHtml).join('');
        });
        materialPickerList.innerHTML = html || '<div class="text-muted">ไม่พบวัสดุ</div>';
      } else {
        const filtered = materials.filter(m => getCategoryName(m) === activeCategory);
        materialPickerList.innerHTML = filtered.map(materialCardHtml).join('') || '<div class="text-muted">ไม่พบวัสดุ</div>';
      }
    }

    function resetPendingMaterialSelection() {
      pendingSelectedMaterialIds = new Set(
        getDataRows().map((tr) => tr.querySelector('.material-select').value)
      );
    }

    function syncModalSelection() {
      materialPickerList.querySelectorAll('.material-card').forEach((btn) => {
        const id = btn.getAttribute('data-id');
        btn.classList.toggle('is-selected', pendingSelectedMaterialIds.has(id));
      });
    }

    materialPickerList.addEventListener('click', (e) => {
      const card = e.target.closest('.material-card');
      if (!card) return;
      const id = card.getAttribute('data-id');

      if (pendingSelectedMaterialIds.has(id)) {
        pendingSelectedMaterialIds.delete(id);
      } else {
        pendingSelectedMaterialIds.add(id);
      }

      card.classList.toggle('is-selected', pendingSelectedMaterialIds.has(id));
    });

    materialCategoryFilters.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-category]');
      if (!btn) return;
      activeCategory = btn.getAttribute('data-category') || 'all';
      renderCategoryFilters();
      renderMaterialPicker();
      syncModalSelection();
    });

    confirmMaterialSelection.addEventListener('click', () => {
      const existingIds = new Set(getDataRows().map(tr => tr.querySelector('.material-select').value));

      pendingSelectedMaterialIds.forEach((id) => {
        if (!existingIds.has(id)) addRow({ material_id: id });
      });

      bootstrap.Modal.getOrCreateInstance(materialPickerModalEl).hide();
    });

    openMaterialModalBtn.addEventListener('click', () => {
      resetPendingMaterialSelection();

      if (!materialPickerList.children.length) {
        renderCategoryFilters();
        renderMaterialPicker();
      } else {
        renderMaterialPicker();
      }

      syncModalSelection();
      bootstrap.Modal.getOrCreateInstance(materialPickerModalEl).show();
    });

    function clearHouseholdInfo() {
      infoAccountNo.value = '';
      infoContactPerson.value = '';
      infoCommunity.value = '';
      infoHouseNo.value = '';
      infoStatus.value = '';
      infoBalance.value = '';
      householdNameDisplay.textContent = 'ยังไม่ได้ค้นหา';
      householdMetaDisplay.textContent = 'ค้นหาจากเลขที่ชุมชน บ้านเลขที่ หรือค้นหาด่วน';
    }

    function populateHouseholdInfo(h) {
      infoAccountNo.value = h.account_no || '';
      infoContactPerson.value = h.contact_person || '';
      infoCommunity.value = h.community_name ? `${h.community_id} (${h.community_name})` : (h.community_id || '');
      infoHouseNo.value = h.house_no || '';
      infoStatus.value = statusLabel(h.active_status || '');
      infoBalance.value = Number(h.total_balance || 0).toFixed(2);
      householdNameDisplay.textContent = h.contact_person || 'พบครัวเรือนแล้ว';
      householdMetaDisplay.textContent = `${h.account_no || '-'} · คงเหลือ ${Number(h.total_balance || 0).toFixed(2)} บาท`;
      householdInfo.classList.remove('d-none');
    }

    function hideQuickSearchResults() {
      quickSearchMatches = [];
      quickSearchResults.innerHTML = '';
      quickSearchResults.classList.add('d-none');
    }

    function showQuickSearchMessage(message) {
      quickSearchMatches = [];
      quickSearchResults.innerHTML = `<div class="alert alert-light border mb-0">${escapeHtml(message)}</div>`;
      quickSearchResults.classList.remove('d-none');
    }

    function renderQuickSearchResults(matches) {
      quickSearchMatches = matches;
      quickSearchResults.innerHTML = `
        <div class="list-group shadow-sm">
          ${matches.map((household, index) => `
            <button type="button" class="list-group-item list-group-item-action text-start" data-match-index="${index}">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                  <div class="fw-semibold">${escapeHtml(household.contact_person || household.account_no || 'ไม่ระบุชื่อ')}</div>
                  <div class="small text-muted mt-1">
                    บัญชี ${escapeHtml(household.account_no || '-')}
                    · ชุมชน ${escapeHtml(household.community_id || '-')}
                    · บ้านเลขที่ ${escapeHtml(household.house_no || '-')}
                  </div>
                </div>
                <span class="badge text-bg-light border">${escapeHtml(statusLabel(household.active_status || ''))}</span>
              </div>
            </button>
          `).join('')}
        </div>
      `;
      quickSearchResults.classList.remove('d-none');
    }

    function applyHouseholdSelection(household) {
      communityIdInput.value = household.community_id || '';
      houseNoInput.value = household.house_no || '';
      householdError.classList.add('d-none');
      householdError.textContent = '';
      hideQuickSearchResults();
      populateHouseholdInfo(household);
    }

    async function quickSearchHouseholds() {
      const q = quickSearchInput.value.trim();

      householdError.classList.add('d-none');
      householdError.textContent = '';

      if (!q) {
        showQuickSearchMessage('กรุณากรอกคำค้นหาก่อน เช่น เลขบัญชี ชื่อผู้ติดต่อ หรือชื่อสมาชิก');
        return;
      }

      quickSearchBtn.disabled = true;
      quickSearchBtn.textContent = 'กำลังค้นหา...';

      try {
        const url = new URL(quickSearchUrl, window.location.origin);
        url.searchParams.set('q', q);

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!res.ok || !data.found) {
          showQuickSearchMessage(data.message || 'ไม่พบครัวเรือน');
          return;
        }

        if ((data.matches || []).length === 1) {
          applyHouseholdSelection(data.matches[0]);
          showQuickSearchMessage('พบครัวเรือนและเลือกให้แล้ว สามารถกดบันทึกรายการต่อได้เลย');
          return;
        }

        renderQuickSearchResults(data.matches || []);
      } catch (error) {
        showQuickSearchMessage('เกิดข้อผิดพลาดระหว่างค้นหาด่วน กรุณาลองใหม่');
      } finally {
        quickSearchBtn.disabled = false;
        quickSearchBtn.textContent = 'ค้นหาแบบเร็ว';
      }
    }

    async function lookupHousehold() {
      const communityId = communityIdInput.value.trim();
      const houseNo = houseNoInput.value.trim();

      householdInfo.classList.add('d-none');
      householdError.classList.add('d-none');
      householdError.textContent = '';
      clearHouseholdInfo();

      if (!communityId || !houseNo) {
        householdError.textContent = 'กรุณากรอกเลขที่ชุมชนและบ้านเลขที่ก่อนค้นหา';
        householdError.classList.remove('d-none');
        return;
      }

      searchHouseholdBtn.disabled = true;
      searchHouseholdBtn.textContent = 'กำลังค้นหา...';
      try {
        const url = new URL(lookupUrl, window.location.origin);
        url.searchParams.set('community_id', communityId);
        url.searchParams.set('house_no', houseNo);

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json' },
        });

        const data = await res.json();
        if (!res.ok || !data.found) {
          householdError.textContent = data.message || 'ไม่พบครัวเรือน';
          householdError.classList.remove('d-none');
          return;
        }

        const h = data.household;
        populateHouseholdInfo(h);
      } catch (err) {
        householdError.textContent = 'เกิดข้อผิดพลาดระหว่างค้นหา กรุณาลองใหม่';
        householdError.classList.remove('d-none');
      } finally {
        searchHouseholdBtn.disabled = false;
        searchHouseholdBtn.textContent = 'ค้นหา';
      }
    }

    quickSearchBtn.addEventListener('click', quickSearchHouseholds);
    searchHouseholdBtn.addEventListener('click', lookupHousehold);
    quickSearchResults.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-match-index]');
      if (!trigger) return;

      const household = quickSearchMatches[Number(trigger.dataset.matchIndex)];
      if (!household) return;

      applyHouseholdSelection(household);
    });

    quickSearchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        quickSearchHouseholds();
      }
    });

    quickSearchInput.addEventListener('input', () => {
      if (quickSearchInput.value.trim() === '') {
        hideQuickSearchResults();
      }
    });

    [communityIdInput, houseNoInput].forEach((el) => {
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          lookupHousehold();
        }
      });

      el.addEventListener('input', () => {
        householdInfo.classList.add('d-none');
        householdError.classList.add('d-none');
        householdError.textContent = '';
        hideQuickSearchResults();
        clearHouseholdInfo();
      });
    });

    transactionDateInput.addEventListener('input', updateHeaderSummary);

    depositForm.addEventListener('submit', (e) => {
      if (isDepositSubmissionConfirmed) {
        return;
      }

      e.preventDefault();
      syncConfirmationModal();
      bootstrap.Modal.getOrCreateInstance(confirmDepositModalEl).show();
    });

    confirmDepositSubmitBtn.addEventListener('click', () => {
      isDepositSubmissionConfirmed = true;
      confirmDepositSubmitBtn.disabled = true;
      confirmDepositSubmitBtn.textContent = 'กำลังบันทึก...';
      bootstrap.Modal.getOrCreateInstance(confirmDepositModalEl).hide();
      depositForm.requestSubmit();
    });

    confirmDepositModalEl.addEventListener('hidden.bs.modal', () => {
      if (!isDepositSubmissionConfirmed) {
        confirmDepositSubmitBtn.disabled = false;
        confirmDepositSubmitBtn.textContent = 'ยืนยันและบันทึก';
      }
    });

    updateHeaderSummary();
    ensureEmptyRow();
  </script>
</x-layouts.admin>

<x-layouts.admin title="ฝาก/รับซื้อ (Deposit)">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ฝาก/รับซื้อวัสดุ (Deposit)</h3>
    <a class="btn btn-outline-dark" href="{{ route('withdraws.create') }}">ไปหน้า “ถอน”</a>
  </div>

  <form method="POST" action="{{ route('deposits.store') }}" class="bg-white p-3 rounded" id="depositForm">
    @csrf

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">ค้นหาครัวเรือน</label>
        <div class="row g-2">
          <div class="col-md-4">
            <input class="form-control" id="communityIdInput" name="community_id" value="{{ old('community_id') }}"
                   placeholder="เลขที่ชุมชน" inputmode="numeric" maxlength="2" required>
          </div>
          <div class="col-md-5">
            <input class="form-control" id="houseNoInput" name="house_no" value="{{ old('house_no') }}"
                   placeholder="บ้านเลขที่" required>
          </div>
          <div class="col-md-3 d-grid">
            <button type="button" class="btn btn-outline-primary" id="searchHouseholdBtn">ค้นหา</button>
          </div>
        </div>
        <div class="form-text">ระบบจะค้นหาครัวเรือนจากเลขที่ชุมชน + บ้านเลขที่</div>
      </div>

      <div class="col-md-3">
        <label class="form-label">วันที่ทำรายการ</label>
        <input type="date" class="form-control" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">ยอดรวม (คำนวณ)</label>
        <input type="text" class="form-control" id="grandTotal" value="0.00" readonly>
      </div>

      <div class="col-12">
        <div id="householdInfo" class="border rounded-3 bg-light p-3 d-none">
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
        <div id="householdError" class="alert alert-warning mt-2 d-none" role="alert"></div>
      </div>
    </div>

    <hr class="my-3">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">รายการวัสดุ</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" id="openMaterialModalBtn">+ เพิ่มรายการ</button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="itemsTable">
        <thead class="table-light">
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

    <button class="btn btn-success">บันทึกฝาก/รับซื้อ</button>
    <a class="btn btn-secondary" href="{{ route('materials.index') }}">กลับหน้า “วัสดุ”</a>
  </form>

  <div class="modal fade" id="materialPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">เลือกวัสดุ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex flex-wrap gap-2 mb-3" id="materialCategoryFilters"></div>
          <div class="row g-2" id="materialPickerList"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="confirmMaterialSelection">ยืนยัน</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .material-card {
      width: 100%;
      text-align: left;
      border: 1px solid #d0e7dc;
      border-radius: 0.6rem;
      padding: 0.75rem 0.9rem;
      background: #ffffff;
      color: #1f2937;
      transition: all 0.15s ease-in-out;
    }
    .material-card:hover {
      border-color: #74c7a2;
      box-shadow: 0 4px 10px rgba(15, 109, 74, 0.08);
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
      padding: 0.25rem 0.35rem;
      border-left: 4px solid #0f6d4a;
      background: #f3fbf7;
      border-radius: 0.35rem;
    }
  </style>

  <script>
    const materials = @json($materials);
    const currentPrices = @json($currentPrices);
    const lookupUrl = @json(route('deposits.lookup-household'));

    const itemsBody = document.getElementById('itemsBody');
    const openMaterialModalBtn = document.getElementById('openMaterialModalBtn');
    const materialCategoryFilters = document.getElementById('materialCategoryFilters');
    const materialPickerList = document.getElementById('materialPickerList');
    const materialPickerModalEl = document.getElementById('materialPickerModal');
    const confirmMaterialSelection = document.getElementById('confirmMaterialSelection');
    const grandTotalEl = document.getElementById('grandTotal');
    const communityIdInput = document.getElementById('communityIdInput');
    const houseNoInput = document.getElementById('houseNoInput');
    const searchHouseholdBtn = document.getElementById('searchHouseholdBtn');
    const householdInfo = document.getElementById('householdInfo');
    const householdError = document.getElementById('householdError');
    const infoAccountNo = document.getElementById('infoAccountNo');
    const infoContactPerson = document.getElementById('infoContactPerson');
    const infoCommunity = document.getElementById('infoCommunity');
    const infoHouseNo = document.getElementById('infoHouseNo');
    const infoStatus = document.getElementById('infoStatus');
    const infoBalance = document.getElementById('infoBalance');


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
            <td colspan="6" class="text-center text-muted">ยังไม่มีรายการวัสดุ</td>
          </tr>
        `;
      }
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
        <td><input class="form-control weight" type="number" step="0.01" min="0.01" name="items[${rowIndex}][weight]" value="${weight}" required></td>
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
        // รี index ให้ name ถูก (ง่ายสุด: rebuild name)
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

    function syncModalSelection() {
      const selectedIds = new Set(getDataRows().map(tr => tr.querySelector('.material-select').value));
      materialPickerList.querySelectorAll('.material-card').forEach((btn) => {
        const id = btn.getAttribute('data-id');
        btn.classList.toggle('is-selected', selectedIds.has(id));
      });
    }

    materialPickerList.addEventListener('click', (e) => {
      const card = e.target.closest('.material-card');
      if (!card) return;
      card.classList.toggle('is-selected');
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
      const checked = Array.from(materialPickerList.querySelectorAll('.material-card.is-selected'))
        .map((btn) => btn.getAttribute('data-id'));
      const existingIds = new Set(getDataRows().map(tr => tr.querySelector('.material-select').value));
      checked.forEach((id) => {
        if (!existingIds.has(id)) addRow({ material_id: id });
      });
      bootstrap.Modal.getOrCreateInstance(materialPickerModalEl).hide();
    });

    openMaterialModalBtn.addEventListener('click', () => {
      if (!materialPickerList.children.length) {
        renderCategoryFilters();
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
        infoAccountNo.value = h.account_no || '';
        infoContactPerson.value = h.contact_person || '';
        infoCommunity.value = h.community_name ? `${h.community_id} (${h.community_name})` : (h.community_id || '');
        infoHouseNo.value = h.house_no || '';
        infoStatus.value = h.active_status || '';
        infoBalance.value = Number(h.total_balance || 0).toFixed(2);
        householdInfo.classList.remove('d-none');
      } catch (err) {
        householdError.textContent = 'เกิดข้อผิดพลาดระหว่างค้นหา กรุณาลองใหม่';
        householdError.classList.remove('d-none');
      } finally {
        searchHouseholdBtn.disabled = false;
        searchHouseholdBtn.textContent = 'ค้นหา';
      }
    }

    searchHouseholdBtn.addEventListener('click', lookupHousehold);
    [communityIdInput, houseNoInput].forEach((el) => {
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          lookupHousehold();
        }
      });
    });

    ensureEmptyRow();
  </script>
</x-layouts.admin>

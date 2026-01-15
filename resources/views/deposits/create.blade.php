<x-layouts.admin title="ฝาก/รับซื้อ (Deposit)">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ฝาก/รับซื้อวัสดุ (Deposit)</h3>
    <a class="btn btn-outline-dark" href="{{ route('withdraws.create') }}">ไปหน้า “ถอน”</a>
  </div>

  <form method="POST" action="{{ route('deposits.store') }}" class="bg-white p-3 rounded" id="depositForm">
    @csrf

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">บัญชีครัวเรือน (account_no)</label>
        <select class="form-select" name="household_id" required>
          <option value="">-- เลือกครัวเรือน --</option>
          @foreach($households as $h)
            <option value="{{ $h->household_id }}" @selected(old('household_id') == $h->household_id)>
              {{ $h->account_no }} - {{ $h->contact_person }} (คงเหลือ {{ number_format((float)$h->total_balance,2) }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">วันที่ทำรายการ</label>
        <input type="date" class="form-control" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">ยอดรวม (คำนวณ)</label>
        <input type="text" class="form-control" id="grandTotal" value="0.00" readonly>
      </div>
    </div>

    <hr class="my-3">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">รายการวัสดุ</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">+ เพิ่มแถว</button>
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

  <script>
    const materials = @json($materials);
    const currentPrices = @json($currentPrices);

    const itemsBody = document.getElementById('itemsBody');
    const addRowBtn = document.getElementById('addRowBtn');
    const grandTotalEl = document.getElementById('grandTotal');

    function materialOptionsHtml(selectedId = '') {
      return materials.map(m => {
        const sel = String(m.material_id) === String(selectedId) ? 'selected' : '';
        return `<option value="${m.material_id}" ${sel}>${m.material_name}</option>`;
      }).join('');
    }

    function addRow(prefill = {}) {
      const rowIndex = itemsBody.children.length;
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
        Array.from(itemsBody.children).forEach((tr, i) => {
          tr.querySelector('.material-select').name = `items[${i}][material_id]`;
          tr.querySelector('.weight').name = `items[${i}][weight]`;
        });
        recalcGrandTotal();
      }
    });

    addRowBtn.addEventListener('click', () => addRow());

    // เริ่มต้น 1 แถว
    addRow();
  </script>
</x-layouts.admin>

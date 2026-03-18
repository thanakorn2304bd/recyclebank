<x-layouts.admin title="ถอน (Withdraw)">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Cash Withdrawal</div>
      <h1 class="rb-page-title">ถอนเงิน</h1>
      <p class="rb-page-subtitle">
        ตรวจสอบครัวเรือน ยอดคงเหลือ และจำนวนเงินที่จะถอนก่อนบันทึก พร้อมเปิดใบถอน PDF ได้ทันที
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-dark" href="{{ route('deposits.create') }}">ไปหน้า “ฝาก/รับซื้อ”</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">วันที่ทำรายการ</div>
      <div class="rb-stat-value" id="transactionDateDisplay">{{ old('transaction_date', now()->toDateString()) }}</div>
      <div class="rb-stat-meta">เลือกวันที่สำหรับบันทึกและพิมพ์เอกสาร</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดที่จะถอน</div>
      <div class="rb-stat-value" id="withdrawAmountDisplay">{{ old('amount', '0.00') }}</div>
      <div class="rb-stat-meta">ระบบจะตรวจสอบว่ายอดไม่เกินคงเหลือ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ครัวเรือนที่เลือก</div>
      <div class="rb-stat-value rb-stat-value-sm" id="householdNameDisplay">ยังไม่ได้ค้นหา</div>
      <div class="rb-stat-meta" id="householdMetaDisplay">ค้นหาจากเลขที่ชุมชนและบ้านเลขที่</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดคงเหลือ</div>
      <div class="rb-stat-value" id="balanceDisplay">0.00</div>
      <div class="rb-stat-meta">อัปเดตหลังค้นหาครัวเรือนสำเร็จ</div>
    </div>
  </div>

  <form method="POST" action="{{ route('withdraws.store') }}" class="rb-surface p-4 p-lg-4" id="withdrawForm">
    @csrf

    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">ค้นหาครัวเรือนและระบุยอดถอน</h2>
        <p class="rb-card-subtitle">ยืนยันว่าค้นพบครัวเรือนและยอดคงเหลือเพียงพอก่อนบันทึกรายการ</p>
      </div>
      <span class="rb-chip">Step 1</span>
    </div>

    <div class="row g-3">
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
        <label class="form-label">จำนวนเงินที่ถอน</label>
        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
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

    <div class="d-flex flex-wrap gap-2 mt-4">
      <button type="button" class="btn btn-outline-secondary" id="previewWithdrawPdfBtn">ตรวจสอบไฟล์ PDF</button>
      <button class="btn btn-primary">บันทึกและพิมพ์ใบถอน PDF</button>
      <a class="btn btn-outline-secondary" href="{{ route('materials.index') }}">กลับหน้า “วัสดุ”</a>
    </div>

    <div class="form-text mt-2">
      ระบบจะเปิดเอกสารใบถอนเงินสำหรับพิมพ์และลงลายมือชื่อทันทีหลังบันทึกรายการ
    </div>
  </form>

  <style>
    .rb-stat-value-sm {
      font-size: 1.35rem;
    }
  </style>

  <script>
    const lookupUrl = @json(route('deposits.lookup-household'));
    const previewUrl = @json(route('withdraws.preview'));
    const withdrawForm = document.getElementById('withdrawForm');
    const communityIdInput = document.getElementById('communityIdInput');
    const houseNoInput = document.getElementById('houseNoInput');
    const searchHouseholdBtn = document.getElementById('searchHouseholdBtn');
    const previewWithdrawPdfBtn = document.getElementById('previewWithdrawPdfBtn');
    const householdInfo = document.getElementById('householdInfo');
    const householdError = document.getElementById('householdError');
    const infoAccountNo = document.getElementById('infoAccountNo');
    const infoContactPerson = document.getElementById('infoContactPerson');
    const infoCommunity = document.getElementById('infoCommunity');
    const infoHouseNo = document.getElementById('infoHouseNo');
    const infoStatus = document.getElementById('infoStatus');
    const infoBalance = document.getElementById('infoBalance');
    const transactionDateInput = withdrawForm.elements.namedItem('transaction_date');
    const amountInput = withdrawForm.elements.namedItem('amount');
    const transactionDateDisplay = document.getElementById('transactionDateDisplay');
    const withdrawAmountDisplay = document.getElementById('withdrawAmountDisplay');
    const householdNameDisplay = document.getElementById('householdNameDisplay');
    const householdMetaDisplay = document.getElementById('householdMetaDisplay');
    const balanceDisplay = document.getElementById('balanceDisplay');

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

    function updateSummary() {
      transactionDateDisplay.textContent = formatDateDisplay(transactionDateInput.value);
      withdrawAmountDisplay.textContent = Number(amountInput.value || 0).toFixed(2);
    }

    function clearHouseholdInfo() {
      infoAccountNo.value = '';
      infoContactPerson.value = '';
      infoCommunity.value = '';
      infoHouseNo.value = '';
      infoStatus.value = '';
      infoBalance.value = '';
      householdNameDisplay.textContent = 'ยังไม่ได้ค้นหา';
      householdMetaDisplay.textContent = 'ค้นหาจากเลขที่ชุมชนและบ้านเลขที่';
      balanceDisplay.textContent = '0.00';
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
        householdNameDisplay.textContent = h.contact_person || 'พบครัวเรือนแล้ว';
        householdMetaDisplay.textContent = `${h.account_no || '-'} · ${h.community_name || h.community_id || ''}`;
        balanceDisplay.textContent = Number(h.total_balance || 0).toFixed(2);
        householdInfo.classList.remove('d-none');
      } catch (err) {
        householdError.textContent = 'เกิดข้อผิดพลาดระหว่างค้นหา กรุณาลองใหม่';
        householdError.classList.remove('d-none');
        return false;
      } finally {
        searchHouseholdBtn.disabled = false;
        searchHouseholdBtn.textContent = 'ค้นหา';
      }

      return true;
    }

    searchHouseholdBtn.addEventListener('click', lookupHousehold);

    previewWithdrawPdfBtn.addEventListener('click', async () => {
      const communityId = communityIdInput.value.trim();
      const houseNo = houseNoInput.value.trim();
      const transactionDate = transactionDateInput.value.trim();
      const amount = amountInput.value.trim();

      householdError.classList.add('d-none');
      householdError.textContent = '';

      if (!communityId || !houseNo || !transactionDate || !amount) {
        householdError.textContent = 'กรุณากรอกข้อมูลให้ครบก่อนตรวจสอบไฟล์ PDF';
        householdError.classList.remove('d-none');
        return;
      }

      const found = await lookupHousehold();
      if (!found) {
        return;
      }

      const balance = parseFloat(infoBalance.value || '0');
      const requestedAmount = parseFloat(amount || '0');

      if (!Number.isFinite(requestedAmount) || requestedAmount <= 0) {
        householdError.textContent = 'กรุณาระบุจำนวนเงินที่ถอนให้ถูกต้อง';
        householdError.classList.remove('d-none');
        return;
      }

      if (requestedAmount > balance) {
        householdError.textContent = `ยอดเงินไม่พอ (คงเหลือ ${balance.toFixed(2)})`;
        householdError.classList.remove('d-none');
        return;
      }

      const url = new URL(previewUrl, window.location.origin);
      url.searchParams.set('community_id', communityId);
      url.searchParams.set('house_no', houseNo);
      url.searchParams.set('transaction_date', transactionDate);
      url.searchParams.set('amount', requestedAmount.toFixed(2));

      window.open(url.toString(), '_blank', 'noopener');
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
        clearHouseholdInfo();
      });
    });

    [transactionDateInput, amountInput].forEach((el) => {
      el.addEventListener('input', () => {
        householdError.classList.add('d-none');
        householdError.textContent = '';
        updateSummary();
      });
    });

    updateSummary();

    if (communityIdInput.value.trim() && houseNoInput.value.trim()) {
      lookupHousehold();
    }
  </script>
</x-layouts.admin>

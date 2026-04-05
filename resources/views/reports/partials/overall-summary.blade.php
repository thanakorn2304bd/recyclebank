<div class="rb-overall-summary p-4 mb-4">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
      <div class="rb-section-title mb-1">ยอดรวมทั้งหมด</div>
      <div class="small text-muted">
        ตัวเลขชุดนี้ไม่เปลี่ยนตามตัวกรองหรือการค้นหาด้านล่าง
      </div>
    </div>
    <span class="badge text-bg-light">
      {{ $isPrivileged ? 'ภาพรวมทั้งระบบ' : 'ภาพรวมของบัญชีนี้' }}
    </span>
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="rb-overall-card h-100">
        <div class="rb-overall-label">ยอดรับซื้อทั้งหมด</div>
        <div class="rb-overall-value text-success">{{ number_format($overallSummary['depositAmount'], 2) }}</div>
        <div class="rb-overall-note">
          {{ number_format($overallSummary['depositCount']) }} รายการ · น้ำหนักสะสม {{ number_format($overallSummary['depositWeight'], 2) }} กก.
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="rb-overall-card h-100">
        <div class="rb-overall-label">ยอดถอนทั้งหมด</div>
        <div class="rb-overall-value text-warning-emphasis">{{ number_format($overallSummary['withdrawAmount'], 2) }}</div>
        <div class="rb-overall-note">
          {{ number_format($overallSummary['withdrawCount']) }} รายการ · รายการรวมทั้งหมด {{ number_format($overallSummary['transactionCount']) }} ครั้ง
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="rb-overall-card h-100">
        <div class="rb-overall-label">ยอดคงเหลือทั้งหมด</div>
        <div class="rb-overall-value">{{ number_format($overallSummary['totalBalance'], 2) }}</div>
        <div class="rb-overall-note">
          {{ $isPrivileged ? 'รวมจาก '.number_format($overallSummary['householdCount']).' ครัวเรือน' : 'ยอดคงเหลือปัจจุบันของครัวเรือนนี้' }}
        </div>
      </div>
    </div>
  </div>
</div>

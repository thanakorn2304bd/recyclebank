<div class="rb-page-header">
  <div>
    <div class="rb-page-kicker">Monthly Price Publisher</div>
    <h1 class="rb-page-title">จัดชุดราคาวัสดุรายเดือน</h1>
    <p class="rb-page-subtitle">
      เลือกเดือนปัจจุบันหรือเดือนล่วงหน้า แล้วจัดชุดราคาของวัสดุทั้งหมดในรอบเดียว รายการที่ไม่แก้จะยึดค่าตั้งต้นจากเดือนก่อนและเผยแพร่เข้าเดือนที่เลือกให้อัตโนมัติ
    </p>
  </div>
  <div class="rb-page-actions">
    <span class="rb-chip">เดือนเป้าหมาย {{ $targetMonthLabel }}</span>
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
    <div class="rb-stat-label">เผยแพร่แล้วในเดือนนี้</div>
    <div class="rb-stat-value">{{ number_format($summary['published']) }}</div>
    <div class="rb-stat-meta">มีชุดราคาเริ่มวันที่ {{ \Carbon\Carbon::parse($monthStart)->format('d/m/Y') }}</div>
  </div>
  <div class="rb-stat-card">
    <div class="rb-stat-label">พร้อมคัดลอกจากเดือนก่อน</div>
    <div class="rb-stat-value">{{ number_format($summary['carry_forward']) }}</div>
    <div class="rb-stat-meta">หากไม่แก้จะใช้ค่าตั้งต้นเดิมเมื่อเผยแพร่</div>
  </div>
  <div class="rb-stat-card">
    <div class="rb-stat-label">ยังต้องกำหนดราคาเอง</div>
    <div class="rb-stat-value">{{ number_format($summary['missing']) }}</div>
    <div class="rb-stat-meta">วัสดุที่ยังไม่มีราคาตั้งต้นสำหรับเดือนที่เลือก</div>
  </div>
</div>

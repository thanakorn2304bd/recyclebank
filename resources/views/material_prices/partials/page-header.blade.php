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

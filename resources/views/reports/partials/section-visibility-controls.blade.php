<div class="rb-section-switcher mb-4" data-report-visibility-controls data-storage-key="rb-report-sections-{{ $storageKey }}">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
    <div>
      <div class="rb-section-title mb-1">เลือกข้อมูลที่ต้องการแสดง</div>
      <div class="small text-muted">ติ๊กเฉพาะส่วนที่อยากดู ระบบจะซ่อนส่วนที่ไม่เลือกและจำค่าไว้ในเครื่องนี้</div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-select-all>แสดงทั้งหมด</button>
  </div>

  <div class="d-flex flex-wrap gap-2 mt-3">
    @foreach($sectionOptions as $option)
      <label class="rb-section-toggle-pill is-active">
        <input
          class="form-check-input"
          type="checkbox"
          value="{{ $option['id'] }}"
          data-target="{{ $option['id'] }}"
          checked
        >
        <span>{{ $option['label'] }}</span>
      </label>
    @endforeach
  </div>
</div>

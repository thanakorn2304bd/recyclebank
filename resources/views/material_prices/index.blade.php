<x-layouts.admin title="ราคา">
  @php
    $pagePrices = $prices->getCollection();
    $currentPrices = $pagePrices->whereNull('expired_date')->count();
  @endphp

  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Price Management</div>
      <h1 class="rb-page-title">ราคาวัสดุ</h1>
      <p class="rb-page-subtitle">
        ตรวจสอบราคาปัจจุบันย้อนหลังตามวันเริ่มใช้ กรองตามวัสดุ และจัดการรายการราคาที่ไม่ต้องการได้จากหน้านี้
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-primary" href="{{ route('material-prices.create') }}">+ เพิ่มราคา</a>
      <a class="btn btn-outline-secondary" href="{{ route('materials.index') }}">ดูวัสดุ</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">รายการราคา</div>
      <div class="rb-stat-value">{{ number_format($prices->total()) }}</div>
      <div class="rb-stat-meta">กำลังแสดง {{ number_format($prices->count()) }} รายการ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ราคาปัจจุบันในหน้า</div>
      <div class="rb-stat-value">{{ number_format($currentPrices) }}</div>
      <div class="rb-stat-meta">รายการที่ยังไม่กำหนดวันสิ้นสุดราคา</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">ตัวกรองวัสดุ</div>
      <div class="rb-stat-value">{{ $materialId ? '1' : 'ทั้งหมด' }}</div>
      <div class="rb-stat-meta">{{ $materialId ? 'กำลังดูเฉพาะวัสดุที่เลือก' : 'แสดงทุกวัสดุในระบบ' }}</div>
    </div>
  </div>

  <form class="rb-surface p-4 mb-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">กรองตามวัสดุ</h2>
        <p class="rb-card-subtitle">เลือกวัสดุเพื่อดูประวัติราคาเฉพาะรายการได้อย่างรวดเร็ว</p>
      </div>
      @if($materialId)
        <span class="rb-chip">กำลังกรองเฉพาะวัสดุที่เลือก</span>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-lg-6">
        <label class="form-label">วัสดุ</label>
        <select class="form-select" name="material_id">
          <option value="">ทุกวัสดุ</option>
          @foreach($materials as $m)
            <option value="{{ $m->material_id }}" @selected((string)$materialId === (string)$m->material_id)>
              {{ $m->material_name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-3 d-flex align-items-end">
        <button class="btn btn-primary w-100">กรอง</button>
      </div>
      <div class="col-lg-3 d-flex align-items-end">
        <a class="btn btn-outline-secondary w-100" href="{{ route('material-prices.index') }}">ล้าง</a>
      </div>
    </div>
  </form>

  <div class="rb-surface p-3 p-lg-4">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">รายการราคา</h2>
        <p class="rb-card-subtitle">เปรียบเทียบราคาและช่วงเวลาที่ใช้จริง พร้อมลบรายการที่ไม่ต้องการได้จากตารางนี้</p>
      </div>
      <span class="rb-chip">
        {{ number_format($prices->firstItem() ?? 0) }}-{{ number_format($prices->lastItem() ?? 0) }}
        / {{ number_format($prices->total()) }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle" data-sortable-table>
        <thead>
          <tr>
            <th>วัสดุ</th>
            <th style="width:120px;" class="text-end" data-sort-type="number">ราคา</th>
            <th style="width:150px;" data-sort-type="date">เริ่มใช้</th>
            <th style="width:150px;" data-sort-type="date">หมดอายุ</th>
            <th style="width:120px;" data-sortable="false"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($prices as $p)
            <tr>
              <td>{{ $p->material?->material_name }}</td>
              <td class="text-end">{{ number_format((float)$p->price, 2) }}</td>
              <td>{{ $p->effective_date }}</td>
              <td>{{ $p->expired_date ?? '-' }}</td>
              <td class="text-end">
                <form class="d-inline" method="POST" action="{{ route('material-prices.destroy', $p) }}" onsubmit="return confirm('ลบราคานี้?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">ลบ</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">ไม่พบรายการราคาตามเงื่อนไขที่เลือก</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $prices->links() }}
  </div>
</x-layouts.admin>

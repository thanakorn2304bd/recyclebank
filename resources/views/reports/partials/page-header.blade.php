<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
  <div>
    <h3 class="mb-1">สรุปรายงาน</h3>
    <div class="text-muted">
      ช่วงข้อมูล {{ $periodLabel }}
      @if($selectedCommunity)
        · ชุมชน {{ $selectedCommunity->community_id }} - {{ $selectedCommunity->community_name }}
      @endif
      @if($statusText)
        · สถานะ {{ $statusText }}
      @endif
      @if($householdQuery)
        · Quick Search {{ $householdQuery }}
      @endif
      @if($selectedHouseholdSearchCommunity)
        · ชุมชนที่ค้นหา {{ $selectedHouseholdSearchCommunity->community_id }} - {{ $selectedHouseholdSearchCommunity->community_name }}
      @endif
      @if($householdSearchHouseNo)
        · บ้านเลขที่ {{ $householdSearchHouseNo }}
      @endif
      @if($selectedCategory)
        · หมวดวัสดุ {{ $selectedCategory->category_name }}
      @endif
    </div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-dark" href="{{ route('reports.export.pdf', request()->query()) }}">Export PDF</a>
    <a class="btn btn-outline-dark" href="{{ route('reports.export.excel', request()->query()) }}">Export Excel</a>
    <a class="btn btn-outline-dark" href="{{ route('transactions.index', request()->query()) }}">ดูประวัติรายการ</a>
    <a class="btn btn-success" href="{{ route('main-menu') }}">กลับเมนูหลัก</a>
  </div>
</div>

<x-layouts.admin title="แก้ไขราคาวัสดุ">
  <style>
    .rb-price-editor-row-dirty > * {
      background: linear-gradient(180deg, rgba(255, 251, 235, 0.95) 0%, rgba(255, 255, 255, 0.96) 100%) !important;
    }

    .rb-price-editor-grid {
      display: grid;
      gap: 1rem;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .rb-price-status {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      border-radius: 999px;
      padding: 0.4rem 0.75rem;
      font-size: 0.82rem;
      font-weight: 600;
    }

    .rb-price-status-current {
      background: #eaf8f1;
      color: #116149;
      border: 1px solid #b8e4cc;
    }

    .rb-price-status-missing {
      background: #fff7e8;
      color: #9a5b00;
      border: 1px solid #f7ddb0;
    }

    .rb-price-meta {
      color: #5f766a;
      font-size: 0.82rem;
    }
  </style>

  @include('material_prices.partials.page-header')
  @include('material_prices.partials.filter-form')
  @include('material_prices.partials.editor-table')
  @include('material_prices.partials.editor-script')
</x-layouts.admin>

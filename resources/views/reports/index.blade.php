<x-layouts.admin title="สรุปรายงาน">
  <style>
    .rb-report-card {
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      box-shadow: 0 12px 28px rgba(15, 109, 74, 0.08);
    }

    .rb-metric-card {
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      background: linear-gradient(160deg, #ffffff 0%, #f2fbf6 100%);
      box-shadow: 0 12px 30px rgba(15, 109, 74, 0.06);
    }

    .rb-metric-label {
      font-size: 0.82rem;
      color: #4b6b5c;
      margin-bottom: 0.35rem;
    }

    .rb-metric-value {
      font-size: 1.55rem;
      font-weight: 700;
      color: #0f5132;
      line-height: 1.1;
    }

    .rb-metric-note {
      color: #5f6b66;
      font-size: 0.85rem;
    }

    .rb-section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #0f5132;
    }

    .rb-bar-track {
      height: 0.45rem;
      border-radius: 999px;
      background: #e7f5ed;
      overflow: hidden;
    }

    .rb-bar-fill {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, #17a97a 0%, #0f6d4a 100%);
    }

    .rb-mini-list > div + div {
      border-top: 1px solid #edf7f1;
    }

    .rb-table td,
    .rb-table th {
      vertical-align: middle;
    }

    .rb-filter-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      border-radius: 999px;
      background: #eef8f2;
      border: 1px solid #d7f0e3;
      padding: 0.45rem 0.8rem;
      font-size: 0.82rem;
      color: #0f5132;
    }

    .rb-chart-card {
      border: 1px solid #d7f0e3;
      border-radius: 1rem;
      background: linear-gradient(180deg, #fcfffd 0%, #f4fbf7 100%);
      box-shadow: 0 16px 30px rgba(15, 109, 74, 0.07);
    }

    .rb-chart-shell {
      position: relative;
      min-height: 290px;
    }

    .rb-chart-shell canvas {
      width: 100% !important;
      height: 290px !important;
    }

    .rb-chart-shell.rb-chart-shell-sm {
      min-height: 250px;
    }

    .rb-chart-shell.rb-chart-shell-sm canvas {
      height: 250px !important;
    }

    .rb-note {
      border-left: 4px solid #17a97a;
      background: #f2fbf6;
      color: #325244;
      padding: 0.85rem 1rem;
      border-radius: 0.85rem;
      font-size: 0.9rem;
    }
  </style>

  @include('reports.partials.page-header')
  @include('reports.partials.filter-bar')

  @if($isPrivileged)
    @include('reports.partials.privileged-dashboard')
  @else
    @include('reports.partials.member-dashboard')
  @endif

  @include('reports.partials.recent-transactions')
  @include('reports.partials.chart-scripts')
</x-layouts.admin>

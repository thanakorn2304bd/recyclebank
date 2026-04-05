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

    .rb-overall-summary {
      border: 1px solid #cdebdc;
      border-radius: 1.35rem;
      background:
        radial-gradient(circle at top left, rgba(23, 169, 122, 0.16), transparent 34%),
        linear-gradient(135deg, #fcfffd 0%, #edf9f2 100%);
      box-shadow: 0 20px 38px rgba(15, 109, 74, 0.08);
    }

    .rb-overall-card {
      border: 1px solid rgba(15, 109, 74, 0.08);
      border-radius: 1.1rem;
      background: rgba(255, 255, 255, 0.92);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
      padding: 1.2rem 1.25rem;
    }

    .rb-overall-label {
      font-size: 0.95rem;
      font-weight: 700;
      color: #2f5b49;
      margin-bottom: 0.55rem;
    }

    .rb-overall-value {
      font-size: clamp(2rem, 3vw, 2.8rem);
      font-weight: 800;
      color: #0f5132;
      line-height: 1;
      letter-spacing: -0.03em;
    }

    .rb-overall-note {
      margin-top: 0.7rem;
      color: #5f6b66;
      font-size: 0.92rem;
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

    .rb-report-filters {
      padding: 1rem !important;
      margin-bottom: 0.75rem !important;
    }

    .rb-report-filter-card {
      padding: 0.9rem 1rem !important;
    }

    .rb-report-filters .rb-section-head {
      gap: 0.5rem;
      margin-bottom: 0.75rem;
    }

    .rb-report-filters .rb-card-subtitle {
      line-height: 1.45;
    }

    .rb-report-filters .row {
      --bs-gutter-x: 1rem;
      --bs-gutter-y: 0.75rem;
    }

    .rb-report-filters .form-label {
      margin-bottom: 0.35rem;
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

    .rb-report-note {
      padding: 0.7rem 0.9rem;
      margin-bottom: 0.75rem !important;
    }
  </style>

  @include('reports.partials.page-header')
  @include('reports.partials.overall-summary')
  @include('reports.partials.filter-bar')

  @if($isPrivileged)
    @include('reports.partials.privileged-dashboard')
  @else
    @include('reports.partials.member-dashboard')
  @endif

  @include('reports.partials.recent-transactions')
  @include('reports.partials.chart-scripts')
</x-layouts.admin>

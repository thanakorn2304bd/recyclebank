<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'RecycleBank' }}</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=kanit:400,500,600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --rb-forest: #0d3f31;
      --rb-green: #0f6d4a;
      --rb-green-2: #17a97a;
      --rb-green-3: #34d399;
      --rb-green-100: #eef9f3;
      --rb-green-200: #d7f0e3;
      --rb-green-300: #b7e5ce;
      --rb-border: #d7e8df;
      --rb-text: #1f2937;
      --rb-text-soft: #5f766a;
      --rb-surface: rgba(255, 255, 255, 0.92);
      --rb-shadow: 0 22px 55px rgba(15, 109, 74, 0.08);
      --rb-shadow-soft: 0 12px 28px rgba(15, 109, 74, 0.06);
    }

    body {
      min-height: 100vh;
      font-family: 'Kanit', ui-sans-serif, system-ui, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(52, 211, 153, 0.16), transparent 30%),
        radial-gradient(circle at top right, rgba(20, 184, 166, 0.14), transparent 26%),
        linear-gradient(180deg, #f4fbf7 0%, #edf8f3 42%, #f9fcfb 100%);
      color: var(--rb-text);
    }

    a {
      text-decoration: none;
    }

    .rb-app-shell {
      min-height: 100vh;
    }

    .rb-topbar {
      position: sticky;
      top: 0;
      z-index: 1030;
      background: rgba(8, 45, 33, 0.85);
      border-bottom: 1px solid rgba(196, 245, 220, 0.16);
      backdrop-filter: blur(14px);
      box-shadow: 0 18px 40px rgba(8, 45, 33, 0.16);
    }

    .navbar-brand {
      color: #e8fff3;
      font-weight: 600;
      letter-spacing: 0.15px;
      white-space: nowrap;
    }

    .navbar-brand:hover {
      color: #ffffff;
    }

    .navbar-nav {
      gap: 0.2rem;
      flex-wrap: nowrap;
    }

    .rb-topbar .nav-link {
      color: rgba(232, 255, 243, 0.86);
      font-weight: 500;
      border-radius: 999px;
      padding: 0.46rem 0.78rem;
      font-size: 0.95rem;
      white-space: nowrap;
      transition: all 0.16s ease-in-out;
    }

    .rb-topbar .nav-link:hover,
    .rb-topbar .nav-link.active {
      background: rgba(255, 255, 255, 0.14);
      color: #ffffff;
      transform: translateY(-1px);
    }

    .rb-topbar .nav-link.rb-nav-link--deposit,
    .rb-topbar .nav-link.rb-nav-link--withdraw {
      color: #ffffff;
      box-shadow: 0 12px 22px rgba(0, 0, 0, 0.14);
    }

    .rb-topbar .nav-link.rb-nav-link--deposit {
      background: linear-gradient(135deg, #16a34a 0%, #10b981 100%);
      border: 1px solid rgba(167, 243, 208, 0.3);
    }

    .rb-topbar .nav-link.rb-nav-link--deposit:hover,
    .rb-topbar .nav-link.rb-nav-link--deposit.active {
      background: linear-gradient(135deg, #15803d 0%, #0f9f6e 100%);
      color: #ffffff;
      transform: translateY(-1px);
    }

    .rb-topbar .nav-link.rb-nav-link--withdraw {
      background: linear-gradient(135deg, #ea580c 0%, #f59e0b 100%);
      border: 1px solid rgba(253, 186, 116, 0.34);
    }

    .rb-topbar .nav-link.rb-nav-link--withdraw:hover,
    .rb-topbar .nav-link.rb-nav-link--withdraw.active {
      background: linear-gradient(135deg, #c2410c 0%, #d97706 100%);
      color: #ffffff;
      transform: translateY(-1px);
    }

    .navbar-toggler {
      border-color: rgba(255, 255, 255, 0.24);
      border-radius: 1rem;
      padding: 0.5rem 0.7rem;
    }

    .navbar-toggler:focus {
      box-shadow: 0 0 0 0.25rem rgba(217, 245, 230, 0.18);
    }

    .container {
      max-width: 1240px;
    }

    .rb-main {
      padding: 2rem 0 3rem;
    }

    .rb-user-panel {
      border: 1px solid rgba(217, 245, 230, 0.16);
      border-radius: 1.2rem;
      background: rgba(255, 255, 255, 0.08);
      padding: 0.5rem 0.65rem 0.5rem 0.85rem;
      color: #effcf5;
      white-space: nowrap;
    }

    .rb-user-meta {
      display: flex;
      align-items: baseline;
      gap: 0.45rem;
      white-space: nowrap;
    }

    .rb-user-name {
      font-size: 0.95rem;
      font-weight: 600;
      line-height: 1;
    }

    .rb-user-role {
      color: rgba(232, 255, 243, 0.72);
      font-size: 0.76rem;
      line-height: 1;
    }

    .rb-user-panel .btn-outline-light {
      border-radius: 0.9rem;
      border-color: rgba(255, 255, 255, 0.24);
      color: #f2fff7;
      background: rgba(255, 255, 255, 0.02);
    }

    .rb-user-panel .btn-outline-light:hover,
    .rb-user-panel .btn-outline-light:focus {
      background: rgba(255, 255, 255, 0.14);
      color: #ffffff;
      border-color: rgba(255, 255, 255, 0.28);
    }

    .rb-flash-stack {
      display: grid;
      gap: 0.9rem;
      margin-bottom: 1.5rem;
    }

    .rb-page-header {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: end;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .rb-page-kicker {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      margin-bottom: 0.8rem;
      padding: 0.45rem 0.8rem;
      border-radius: 999px;
      border: 1px solid var(--rb-green-200);
      background: rgba(238, 249, 243, 0.9);
      color: var(--rb-green);
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .rb-page-title {
      margin: 0;
      font-size: clamp(1.75rem, 2vw + 1rem, 2.5rem);
      font-weight: 700;
      line-height: 1.05;
      color: #0d5134;
    }

    .rb-page-subtitle {
      margin: 0.45rem 0 0;
      max-width: 56rem;
      color: var(--rb-text-soft);
      font-size: 0.98rem;
      line-height: 1.7;
    }

    .rb-page-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.75rem;
    }

    .rb-stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      margin-bottom: 1.35rem;
    }

    .rb-stat-card {
      border: 1px solid var(--rb-green-200);
      border-radius: 1.25rem;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(242, 251, 246, 0.96) 100%);
      box-shadow: var(--rb-shadow-soft);
      padding: 1rem 1.1rem;
    }

    .rb-stat-label {
      margin-bottom: 0.4rem;
      color: #4c6658;
      font-size: 0.79rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .rb-stat-value {
      color: #0b4d32;
      font-size: 1.85rem;
      font-weight: 700;
      line-height: 1.1;
    }

    .rb-stat-meta {
      margin-top: 0.5rem;
      color: var(--rb-text-soft);
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .rb-surface,
    .card,
    .modal-content {
      border: 1px solid var(--rb-green-200);
      border-radius: 1.25rem;
      background: var(--rb-surface);
      box-shadow: var(--rb-shadow);
    }

    form.bg-white,
    div.bg-white,
    table.bg-white {
      border: 1px solid var(--rb-green-200);
      border-radius: 1.15rem !important;
      background: rgba(255, 255, 255, 0.96) !important;
      box-shadow: var(--rb-shadow-soft);
    }

    .card {
      overflow: hidden;
    }

    .rb-section-head {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: end;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }

    .rb-card-title {
      margin: 0;
      color: #0d5134;
      font-size: 1.08rem;
      font-weight: 700;
    }

    .rb-card-subtitle {
      margin: 0.28rem 0 0;
      color: var(--rb-text-soft);
      font-size: 0.9rem;
      line-height: 1.6;
    }

    .rb-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      border-radius: 999px;
      border: 1px solid var(--rb-green-200);
      background: #edf8f2;
      color: #1a6141;
      padding: 0.42rem 0.8rem;
      font-size: 0.82rem;
      font-weight: 500;
    }

    .form-control,
    .form-select {
      min-height: 48px;
      border-radius: 0.95rem;
      border-color: var(--rb-border);
      background: rgba(255, 255, 255, 0.96);
      padding-inline: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--rb-green-3);
      box-shadow: 0 0 0 0.22rem rgba(16, 185, 129, 0.16);
    }

    .form-label {
      margin-bottom: 0.55rem;
      color: #294539;
      font-size: 0.92rem;
      font-weight: 600;
    }

    .form-text {
      color: #648072;
      font-size: 0.84rem;
      line-height: 1.55;
    }

    .input-group > .form-control,
    .input-group > .form-select,
    .input-group-text {
      min-height: 48px;
      border-radius: 0.95rem;
    }

    .btn {
      border-radius: 0.95rem;
      padding: 0.72rem 1rem;
      font-weight: 600;
      transition: all 0.15s ease-in-out;
    }

    .btn:hover {
      transform: translateY(-1px);
    }

    .btn-sm {
      border-radius: 0.8rem;
      padding: 0.45rem 0.78rem;
    }

    .btn-primary,
    .btn-success {
      background: linear-gradient(135deg, var(--rb-green-2) 0%, var(--rb-green) 100%);
      border-color: transparent;
      box-shadow: 0 14px 24px rgba(15, 109, 74, 0.18);
    }

    .btn-primary:hover,
    .btn-success:hover,
    .btn-primary:focus,
    .btn-success:focus {
      background: linear-gradient(135deg, #14976d 0%, #0c5c3f 100%);
      border-color: transparent;
    }

    .btn-outline-primary {
      color: var(--rb-green);
      border-color: #9fdac3;
      background: rgba(255, 255, 255, 0.92);
    }

    .btn-outline-primary:hover,
    .btn-outline-primary:focus {
      background: #eaf8f1;
      border-color: #71c6a4;
      color: #0b4d32;
    }

    .btn-outline-dark {
      color: var(--rb-green);
      border-color: #9fdac3;
      background: rgba(255, 255, 255, 0.92);
    }

    .btn-outline-dark:hover,
    .btn-outline-dark:focus {
      background: var(--rb-green);
      border-color: var(--rb-green);
      color: #fff;
    }

    .btn-secondary {
      background: #6b7280;
      border-color: #6b7280;
    }

    .btn-outline-secondary {
      color: #54635b;
      border-color: #cddbd4;
      background: rgba(255, 255, 255, 0.92);
    }

    .btn-outline-secondary:hover,
    .btn-outline-secondary:focus {
      background: #f2f6f4;
      border-color: #b9ccc2;
      color: #30463b;
    }

    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
      transform: translateY(-1px);
    }

    .badge {
      border-radius: 999px;
      padding: 0.48rem 0.72rem;
      font-weight: 600;
      letter-spacing: 0.01em;
    }

    hr {
      border-color: rgba(15, 109, 74, 0.12);
    }

    .alert {
      border-radius: 1rem;
      border-width: 1px;
      box-shadow: var(--rb-shadow-soft);
      padding: 1rem 1.1rem;
    }

    .alert-success {
      background: #edf9f2;
      border-color: #cdebd9;
      color: #17553a;
    }

    .alert-danger {
      background: #fff6f5;
      border-color: #f5d2cf;
      color: #8b2e28;
    }

    .alert-warning,
    .alert-info {
      border-color: #e9e1b7;
    }

    .table-responsive {
      border-radius: 1.15rem;
      border: 1px solid var(--rb-green-200);
      background: rgba(255, 255, 255, 0.96);
      box-shadow: var(--rb-shadow-soft);
      overflow: hidden;
    }

    .table {
      margin-bottom: 0;
      border-color: var(--rb-border);
      vertical-align: middle;
    }

    .table > :not(caption) > * > * {
      padding: 0.95rem 1rem;
      background: transparent;
    }

    .table thead th {
      background: #f1fbf5;
      color: #166534;
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .table-striped > tbody > tr:nth-of-type(odd) > * {
      --bs-table-accent-bg: rgba(246, 251, 248, 0.92);
    }

    .table tbody tr:hover {
      background: rgba(241, 251, 245, 0.8);
    }

    .table[data-sortable-table] thead th[data-sortable="false"] {
      cursor: default;
    }

    .pagination {
      gap: 0.35rem;
      margin-top: 1rem;
    }

    .page-link {
      min-width: 42px;
      border-radius: 0.85rem;
      border-color: var(--rb-green-200);
      color: var(--rb-green);
      box-shadow: 0 6px 16px rgba(15, 109, 74, 0.05);
    }

    .page-link:hover {
      color: #0b4d32;
      background: #edf8f2;
      border-color: #a2dcc5;
    }

    .page-item.active .page-link {
      background: linear-gradient(135deg, var(--rb-green-2) 0%, var(--rb-green) 100%);
      border-color: transparent;
      box-shadow: 0 10px 20px rgba(15, 109, 74, 0.16);
    }

    .page-item.disabled .page-link {
      background: rgba(255, 255, 255, 0.72);
      border-color: var(--rb-green-200);
    }

    .rb-info-panel {
      border: 1px dashed var(--rb-green-300);
      border-radius: 1.1rem;
      background: linear-gradient(180deg, #fbfffc 0%, #f3fbf7 100%);
      padding: 1rem;
    }

    .rb-info-panel .form-control[readonly] {
      background: rgba(255, 255, 255, 0.98);
    }

    .rb-empty-state {
      border: 1px dashed var(--rb-green-300);
      border-radius: 1rem;
      background: #f7fcf9;
      color: var(--rb-text-soft);
      padding: 1.2rem;
      text-align: center;
    }

    .rb-detail-list dt {
      color: var(--rb-text-soft);
      font-weight: 500;
    }

    .rb-detail-list dd {
      color: #143728;
      font-weight: 500;
    }

    .rb-sort-button {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      width: 100%;
      padding: 0;
      border: 0;
      background: transparent;
      color: inherit;
      font: inherit;
      font-weight: inherit;
      text-align: inherit;
    }

    .rb-sort-button:hover {
      color: #0f6d4a;
    }

    .rb-sort-button:focus-visible {
      outline: 2px solid rgba(23, 169, 122, 0.35);
      outline-offset: 2px;
      border-radius: 0.35rem;
    }

    .rb-sort-label {
      min-width: 0;
    }

    .rb-sort-indicator {
      flex-shrink: 0;
      color: #9ca3af;
      font-size: 0.8em;
      line-height: 1;
    }

    th.rb-sort-active .rb-sort-indicator {
      color: #0f6d4a;
    }

    @media (max-width: 1199.98px) {
      .rb-topbar .navbar-collapse {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid rgba(217, 245, 230, 0.14);
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.08);
      }

      .rb-user-panel {
        margin-top: 0.75rem;
      }
    }

    @media (max-width: 991.98px) {
      .rb-main {
        padding-top: 1.5rem;
      }

      .rb-page-header {
        align-items: start;
      }
    }
  </style>
</head>
<body>
<div class="rb-app-shell">
  <nav class="navbar navbar-expand-xl navbar-dark rb-topbar">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('main-menu') }}">
        <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้ธนาคารวัสดุรีไซเคิล" style="height:42px;width:42px;object-fit:contain;background:#ffffff;border-radius:14px;padding:4px;box-shadow:0 12px 22px rgba(0,0,0,0.12);">
        <span>ธนาคารวัสดุรีไซเคิล</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#rbNavbar" aria-controls="rbNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="rbNavbar">
        <div class="navbar-nav ms-auto align-items-xl-center">
          @foreach($navItems as $item)
            @continue(($item['privileged'] ?? false) && !$isPrivileged)
            @continue(($item['admin_only'] ?? false) && !$isAdmin)
            <a
              @class([
                  'nav-link',
                  'active' => request()->routeIs(...$item['patterns']),
                  'rb-nav-link--deposit' => ($item['accent'] ?? null) === 'deposit',
                  'rb-nav-link--withdraw' => ($item['accent'] ?? null) === 'withdraw',
              ])
              href="{{ route($item['route']) }}"
            >
              {{ $item['label'] }}
            </a>
          @endforeach
        </div>
        @if($authUser)
          <div class="d-flex flex-column flex-xl-row align-items-xl-center gap-2 ms-xl-3 mt-3 mt-xl-0">
            <div class="rb-user-panel d-flex align-items-center justify-content-between gap-3">
              <div class="rb-user-meta">
                <div class="rb-user-name">{{ $authUser->username }}</div>
                <div class="rb-user-role">สิทธิ์ {{ $authUser->role }}</div>
              </div>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">ออกจากระบบ</button>
              </form>
            </div>
          </div>
        @endif
      </div>
    </div>
  </nav>

  <main class="rb-main">
    <div class="container">
      @if (session('success') || $errors->any())
        <div class="rb-flash-stack">
          @if (session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger mb-0">
              <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
              </ul>
            </div>
          @endif
        </div>
      @endif

      {{ $slot ?? '' }}
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const collator = new Intl.Collator('th', { numeric: true, sensitivity: 'base' });

    function normalizeText(value) {
      return String(value ?? '').replace(/\s+/g, ' ').trim();
    }

    function parseNumber(value) {
      const cleaned = normalizeText(value).replace(/,/g, '').replace(/[^\d.-]/g, '');

      if (cleaned === '' || cleaned === '-' || cleaned === '.' || cleaned === '-.') {
        return null;
      }

      const parsed = Number(cleaned);

      return Number.isFinite(parsed) ? parsed : null;
    }

    function parseDate(value) {
      const normalized = normalizeText(value);

      if (normalized === '' || normalized === '-') {
        return null;
      }

      const thaiDateMatch = normalized.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{1,2}):(\d{2}))?/);

      if (thaiDateMatch) {
        const [, day, month, year, hour = '00', minute = '00'] = thaiDateMatch;
        const parsed = new Date(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hour),
          Number(minute)
        ).getTime();

        return Number.isNaN(parsed) ? null : parsed;
      }

      const isoCandidate = normalized.replace(' ', 'T');
      const parsed = new Date(isoCandidate).getTime();

      return Number.isNaN(parsed) ? null : parsed;
    }

    function inferSortType(values) {
      const presentValues = values.filter(function (value) {
        return normalizeText(value) !== '' && normalizeText(value) !== '-';
      });

      if (presentValues.length === 0) {
        return 'text';
      }

      const isDateColumn = presentValues.every(function (value) {
        return parseDate(value) !== null;
      });

      if (isDateColumn) {
        return 'date';
      }

      const isNumberColumn = presentValues.every(function (value) {
        return parseNumber(value) !== null;
      });

      if (isNumberColumn) {
        return 'number';
      }

      return 'text';
    }

    function getCellText(cell) {
      return normalizeText(cell?.dataset.sortValue ?? cell?.textContent ?? '');
    }

    function getTypedValue(cell, type) {
      const text = getCellText(cell);

      if (type === 'number') {
        return parseNumber(text);
      }

      if (type === 'date') {
        return parseDate(text);
      }

      return text;
    }

    function compareValues(left, right, type, direction) {
      if (left === null || left === '') {
        return right === null || right === '' ? 0 : 1;
      }

      if (right === null || right === '') {
        return -1;
      }

      const factor = direction === 'asc' ? 1 : -1;

      if (type === 'number' || type === 'date') {
        return (left - right) * factor;
      }

      return collator.compare(String(left), String(right)) * factor;
    }

    function updateHeaderState(headers, activeIndex, direction) {
      headers.forEach(function (th, index) {
        const indicator = th.querySelector('.rb-sort-indicator');
        const isActive = index === activeIndex;

        th.classList.toggle('rb-sort-active', isActive);
        th.setAttribute('aria-sort', isActive ? (direction === 'asc' ? 'ascending' : 'descending') : 'none');

        if (indicator) {
          indicator.textContent = isActive ? (direction === 'asc' ? '▲' : '▼') : '↕';
        }
      });
    }

    document.querySelectorAll('table[data-sortable-table]').forEach(function (table) {
      const theadRow = table.tHead?.rows?.[0];
      const tbody = table.tBodies?.[0];

      if (!theadRow || !tbody) {
        return;
      }

      const headers = Array.from(theadRow.cells);
      const dataRows = Array.from(tbody.rows).filter(function (row) {
        return !Array.from(row.cells).some(function (cell) {
          return cell.colSpan > 1;
        });
      });

      headers.forEach(function (th, index) {
        if (th.dataset.sortable === 'false') {
          th.setAttribute('aria-sort', 'none');
          return;
        }

        const label = normalizeText(th.textContent).replace(/\s*[▲▼↕]$/, '');
        const sampledValues = dataRows.map(function (row) {
          return getCellText(row.cells[index]);
        });
        const sortType = th.dataset.sortType || inferSortType(sampledValues);
        const button = document.createElement('button');
        const labelSpan = document.createElement('span');
        const indicator = document.createElement('span');

        button.type = 'button';
        button.className = 'rb-sort-button';
        button.setAttribute('aria-label', `เรียงตาม ${label}`);

        labelSpan.className = 'rb-sort-label';
        labelSpan.textContent = label;

        indicator.className = 'rb-sort-indicator';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.textContent = '↕';

        button.append(labelSpan, indicator);
        th.textContent = '';
        th.appendChild(button);
        th.dataset.sortType = sortType;
        th.dataset.sortDirection = 'none';
        th.setAttribute('aria-sort', 'none');

        button.addEventListener('click', function () {
          const sortableRows = Array.from(tbody.rows).filter(function (row) {
            return !Array.from(row.cells).some(function (cell) {
              return cell.colSpan > 1;
            });
          });

          if (sortableRows.length <= 1) {
            return;
          }

          const nextDirection = th.dataset.sortDirection === 'asc' ? 'desc' : 'asc';

          sortableRows
            .map(function (row, originalIndex) {
              return {
                row,
                originalIndex,
                value: getTypedValue(row.cells[index], sortType),
              };
            })
            .sort(function (left, right) {
              const result = compareValues(left.value, right.value, sortType, nextDirection);

              return result !== 0 ? result : left.originalIndex - right.originalIndex;
            })
            .forEach(function (entry) {
              tbody.appendChild(entry.row);
            });

          headers.forEach(function (header) {
            if (header.dataset.sortable !== 'false') {
              header.dataset.sortDirection = 'none';
            }
          });

          th.dataset.sortDirection = nextDirection;
          updateHeaderState(headers, index, nextDirection);
        });
      });
    });
  });
</script>
</body>
</html>

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
      --rb-green: #0f6d4a;
      --rb-green-2: #17a97a;
      --rb-green-100: #e9f7ef;
      --rb-green-200: #d7f0e3;
      --rb-border: #d7e8df;
      --rb-text: #1f2937;
    }

    body {
      font-family: 'Kanit', ui-sans-serif, system-ui, sans-serif;
      background: #f6fbf8;
      color: var(--rb-text);
    }

    .navbar {
      background: #0d3b2b;
      border-bottom: 1px solid #0b2f22;
    }

    .navbar-brand {
      color: #d9f5e6;
      font-weight: 600;
      letter-spacing: 0.2px;
    }

    .navbar-brand:hover {
      color: #ffffff;
    }

    .navbar-nav {
      gap: 0.25rem;
    }

    .navbar .nav-link {
      color: #d9f5e6;
      font-weight: 500;
      border-radius: 0.6rem;
      padding: 0.35rem 0.75rem;
    }

    .navbar .nav-link:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #ffffff;
    }

    .navbar-toggler {
      border-color: rgba(255, 255, 255, 0.3);
    }

    .navbar-toggler:focus {
      box-shadow: 0 0 0 0.2rem rgba(217, 245, 230, 0.25);
    }

    .container {
      max-width: 1100px;
    }

    .form-control,
    .form-select {
      border-radius: 0.6rem;
      border-color: var(--rb-border);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #34d399;
      box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
    }

    .table {
      border-color: var(--rb-border);
    }

    .table thead th {
      background: #f1fbf5;
      color: #166534;
      font-weight: 600;
    }

    .table[data-sortable-table] thead th[data-sortable="false"] {
      cursor: default;
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

    .btn-success {
      background: var(--rb-green-2);
      border-color: var(--rb-green-2);
    }

    .btn-success:hover {
      background: #128e66;
      border-color: #128e66;
    }

    .btn-outline-dark {
      color: var(--rb-green);
      border-color: #9fdac3;
    }

    .btn-outline-dark:hover {
      background: var(--rb-green);
      border-color: var(--rb-green);
      color: #fff;
    }

    .btn-secondary {
      background: #6b7280;
      border-color: #6b7280;
    }

    .alert {
      border-radius: 0.8rem;
      border-color: var(--rb-green-200);
    }

    .bg-white {
      border-radius: 0.9rem;
      border: 1px solid var(--rb-green-200);
      box-shadow: 0 8px 20px rgba(15, 109, 74, 0.06);
    }
  </style>
</head>
<body>
@php
  $authUser = auth()->user();
  $isPrivileged = $authUser && in_array($authUser->role, ['admin', 'staff'], true);
@endphp
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('main-menu') }}">
      <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้ธนาคารวัสดุรีไซเคิล" style="height:36px;width:36px;object-fit:contain;background:#ffffff;border-radius:10px;padding:3px;">
      ธนาคารวัสดุรีไซเคิล
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#rbNavbar" aria-controls="rbNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="rbNavbar">
      <div class="navbar-nav ms-auto align-items-lg-center">
        @if($isPrivileged)
          <a class="nav-link" href="{{ route('deposits.create') }}">ฝาก/รับซื้อ</a>
          <a class="nav-link" href="{{ route('withdraws.create') }}">ถอน</a>
        @endif
        <a class="nav-link" href="{{ route('reports.index') }}">สรุปรายงาน</a>
        <a class="nav-link" href="{{ route('transactions.index') }}">ประวัติรายการ</a>
        <a class="nav-link" href="{{ route('households.index') }}">ครัวเรือน</a>
        @if($isPrivileged)
          <a class="nav-link" href="{{ route('material-categories.index') }}">หมวดวัสดุ</a>
          <a class="nav-link" href="{{ route('materials.index') }}">วัสดุ</a>
          <a class="nav-link" href="{{ route('material-prices.index') }}">ราคา</a>
        @endif
      </div>
      @if($authUser)
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 ms-lg-3 mt-2 mt-lg-0">
          <span class="small text-muted">{{ $authUser->username }} ({{ $authUser->role }})</span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-dark">ออกจากระบบ</button>
          </form>
        </div>
      @endif
    </div>
  </div>
</nav>

<main class="container py-4">
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{ $slot ?? '' }}
</main>

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

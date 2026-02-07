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
      <div class="navbar-nav ms-auto">
        <a class="nav-link" href="{{ route('deposits.create') }}">ฝาก/รับซื้อ</a>
        <a class="nav-link" href="{{ route('withdraws.create') }}">ถอน</a>
        <a class="nav-link" href="{{ route('transactions.index') }}">ประวัติรายการ</a>
        <a class="nav-link" href="{{ route('households.index') }}">ครัวเรือน</a>
        <a class="nav-link" href="{{ route('material-categories.index') }}">หมวดวัสดุ</a>
        <a class="nav-link" href="{{ route('materials.index') }}">วัสดุ</a>
        <a class="nav-link" href="{{ route('material-prices.index') }}">ราคา</a>
      </div>
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
</body>
</html>

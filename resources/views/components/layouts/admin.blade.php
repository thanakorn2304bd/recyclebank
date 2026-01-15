<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'RecycleBank' }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('materials.index') }}">RecycleBank</a>
    <div class="navbar-nav">
      <a class="nav-link" href="{{ route('deposits.create') }}">ฝาก/รับซื้อ</a>
      <a class="nav-link" href="{{ route('withdraws.create') }}">ถอน</a>
      <a class="nav-link" href="{{ route('transactions.index') }}">ประวัติรายการ</a>
      <a class="nav-link" href="{{ route('material-categories.index') }}">หมวดวัสดุ</a>
      <a class="nav-link" href="{{ route('materials.index') }}">วัสดุ</a>
      <a class="nav-link" href="{{ route('material-prices.index') }}">ราคา</a>
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

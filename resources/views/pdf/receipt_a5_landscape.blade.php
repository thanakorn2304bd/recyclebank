@php
  use App\Support\ThaiBaht;

  $orgName = 'กองทุนธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่';
  $title   = 'ใบนำฝาก';

  $fmt = fn($n) => number_format((float)$n, 2);

  $bookNo    = '01';
  $receiptNo = str_pad((string)$tx->transaction_id, 6, '0', STR_PAD_LEFT);
  $dateText  = $todayText ?? \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y');

  $household = $tx->household;
  $houseNo   = $household?->house_no ?? '..........';
  $villageNo = $household?->village_no ?? '..........';
  $community = $household?->community_id ?? '..........';
  $accountNo = $household?->account_no ?? '................................';
@endphp

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <style>
    @page { size: A5 landscape; margin: 2.5mm; }
    * { box-sizing: border-box; }

    @font-face {
      font-family: "THSarabunNew";
      src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format("truetype");
      font-weight: normal;
      font-style: normal;
    }
    @font-face {
      font-family: "THSarabunNew";
      src: url("{{ storage_path('fonts/THSarabunNew Bold.ttf') }}") format("truetype");
      font-weight: bold;
      font-style: normal;
    }

    body {
      font-family: "THSarabunNew", DejaVu Sans, sans-serif;
      font-size: 25.3px;
      line-height: 0.85;
      color: #000;
      margin: 0;
    }

    .page {
      width: 100%;
      height: 100%;
    }

    .top-line {
      text-align: right;
      font-size: 22px;
      line-height: 0.85;
      margin-bottom: 0;
    }

    .org {
      text-align: center;
      font-weight: bold;
      font-size: 31.9px;
      line-height: 0.85;
      margin-bottom: 0;
    }

    .box {
      border: 2px solid #000;
      padding: 0.2mm;
    }

    .box-title {
      font-weight: bold;
      font-size: 26.4px;
      line-height: 0.85;
      margin-bottom: 0;
    }

    .row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 1.5mm;
      font-size: 24.2px;
      line-height: 0.75;
      margin-bottom: 0;
    }

    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin: 0;
      font-size: 24.2px;
      line-height: 0.75;
      table-layout: fixed;
    }

    .meta-table td {
      padding: 0;
      vertical-align: baseline;
    }

    .meta-date {
      text-align: left;
      white-space: nowrap;
    }

    .row .grow {
      flex: 1;
    }

    .date-right {
      text-align: right;
      position: relative;
      top: -0.2mm;
    }

    .meta-table .date-right {
      margin-left: 0;
      min-width: 0;
      position: relative;
      top: -0.2mm;
    }

    .date-inner {
      width: 100%;
      border-collapse: collapse;
    }

    .date-spacer {
      width: 70%;
    }

    .date-text {
      width: 30%;
      text-align: left;
      white-space: nowrap;
    }

    .items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 0.2mm;
      font-size: 24.2px;
      line-height: 0.7;
      table-layout: fixed;
    }

    .items th,
    .items td {
      border: 1px solid #000;
      padding: 0.2mm 0.35mm;
      vertical-align: middle;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    .items th {
      text-align: center;
      font-weight: bold;
    }

    .items td.num {
      text-align: right;
    }

    .items td.center {
      text-align: center;
    }

    .items tbody tr {
      height: 3.9mm;
    }

    .total-row td {
      font-weight: bold;
    }

    .amount-text {
      border: 1px solid #000;
      border-top: none;
      padding: 0.2mm 0.35mm;
      font-size: 24.2px;
      line-height: 0.7;
    }

    .sign-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 0.2mm;
      font-size: 24.2px;
      line-height: 0.7;
      table-layout: fixed;
    }

    .sign-table td {
      width: 50%;
      vertical-align: top;
      padding: 0 2mm;
    }

    .signature .line {
      border-bottom: 0;
      height: 3mm;
      margin-bottom: 0.3mm;
    }

    .footer-box {
      border: 1px solid #000;
      border-top: none;
      padding: 0.5mm 0.5mm;
    }
  </style>
</head>
<body>
@foreach($pages as $pageIndex => $items)
  @php
    $pageSum = (float) ($items->count() ? $items->sum('amount') : $tx->total_amount);
    $amountText = ThaiBaht::text($pageSum);
  @endphp
  <div class="page">
    <div class="top-line">เล่มที่ {{ $bookNo }} เลขที่ {{ $receiptNo }}/......</div>
    <div class="org">{{ $orgName }}</div>

    <div class="box">
      <div class="box-title">{{ $title }}</div>

      <div class="row">
        <div class="grow">บ้านเลขที่ {{ $houseNo }} หมู่ {{ $villageNo }} ชุมชนที่ {{ $community }}</div>
      </div>
      <table class="meta-table">
        <colgroup>
          <col style="width:55%;">
          <col style="width:15%;">
          <col style="width:15%;">
          <col style="width:15%;">
        </colgroup>
        <tr>
          <td colspan="2">เลขที่บัญชี {{ $accountNo }}</td>
          <td class="meta-date date-right">
            <table class="date-inner">
              <tr>
                <td class="date-spacer"></td>
                <td class="date-text">วันที่ {{ $dateText }}</td>
              </tr>
            </table>
          </td>
          <td></td>
        </tr>
      </table>

      <table class="items">
        <thead>
          <tr>
            <th style="width:55%;">รายการ</th>
            <th style="width:15%;">จำนวน</th>
            <th style="width:15%;">ราคา/หน่วย<br>(บาท)</th>
            <th style="width:15%;">รวมเงิน <br>(บาท)</th>
          </tr>
        </thead>
        <tbody>
          @if($items->count() === 0)
            <tr>
              <td>ถอนเงิน</td>
              <td class="center">-</td>
              <td class="num">-</td>
              <td class="num">{{ $fmt($tx->total_amount) }}</td>
            </tr>
            @for($i = 1; $i < $rowsPerPage; $i++)
              <tr>
                <td>&nbsp;</td>
                <td class="center"></td>
                <td class="num"></td>
                <td class="num"></td>
              </tr>
            @endfor
          @else
            @foreach($items as $d)
              <tr>
                <td>{{ $d->material?->material_name }}</td>
                <td class="center">{{ $fmt($d->weight) }}</td>
                <td class="num">{{ $fmt($d->price_per_unit) }}</td>
                <td class="num">{{ $fmt($d->amount) }}</td>
              </tr>
            @endforeach
            @for($i = $items->count(); $i < $rowsPerPage; $i++)
              <tr>
                <td>&nbsp;</td>
                <td class="center"></td>
                <td class="num"></td>
                <td class="num"></td>
              </tr>
            @endfor
          @endif
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td colspan="3" class="num">รวมเป็นเงิน</td>
            <td class="num">{{ $fmt($pageSum) }}</td>
          </tr>
        </tfoot>
      </table>

      <div class="amount-text">ยอดเงินเป็นตัวอักษร {{ $amountText }}</div>

      <div class="footer-box">
        <table class="sign-table">
          <tr>
            <td class="signature">
              ลงชื่อผู้ฝาก.............................................
              <div class="line"></div>
              (.....................................................)
            </td>
            <td class="signature">
              ลงชื่อเจ้าหน้าที่........................................
              <div class="line"></div>
              (.....................................................)
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  @if($pageIndex < $pages->count() - 1)
    <div style="page-break-after: always;"></div>
  @endif
@endforeach
</body>
</html>

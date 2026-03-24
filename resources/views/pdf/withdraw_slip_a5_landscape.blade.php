<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <style>
    @page { size: A5 landscape; margin: 5mm 6mm; }
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
      margin: 0;
      font-family: "THSarabunNew", DejaVu Sans, sans-serif;
      font-size: 19px;
      line-height: 0.95;
      color: #2d428f;
    }

    .page {
      width: 100%;
      page-break-inside: avoid;
    }

    .serial-wrap {
      text-align: right;
      margin-bottom: 6px;
    }

    .serial-table {
      display: inline-table;
      border-collapse: separate;
      border-spacing: 4px 0;
      font-size: 16px;
      line-height: 1;
    }

    .serial-table td {
      padding: 0;
      border: none;
      white-space: nowrap;
      vertical-align: baseline;
    }

    .serial-dots-cell {
      letter-spacing: 0.4px;
    }

    .org-name {
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 8px;
    }

    .slip-table,
    .amount-table,
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .slip-table {
      border: 2px solid #2d428f;
      page-break-inside: avoid;
    }

    .slip-table td {
      border: 2px solid #2d428f;
      padding: 6px 8px;
      vertical-align: top;
      page-break-inside: avoid;
    }

    .title-cell {
      font-size: 23px;
      font-weight: bold;
      padding: 6px 8px;
    }

    .field {
      white-space: nowrap;
    }

    .field-fill {
      display: inline-block;
      min-width: 58%;
      padding: 0 6px 1px;
      border-bottom: 1px dotted #2d428f;
      color: #243779;
    }

    .field-fill.wide {
      min-width: 80%;
    }

    .field-fill.date {
      min-width: 58%;
    }

    .amount-table td {
      border: 2px solid #2d428f;
      padding: 4px 8px;
      vertical-align: middle;
    }

    .amount-label {
      width: 42%;
      text-align: right;
      font-weight: bold;
    }

    .amount-value {
      color: #243779;
    }

    .signature-wrap {
      padding: 6px 8px;
    }

    .signature-table td {
      width: 50%;
      padding: 6px;
      vertical-align: top;
    }

    .signature-line {
      display: inline-block;
      width: 52%;
      border-bottom: 1px dotted #2d428f;
      height: 11px;
      vertical-align: baseline;
      position: relative;
      top: 1px;
    }

    .signature-block {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .signature-block td {
      border: none;
      padding: 0;
      vertical-align: bottom;
    }

    .signature-label-row {
      white-space: nowrap;
      line-height: 1.1;
    }

    .signature-name-row {
      padding-top: 2px;
    }

    .signature-name-table {
      width: 82%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .signature-name-table td {
      border: none;
      padding: 0;
      vertical-align: bottom;
    }

    .signature-paren {
      width: 14px;
      text-align: center;
      line-height: 1.15;
      padding-bottom: 1px;
    }

    .signature-name-fill {
      border-bottom: 1px dotted #2d428f;
      height: 23px;
      padding: 0 6px 1px;
      text-align: center;
      color: #243779;
      font-size: 16px;
      line-height: 1.15;
      white-space: nowrap;
      overflow: visible;
    }

    .footer-note {
      font-size: 17px;
      font-weight: bold;
      padding: 6px 8px 8px;
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="serial-wrap">
      <table class="serial-table">
        <tr>
          <td>เล่มที่</td>
          <td class="serial-dots-cell">....................</td>
          <td style="padding-left: 10px;">เลขที่</td>
          <td class="serial-dots-cell">....................</td>
          <td style="padding: 0 2px;">/</td>
          <td class="serial-dots-cell">....................</td>
        </tr>
      </table>
    </div>
    <div class="org-name">{{ $orgName }}</div>

    <table class="slip-table">
      <tr>
        <td colspan="2" class="title-cell">{{ $title }}</td>
      </tr>
      <tr>
        <td style="width: 58%;">
          <div class="field">เลขที่บัญชี<span class="field-fill">{{ $accountNo }}</span></div>
        </td>
        <td style="width: 42%;">
          <div class="field">วันที่<span class="field-fill date">{{ $dateText }}</span></div>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="field">ชื่อบัญชี<span class="field-fill wide">{{ $accountName }}</span></div>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding: 0;">
          <table class="amount-table">
            <tr>
              <td class="amount-label">จำนวนเงินที่ถอนเป็นตัวเลข</td>
              <td class="amount-value">{{ number_format($amount, 2) }}</td>
            </tr>
            <tr>
              <td class="amount-label">ตัวอักษร</td>
              <td class="amount-value">{{ $amountText }}</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="signature-wrap">
          <table class="signature-table">
            <tr>
              <td>
                <table class="signature-block">
                  <tr>
                    <td class="signature-label-row">ลงชื่อผู้ถอน/ผู้มอบอำนาจ <span class="signature-line"></span></td>
                  </tr>
                  <tr>
                    <td class="signature-name-row">
                      <table class="signature-name-table">
                        <tr>
                          <td class="signature-paren">(</td>
                          <td class="signature-name-fill"></td>
                          <td class="signature-paren">)</td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
              <td>
                <table class="signature-block">
                  <tr>
                    <td class="signature-label-row">ลงชื่อเจ้าของบัญชี <span class="signature-line"></span></td>
                  </tr>
                  <tr>
                    <td class="signature-name-row">
                      <table class="signature-name-table">
                        <tr>
                          <td class="signature-paren">(</td>
                          <td class="signature-name-fill">{{ $accountName }}</td>
                          <td class="signature-paren">)</td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table class="signature-block">
                  <tr>
                    <td class="signature-label-row">ลงชื่อผู้รับเงิน <span class="signature-line"></span></td>
                  </tr>
                  <tr>
                    <td class="signature-name-row">
                      <table class="signature-name-table">
                        <tr>
                          <td class="signature-paren">(</td>
                          <td class="signature-name-fill"></td>
                          <td class="signature-paren">)</td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
              <td>
                <table class="signature-block">
                  <tr>
                    <td class="signature-label-row">ลงชื่อเจ้าหน้าที่ผู้รับถอนเงิน <span class="signature-line"></span></td>
                  </tr>
                  <tr>
                    <td class="signature-name-row">
                      <table class="signature-name-table">
                        <tr>
                          <td class="signature-paren">(</td>
                          <td class="signature-name-fill">{{ $officerName }}</td>
                          <td class="signature-paren">)</td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="footer-note">***โปรดนำบัตรประจำตัวประชาชนไปด้วยทุกครั้งที่มีการถอนเงิน</td>
      </tr>
    </table>
  </div>
</body>
</html>

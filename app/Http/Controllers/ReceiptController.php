<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function receipt(Transaction $transaction)
    {
        $transaction->load([
            'household',
            'details.material',
        ]);

        // จำนวนแถวต่อ 1 หน้า (ตามแบบฟอร์ม)
        $rowsPerPage = 7;

        // ถ้าเกิน 7 แถว -> ไปหน้า 2,3,... (และจะยกยอดใน view)
        $pages = $transaction->details->chunk($rowsPerPage)->values();

        $pdf = Pdf::loadView('pdf.receipt_a5_landscape', [
            'tx' => $transaction,
            'pages' => $pages,
            'rowsPerPage' => $rowsPerPage,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream('receipt_'.$transaction->transaction_id.'.pdf');
    }
}
    
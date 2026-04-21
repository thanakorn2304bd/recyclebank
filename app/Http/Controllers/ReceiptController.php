<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\Transactions\TransactionPdfViewDataFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function receipt(Request $request, Transaction $transaction, TransactionPdfViewDataFactory $transactionPdfViewDataFactory)
    {
        $this->authorize('view', $transaction);

        abort_if($transaction->is_reversal, 404);

        $transaction->load([
            'household',
            'details.material',
            'recordedByUser.staff',
        ]);

        $printedBy = $request->user();

        if ($transaction->transaction_type === 'withdraw') {
            $pdf = Pdf::loadView(
                'pdf.withdraw_slip_a5_landscape',
                $transactionPdfViewDataFactory->withdrawSlip($transaction, $printedBy)
            )->setPaper('a5', 'landscape');

            return $pdf->stream('withdraw-slip_'.$transaction->transaction_id.'.pdf');
        }

        $pdf = Pdf::loadView(
            'pdf.receipt_a5_landscape',
            $transactionPdfViewDataFactory->receipt($transaction, 7, $printedBy)
        )->setPaper('a5', 'landscape');

        return $pdf->stream('receipt_'.$transaction->transaction_id.'.pdf');
    }
}

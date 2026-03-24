<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\Transactions\TransactionPdfViewDataFactory;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function receipt(Transaction $transaction, TransactionPdfViewDataFactory $transactionPdfViewDataFactory)
    {
        $this->authorize('view', $transaction);

        $transaction->load([
            'household',
            'details.material',
            'recordedByUser.staff',
        ]);

        if ($transaction->transaction_type === 'withdraw') {
            $pdf = Pdf::loadView(
                'pdf.withdraw_slip_a5_landscape',
                $transactionPdfViewDataFactory->withdrawSlip($transaction)
            )->setPaper('a5', 'landscape');

            return $pdf->stream('withdraw-slip_'.$transaction->transaction_id.'.pdf');
        }

        $pdf = Pdf::loadView(
            'pdf.receipt_a5_landscape',
            $transactionPdfViewDataFactory->receipt($transaction)
        )->setPaper('a5', 'landscape');

        return $pdf->stream('receipt_'.$transaction->transaction_id.'.pdf');
    }
}

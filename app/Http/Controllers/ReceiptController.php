<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class ReceiptController extends Controller
{
    public function receipt(Transaction $transaction)
    {
        if (
            Auth::check()
            && Auth::user()->role === 'member'
            && (int) $transaction->household_id !== (int) (Auth::user()->household_id ?? 0)
        ) {
            abort(403, 'ผู้ใช้ทั่วไปสามารถดูได้เฉพาะข้อมูลของตนเอง');
        }

        $transaction->load([
            'household',
            'details.material',
            'recordedByUser.staff',
        ]);

        if ($transaction->transaction_type === 'withdraw') {
            $pdf = Pdf::loadView('pdf.withdraw_slip_a5_landscape', [
                'tx' => $transaction,
            ])->setPaper('a5', 'landscape');

            return $pdf->stream('withdraw-slip_'.$transaction->transaction_id.'.pdf');
        }

        // จำนวนแถวในตารางต่อ 1 หน้า (หน้าถัดไปต้องเผื่อ 1 แถวสำหรับยอดยกมา)
        $rowsPerPage = 7;
        $pages = $this->buildReceiptPages($transaction, $rowsPerPage);

        $pdf = Pdf::loadView('pdf.receipt_a5_landscape', [
            'tx' => $transaction,
            'pages' => $pages,
            'rowsPerPage' => $rowsPerPage,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream('receipt_'.$transaction->transaction_id.'.pdf');
    }

    private function buildReceiptPages(Transaction $transaction, int $rowsPerPage): Collection
    {
        $details = $transaction->details->values();

        if ($details->isEmpty()) {
            return collect([[
                'items' => collect(),
                'carry_in' => 0.0,
                'footer_total' => round((float) $transaction->total_amount, 2),
                'footer_label' => 'รวมเป็นเงิน',
                'show_carry_in' => false,
                'blank_rows' => $rowsPerPage,
            ]]);
        }

        $pages = collect();
        $remaining = $details;
        $carryIn = 0.0;
        $isFirstPage = true;

        while ($remaining->isNotEmpty()) {
            $pageCapacity = $isFirstPage ? $rowsPerPage : max($rowsPerPage - 1, 1);
            $items = $remaining->take($pageCapacity)->values();
            $remaining = $remaining->slice($pageCapacity)->values();

            $pageItemsTotal = round((float) $items->sum('amount'), 2);
            $runningTotal = round($carryIn + $pageItemsTotal, 2);
            $isLastPage = $remaining->isEmpty();
            $footerTotal = $isLastPage
                ? round((float) $transaction->total_amount, 2)
                : $runningTotal;
            $showCarryIn = ! $isFirstPage;

            $pages->push([
                'items' => $items,
                'carry_in' => $carryIn,
                'footer_total' => $footerTotal,
                'footer_label' => $isLastPage ? 'รวมเป็นเงิน' : 'ยอดยกไป',
                'show_carry_in' => $showCarryIn,
                'blank_rows' => max($rowsPerPage - $items->count() - ($showCarryIn ? 1 : 0), 0),
            ]);

            $carryIn = $footerTotal;
            $isFirstPage = false;
        }

        return $pages;
    }
}
    

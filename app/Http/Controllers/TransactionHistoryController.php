<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    // 1) รายการทั้งหมด + filter
    public function index(Request $request)
    {
        $type = $request->input('type'); // deposit|withdraw|null
        $from = $request->input('from');
        $to   = $request->input('to');
        $householdId = $request->input('household_id');

        $households = Household::orderBy('account_no')->get(['household_id','account_no','contact_person']);

        $txs = Transaction::query()
            ->with('household')
            ->when($type, fn($q) => $q->where('transaction_type', $type))
            ->when($from, fn($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('transaction_date', '<=', $to))
            ->when($householdId, fn($q) => $q->where('household_id', $householdId))
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->paginate(20)
            ->withQueryString();

        return view('transactions.index', compact('txs','households','type','from','to','householdId'));
    }

    // 2) รายการตามครัวเรือน (statement)
    public function household(Household $household, Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $txs = Transaction::query()
            ->where('household_id', $household->household_id)
            ->when($from, fn($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->paginate(25)
            ->withQueryString();

        return view('transactions.household', compact('household','txs','from','to'));
    }

    // 3) รายละเอียด (ใบเสร็จ)
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'household',
            'details.material',
        ]);

        return view('transactions.show', compact('transaction'));
    }
}

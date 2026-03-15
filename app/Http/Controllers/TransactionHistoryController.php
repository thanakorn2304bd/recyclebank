<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    // 1) รายการทั้งหมด + filter
    public function index(Request $request)
    {
        $isMember = $this->isMember();
        $memberHouseholdId = $this->memberHouseholdId();

        $type = $request->input('type'); // deposit|withdraw|null
        $from = $request->input('from');
        $to   = $request->input('to');
        $householdId = $isMember ? $memberHouseholdId : $request->input('household_id');

        $households = Household::query()
            ->when($isMember, function ($query) use ($memberHouseholdId) {
                if ($memberHouseholdId) {
                    $query->where('household_id', $memberHouseholdId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('account_no')
            ->get(['household_id','account_no','contact_person']);

        $txs = Transaction::query()
            ->with('household')
            ->when($isMember, function ($query) use ($memberHouseholdId) {
                if ($memberHouseholdId) {
                    $query->where('household_id', $memberHouseholdId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
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
        $this->ensureCanViewHousehold($household);

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
        $this->ensureCanViewTransaction($transaction);

        $transaction->load([
            'household',
            'details.material',
        ]);

        return view('transactions.show', compact('transaction'));
    }

    private function isMember(): bool
    {
        return Auth::check() && Auth::user()->role === 'member';
    }

    private function memberHouseholdId(): ?int
    {
        $householdId = Auth::user()?->household_id;

        return $householdId ? (int) $householdId : null;
    }

    private function ensureCanViewHousehold(Household $household): void
    {
        if (! $this->isMember()) {
            return;
        }

        if ((int) $household->household_id !== (int) $this->memberHouseholdId()) {
            abort(403, 'ผู้ใช้ทั่วไปสามารถดูได้เฉพาะข้อมูลของตนเอง');
        }
    }

    private function ensureCanViewTransaction(Transaction $transaction): void
    {
        if (! $this->isMember()) {
            return;
        }

        if ((int) $transaction->household_id !== (int) $this->memberHouseholdId()) {
            abort(403, 'ผู้ใช้ทั่วไปสามารถดูได้เฉพาะข้อมูลของตนเอง');
        }
    }
}

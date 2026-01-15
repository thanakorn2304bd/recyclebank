<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    public function create()
    {
        $households = Household::orderBy('account_no')->get(['household_id','account_no','contact_person','total_balance']);
        return view('withdraws.create', compact('households'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'household_id' => ['required','integer','exists:household,household_id'],
            'transaction_date' => ['required','date'],
            'amount' => ['required','numeric','min:0.01'],
        ]);

        $householdId = (int)$data['household_id'];
        $date = $data['transaction_date'];
        $amount = round((float)$data['amount'], 2);

        $recordedBy = session('user_id') ?? DB::table('user_account')->min('user_id') ?? 1;

        return DB::transaction(function () use ($householdId, $date, $amount, $recordedBy) {

            $balance = (float) DB::table('household')->where('household_id', $householdId)->lockForUpdate()->value('total_balance');

            // กันถอนเกิน (ถ้าอยากให้ติดลบได้ ให้เอา if นี้ออก)
            if ($amount > $balance) {
                return back()->withErrors("ยอดเงินไม่พอ (คงเหลือ " . number_format($balance, 2) . ")");
            }

            Transaction::create([
                'household_id' => $householdId,
                'transaction_date' => $date,
                'transaction_type' => 'withdraw',
                'total_weight' => 0.00, // สำคัญ: ใน DB คุณห้าม null
                'total_amount' => $amount,
                'recorded_by' => $recordedBy,
            ]);

            DB::table('household')
                ->where('household_id', $householdId)
                ->update(['total_balance' => DB::raw('total_balance - ' . $amount)]);

            return redirect()
                ->route('withdraws.create')
                ->with('success', "บันทึกถอนสำเร็จ (จำนวน " . number_format($amount, 2) . ")");
        });
    }
}

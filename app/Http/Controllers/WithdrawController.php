<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawController extends Controller
{
    public function create()
    {
        return view('withdraws.create');
    }

    public function preview(Request $request)
    {
        ['household' => $household, 'date' => $date, 'amount' => $amount] = $this->prepareWithdrawData($request);

        $previewTransaction = new Transaction([
            'household_id' => $household->household_id,
            'transaction_date' => $date,
            'transaction_type' => 'withdraw',
            'total_weight' => 0.00,
            'total_amount' => $amount,
            'recorded_by' => Auth::id() ?? DB::table('user_account')->min('user_id') ?? 1,
        ]);

        $previewTransaction->setRelation('household', $household);

        if ($user = Auth::user()) {
            $user->loadMissing('staff');
            $previewTransaction->setRelation('recordedByUser', $user);
        }

        $pdf = Pdf::loadView('pdf.withdraw_slip_a5_landscape', [
            'tx' => $previewTransaction,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream('withdraw-slip-preview.pdf');
    }

    public function store(Request $request)
    {
        ['household' => $household, 'date' => $date, 'amount' => $amount] = $this->prepareWithdrawData($request);

        $householdId = (int) $household->household_id;

        $recordedBy = Auth::id() ?? DB::table('user_account')->min('user_id') ?? 1;

        return DB::transaction(function () use ($householdId, $date, $amount, $recordedBy) {

            $balance = (float) DB::table('household')->where('household_id', $householdId)->lockForUpdate()->value('total_balance');

            // กันถอนเกิน (ถ้าอยากให้ติดลบได้ ให้เอา if นี้ออก)
            if ($amount > $balance) {
                return back()
                    ->withErrors("ยอดเงินไม่พอ (คงเหลือ " . number_format($balance, 2) . ")")
                    ->withInput();
            }

            $transaction = Transaction::create([
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

            return redirect()->route('transactions.receipt', $transaction);
        });
    }

    private function prepareWithdrawData(Request $request): array
    {
        $data = $request->validate([
            'community_id' => ['required', 'string', 'max:2'],
            'house_no' => ['required', 'string', 'max:20'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $communityId = trim($data['community_id']);
        if (ctype_digit($communityId)) {
            $communityId = str_pad($communityId, 2, '0', STR_PAD_LEFT);
        }

        $houseNo = trim($data['house_no']);
        $date = $data['transaction_date'];
        $amount = round((float) $data['amount'], 2);

        $household = Household::query()
            ->where('community_id', $communityId)
            ->where('house_no', $houseNo)
            ->first([
                'household_id',
                'account_no',
                'contact_person',
                'community_id',
                'house_no',
                'active_status',
                'total_balance',
            ]);

        if (! $household) {
            throw ValidationException::withMessages([
                'house_no' => "ไม่พบครัวเรือนสำหรับเลขที่ชุมชน {$communityId} และบ้านเลขที่ {$houseNo}",
            ]);
        }

        $balance = (float) $household->total_balance;
        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount' => "ยอดเงินไม่พอ (คงเหลือ " . number_format($balance, 2) . ")",
            ]);
        }

        return [
            'household' => $household,
            'date' => $date,
            'amount' => $amount,
        ];
    }
}

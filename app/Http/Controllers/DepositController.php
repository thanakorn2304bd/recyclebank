<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositStoreRequest;
use App\Http\Requests\LookupHouseholdRequest;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Transactions\DepositService;
use App\Support\Transactions\HouseholdTransactionService;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function create(DepositService $depositService)
    {
        $materials = $depositService->materialsForCreateForm();
        $currentPrices = $depositService->currentPricesForToday();

        return view('deposits.create', compact('materials', 'currentPrices'));
    }

    public function store(
        DepositStoreRequest $request,
        CurrentUserIdResolver $currentUserIdResolver,
        HouseholdTransactionService $householdTransactionService,
        DepositService $depositService
    ) {
        $data = $request->validated();
        $communityId = $data['community_id'];
        $houseNo = $data['house_no'];

        $household = $householdTransactionService->findForLookup($communityId, $houseNo);
        if (! $household) {
            return back()
                ->withErrors("ไม่พบครัวเรือนสำหรับเลขที่ชุมชน {$communityId} และบ้านเลขที่ {$houseNo}")
                ->withInput();
        }

        $householdTransactionService->ensureActive($household);
        $householdId = (int) $household->household_id;
        $date = $data['transaction_date'];
        $recordedBy = $currentUserIdResolver->resolve($request);

        [
            'transaction' => $transaction,
            'total_weight' => $totalWeight,
            'total_amount' => $totalAmount,
        ] = $depositService->record($householdId, $date, $data['items'], $recordedBy);

        ActivityLogger::forCurrentUser(
            'transactions',
            "บันทึกฝาก/รับซื้อให้ {$household->account_no} ({$household->contact_person}) น้ำหนักรวม "
            .number_format($totalWeight, 2).' กก. เป็นเงิน '.number_format($totalAmount, 2).' บาท',
            [
                'entity_type' => 'transaction',
                'entity_id' => (string) $transaction->transaction_id,
                'after' => [
                    'transaction_id' => (int) $transaction->transaction_id,
                    'transaction_type' => 'deposit',
                    'household_id' => $householdId,
                    'account_no' => (string) $household->account_no,
                    'transaction_date' => $date,
                    'total_weight' => $totalWeight,
                    'total_amount' => $totalAmount,
                    'household_balance' => (float) DB::table('household')
                        ->where('household_id', $householdId)
                        ->value('total_balance'),
                ],
            ]
        );

        return redirect()
            ->route('transactions.show', [
                'transaction' => $transaction,
                'source' => 'deposit',
            ])
            ->with('success', 'บันทึกฝาก/รับซื้อสำเร็จ (ยอดรวม '.number_format($totalAmount, 2).')');
    }

    public function lookupHousehold(
        LookupHouseholdRequest $request,
        HouseholdTransactionService $householdTransactionService
    ) {
        $communityId = $request->validated('community_id');
        $houseNo = $request->validated('house_no');

        $household = $householdTransactionService->findForLookup($communityId, $houseNo);

        if (! $household) {
            return response()->json([
                'found' => false,
                'message' => "ไม่พบครัวเรือนสำหรับเลขที่ชุมชน {$communityId} และบ้านเลขที่ {$houseNo}",
            ]);
        }

        return response()->json($householdTransactionService->lookupPayload($household));
    }
}

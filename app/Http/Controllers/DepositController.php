<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    public function create()
    {
        $materials  = Material::with('category')
            ->where('is_active', 1)
            ->orderBy('material_name')
            ->get(['material_id','material_name','unit','category_id']);

        // ส่ง “ราคาปัจจุบัน” ให้หน้าเว็บใช้แสดง/คำนวณ
        // ดึงแบบ 1 query ต่อ material ไม่ดี -> ใช้ subquery เลือกราคาที่ effective_date ล่าสุด
        $today = now()->toDateString();

        $currentPrices = MaterialPrice::query()
            ->select('material_id', 'price')
            ->where(function($q) use ($today){
                $q->whereNull('expired_date')->orWhere('expired_date', '>=', $today);
            })
            ->where('effective_date', '<=', $today)
            ->orderByDesc('effective_date')
            ->get()
            ->groupBy('material_id')
            ->map(fn($rows) => (float) $rows->first()->price)
            ->toArray();

        return view('deposits.create', compact('materials', 'currentPrices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'community_id' => ['required','string','max:2'],
            'house_no' => ['required','string','max:20'],
            'transaction_date' => ['required','date'],

            // รายการวัสดุ
            'items' => ['required','array','min:1'],
            'items.*.material_id' => ['required','integer','exists:material,material_id'],
            'items.*.weight' => ['required','numeric','min:0.01'],
        ]);

        $communityId = trim($data['community_id']);
        if (ctype_digit($communityId)) {
            $communityId = str_pad($communityId, 2, '0', STR_PAD_LEFT);
        }
        $houseNo = trim($data['house_no']);

        $household = Household::where('community_id', $communityId)
            ->where('house_no', $houseNo)
            ->first(['household_id','account_no','contact_person']);

        if (!$household) {
            return back()
                ->withErrors("ไม่พบครัวเรือนสำหรับเลขที่ชุมชน {$communityId} และบ้านเลขที่ {$houseNo}")
                ->withInput();
        }

        $householdId = (int)$household->household_id;
        $date = $data['transaction_date'];

        $recordedBy = Auth::id() ?? DB::table('user_account')->min('user_id') ?? 1;

        $today = now()->toDateString();

        return DB::transaction(function () use ($householdId, $date, $recordedBy, $data, $today) {

            $totalWeight = 0.0;
            $totalAmount = 0.0;

            // สร้าง transaction ก่อน (ยังไม่รู้ total จนกว่าจะคำนวณ)
            $tx = Transaction::create([
                'household_id' => $householdId,
                'transaction_date' => $date,
                'transaction_type' => 'deposit',
                'total_weight' => 0.00,
                'total_amount' => 0.00,
                'recorded_by' => $recordedBy,
            ]);

            foreach ($data['items'] as $item) {
                $materialId = (int)$item['material_id'];
                $weight = (float)$item['weight'];

                // หา “ราคาปัจจุบัน” ของ material นี้
                $priceRow = MaterialPrice::query()
                    ->where('material_id', $materialId)
                    ->where('effective_date', '<=', $today)
                    ->where(function($q) use ($today){
                        $q->whereNull('expired_date')->orWhere('expired_date', '>=', $today);
                    })
                    ->orderByDesc('effective_date')
                    ->first();

                $ppu = (float)($priceRow?->price ?? 0);

                // ถ้าไม่มีราคา -> กันพัง (ไม่ให้ฝากได้)
                if ($ppu <= 0) {
                    throw new \RuntimeException("ไม่พบราคาปัจจุบันสำหรับ material_id={$materialId}");
                }

                $amount = round($weight * $ppu, 2);

                TransactionDetail::create([
                    'transaction_id' => $tx->transaction_id,
                    'material_id' => $materialId,
                    'weight' => $weight,
                    'price_per_unit' => $ppu,
                    'amount' => $amount,
                ]);

                $totalWeight += $weight;
                $totalAmount += $amount;
            }

            // อัปเดตยอดรวมใน transaction
            $tx->update([
                'total_weight' => round($totalWeight, 2),
                'total_amount' => round($totalAmount, 2),
            ]);

            // อัปเดตยอดเงินคงเหลือ household
            DB::table('household')
                ->where('household_id', $householdId)
                ->update(['total_balance' => DB::raw('total_balance + ' . round($totalAmount, 2))]);

            return redirect()
                ->route('deposits.create')
                ->with('success', "บันทึกฝาก/รับซื้อสำเร็จ (ยอดรวม " . number_format($totalAmount, 2) . ")");
        });
    }

    public function lookupHousehold(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'community_id' => ['required','string','max:2'],
            'house_no' => ['required','string','max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'found' => false,
                'message' => 'กรุณากรอกเลขที่ชุมชนและบ้านเลขที่ให้ครบถ้วน',
            ], 422);
        }

        $communityId = trim($request->input('community_id'));
        if (ctype_digit($communityId)) {
            $communityId = str_pad($communityId, 2, '0', STR_PAD_LEFT);
        }
        $houseNo = trim($request->input('house_no'));

        $household = Household::with('community')
            ->where('community_id', $communityId)
            ->where('house_no', $houseNo)
            ->first();

        if (!$household) {
            return response()->json([
                'found' => false,
                'message' => "ไม่พบครัวเรือนสำหรับเลขที่ชุมชน {$communityId} และบ้านเลขที่ {$houseNo}",
            ]);
        }

        return response()->json([
            'found' => true,
            'household' => [
                'household_id' => $household->household_id,
                'account_no' => $household->account_no,
                'contact_person' => $household->contact_person,
                'total_balance' => (float) $household->total_balance,
                'community_id' => $household->community_id,
                'community_name' => $household->community?->community_name,
                'house_no' => $household->house_no,
                'active_status' => $household->active_status,
            ],
        ]);
    }
}

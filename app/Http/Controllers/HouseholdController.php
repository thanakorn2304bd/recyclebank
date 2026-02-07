<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $communityId = $request->string('community_id')->toString();
        $status = $request->string('status')->toString();

        $households = Household::query()
            ->with('community')
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($sub) use ($q) {
                    $sub->where('account_no', 'like', "%{$q}%")
                        ->orWhere('house_no', 'like', "%{$q}%")
                        ->orWhere('contact_person', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when($communityId, fn($qb) => $qb->where('community_id', $communityId))
            ->when($status, fn($qb) => $qb->where('active_status', $status))
            ->orderBy('account_no')
            ->paginate(15)
            ->withQueryString();

        $communities = Community::orderBy('community_id')->get();

        return view('households.index', compact('households', 'communities', 'q', 'communityId', 'status'));
    }

    public function create()
    {
        $communities = Community::orderBy('community_id')->get();
        return view('households.create', compact('communities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_no' => ['required','string','max:10','unique:household,account_no'],
            'house_no' => ['required','string','max:20'],
            'village_no' => ['nullable','string','max:10'],
            'community_id' => ['required','string','exists:community,community_id'],
            'phone' => ['nullable','string','max:20'],
            'contact_person' => ['required','string','max:100'],
            'register_date' => ['required','date'],
            'active_status' => ['required','in:pending,active,inactive'],
            'accumulated_months' => ['required','integer','min:0'],
        ]);

        $data['total_balance'] = 0.00;

        $createdBy = session('user_id') ?? DB::table('user_account')->min('user_id');
        if ($createdBy) {
            $data['created_by'] = $createdBy;
        }

        Household::create($data);

        return redirect()->route('households.index')
            ->with('success', 'เพิ่มครัวเรือนเรียบร้อย');
    }

    public function edit(Household $household)
    {
        $communities = Community::orderBy('community_id')->get();
        return view('households.edit', compact('household', 'communities'));
    }

    public function update(Request $request, Household $household)
    {
        $data = $request->validate([
            'account_no' => ['required','string','max:10','unique:household,account_no,' . $household->household_id . ',household_id'],
            'house_no' => ['required','string','max:20'],
            'village_no' => ['nullable','string','max:10'],
            'community_id' => ['required','string','exists:community,community_id'],
            'phone' => ['nullable','string','max:20'],
            'contact_person' => ['required','string','max:100'],
            'register_date' => ['required','date'],
            'active_status' => ['required','in:pending,active,inactive'],
            'accumulated_months' => ['required','integer','min:0'],
        ]);

        $household->update($data);

        return redirect()->route('households.index')
            ->with('success', 'แก้ไขครัวเรือนเรียบร้อย');
    }

    public function destroy(Household $household)
    {
        if ($household->transactions()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีประวัติการทำรายการที่อ้างถึงครัวเรือนนี้');
        }

        if ($household->members()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีสมาชิกครัวเรือนที่อ้างถึงครัวเรือนนี้');
        }

        if ($household->userAccounts()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีบัญชีผู้ใช้ที่ผูกกับครัวเรือนนี้');
        }

        $household->delete();

        return redirect()->route('households.index')
            ->with('success', 'ลบครัวเรือนเรียบร้อย');
    }
}

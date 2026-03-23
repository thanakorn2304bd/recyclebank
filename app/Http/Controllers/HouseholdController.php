<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Household;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HouseholdController extends Controller
{
    public function index(Request $request)
    {
        if ($this->isMember()) {
            $memberHouseholdId = $this->memberHouseholdId();

            if ($memberHouseholdId) {
                return redirect()->route('households.show', ['household' => $memberHouseholdId]);
            }

            return redirect()->route('main-menu')
                ->withErrors('ไม่พบบัญชีครัวเรือนของผู้ใช้นี้');
        }

        $q = $request->string('q')->toString();
        $communityId = $request->string('community_id')->toString();
        $status = $request->string('status')->toString();

        $householdsQuery = Household::query()
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
            ->when($status, fn($qb) => $qb->where('active_status', $status));

        $households = $householdsQuery
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
            'account_no' => ['nullable', 'string', 'size:10'],
            'house_no' => ['required','string','max:20','regex:/\d/'],
            'village_no' => ['nullable','string','max:10'],
            'community_id' => ['required','string','exists:community,community_id'],
            'phone' => ['nullable','string','max:20'],
            'contact_person' => ['required','string','max:100'],
            'register_date' => ['required','date'],
            'active_status' => ['required','in:pending,active,inactive'],
            'accumulated_months' => ['required','integer','min:0'],
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['nullable', 'string', 'max:100'],
            'members.*.id_card' => ['nullable', 'string', 'max:20'],
            'members.*.relation' => ['nullable', 'string', 'max:50'],
            'members.*.is_head' => ['nullable'],
        ], [
            'account_no.size' => 'เลขบัญชีต้องมี 10 หลัก',
            'house_no.regex' => 'บ้านเลขที่ต้องมีตัวเลขอย่างน้อย 1 หลัก',
        ]);

        $members = $this->validatedMembers($data['members'] ?? []);
        $generatedAccountNo = $this->generateAccountNo($data['community_id'], $data['house_no']);
        $data['account_no'] = trim((string) ($data['account_no'] ?? ''));

        if ($data['account_no'] === '') {
            $data['account_no'] = $generatedAccountNo;
        }

        Validator::make([
            'account_no' => $data['account_no'],
        ], [
            'account_no' => ['required', 'string', 'size:10', 'unique:household,account_no'],
        ], [
            'account_no.required' => 'กรุณากรอกเลขบัญชี',
            'account_no.size' => 'เลขบัญชีต้องมี 10 หลัก',
            'account_no.unique' => 'เลขบัญชีนี้มีอยู่แล้ว กรุณาตรวจสอบเลขที่ชุมชนหรือบ้านเลขที่',
        ])->validate();

        Validator::make([
            'username' => $data['account_no'],
        ], [
            'username' => ['required', 'string', 'max:50', 'unique:user_account,username'],
        ], [
            'username.unique' => 'เลขบัญชีนี้ชนกับชื่อผู้ใช้ที่มีอยู่ในระบบ กรุณาแก้เลขบัญชีก่อนดำเนินการต่อ',
        ])->validate();

        $data['total_balance'] = 0.00;

        $createdBy = Auth::id() ?? DB::table('user_account')->min('user_id');
        if ($createdBy) {
            $data['created_by'] = $createdBy;
        }

        $household = DB::transaction(function () use ($data, $members) {
            $household = Household::create($data);
            $this->createMembers($household, $members);

            return $household;
        });

        ActivityLogger::forCurrentUser(
            'households',
            "สร้างครัวเรือน {$household->account_no} ({$household->contact_person})"
        );

        return redirect()->route('households.credentials.create', $household)
            ->with('success', 'บันทึกข้อมูลครัวเรือนแล้ว กรุณาตั้งรหัสผ่านสำหรับเข้าใช้งาน');
    }

    public function edit(Household $household)
    {
        $communities = Community::orderBy('community_id')->get();
        return view('households.edit', compact('household', 'communities'));
    }

    public function show(Household $household)
    {
        $this->ensureCanViewHousehold($household);

        $household->load([
            'community',
            'createdByUser',
            'members' => fn($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
        ]);

        $memberAccount = $this->memberAccountFor($household);

        return view('households.show', compact('household', 'memberAccount'));
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

        $memberAccount = $this->memberAccountFor($household);

        Validator::make([
            'username' => $data['account_no'],
        ], [
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user_account', 'username')->ignore($memberAccount?->user_id, 'user_id'),
            ],
        ], [
            'username.unique' => 'เลขบัญชีนี้ชนกับชื่อผู้ใช้ที่มีอยู่ในระบบ กรุณาเปลี่ยนเลขบัญชี',
        ])->validate();

        $household->update($data);

        if ($memberAccount) {
            $this->fillMemberAccountFromHousehold($memberAccount, $household);
            $memberAccount->save();
        }

        ActivityLogger::forCurrentUser(
            'households',
            "แก้ไขครัวเรือน {$household->account_no} ({$household->contact_person}) สถานะ {$household->active_status}"
        );

        return redirect()->route('households.index')
            ->with('success', 'แก้ไขครัวเรือนเรียบร้อย');
    }

    public function createCredentials(Household $household)
    {
        $memberAccount = $this->memberAccountFor($household);

        if ($memberAccount) {
            $this->fillMemberAccountFromHousehold($memberAccount, $household);

            if ($memberAccount->isDirty()) {
                $memberAccount->save();
            }
        }

        return view('households.credentials', compact('household', 'memberAccount'));
    }

    public function storeCredentials(Request $request, Household $household)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        $memberAccount = $this->memberAccountFor($household);

        Validator::make([
            'username' => $household->account_no,
        ], [
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user_account', 'username')->ignore($memberAccount?->user_id, 'user_id'),
            ],
        ], [
            'username.unique' => 'เลขบัญชีนี้ชนกับชื่อผู้ใช้ที่มีอยู่ในระบบ กรุณาแก้เลขบัญชีก่อนตั้งรหัสผ่าน',
        ])->validate();

        $memberAccount ??= new UserAccount();
        $this->fillMemberAccountFromHousehold($memberAccount, $household);
        $memberAccount->password = $data['password'];
        $memberAccount->created_at ??= now();
        $memberAccount->last_login ??= null;
        $memberAccount->save();

        ActivityLogger::forCurrentUser(
            'households',
            "ตั้งรหัสผ่านบัญชีครัวเรือน {$memberAccount->username}"
        );

        return redirect()->route('households.show', $household)
            ->with('success', 'ตั้งรหัสผ่านครัวเรือนเรียบร้อย ชื่อผู้ใช้คือ ' . $memberAccount->username);
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

        $accountNo = $household->account_no;
        $contactPerson = $household->contact_person;

        $household->delete();

        ActivityLogger::forCurrentUser(
            'households',
            "ลบครัวเรือน {$accountNo} ({$contactPerson})"
        );

        return redirect()->route('households.index')
            ->with('success', 'ลบครัวเรือนเรียบร้อย');
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

    private function generateAccountNo(string $communityId, string $houseNo): string
    {
        $houseDigits = preg_replace('/\D+/', '', $houseNo) ?? '';

        if ($communityId === '' || $houseDigits === '') {
            return '';
        }

        $houseSuffix = str_pad(substr($houseDigits, -4), 4, '0', STR_PAD_LEFT);

        return now()->format('Y') . $communityId . $houseSuffix;
    }

    private function memberAccountFor(Household $household): ?UserAccount
    {
        return UserAccount::query()
            ->where('household_id', $household->household_id)
            ->where('role', 'member')
            ->orderBy('user_id')
            ->first();
    }

    private function fillMemberAccountFromHousehold(UserAccount $memberAccount, Household $household): void
    {
        $memberAccount->username = $household->account_no;
        $memberAccount->role = 'member';
        $memberAccount->household_id = $household->household_id;
        $memberAccount->staff_id = null;
        $memberAccount->is_active = $household->active_status !== 'inactive';
    }

    private function validatedMembers(array $members): array
    {
        $normalizedMembers = collect($members)
            ->map(function ($member) {
                return [
                    'full_name' => trim((string) ($member['full_name'] ?? '')),
                    'id_card' => preg_replace('/\D+/', '', (string) ($member['id_card'] ?? '')) ?? '',
                    'relation' => trim((string) ($member['relation'] ?? '')),
                    'is_head' => filter_var($member['is_head'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(function ($member) {
                return $member['full_name'] !== ''
                    || $member['id_card'] !== ''
                    || $member['relation'] !== ''
                    || $member['is_head'];
            })
            ->values()
            ->all();

        Validator::make([
            'members' => $normalizedMembers,
        ], [
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['required', 'string', 'max:100'],
            'members.*.id_card' => ['required', 'digits:13', 'distinct'],
            'members.*.relation' => ['required', 'string', 'max:50'],
            'members.*.is_head' => ['nullable', 'boolean'],
        ], [
            'members.*.full_name.required' => 'กรุณากรอกชื่อสมาชิกในครัวเรือนให้ครบ',
            'members.*.id_card.required' => 'กรุณากรอกเลขบัตรประชาชนของสมาชิกให้ครบ',
            'members.*.id_card.digits' => 'เลขบัตรประชาชนของสมาชิกต้องมี 13 หลัก',
            'members.*.id_card.distinct' => 'เลขบัตรประชาชนของสมาชิกห้ามซ้ำกัน',
            'members.*.relation.required' => 'กรุณากรอกความสัมพันธ์ของสมาชิกให้ครบ',
        ])->after(function ($validator) use ($normalizedMembers) {
            if ($normalizedMembers === []) {
                $validator->errors()->add('members', 'กรุณาเพิ่มสมาชิกในครัวเรือนอย่างน้อย 1 คน');
            }

            if (collect($normalizedMembers)->where('is_head', true)->count() > 1) {
                $validator->errors()->add('members', 'เลือกหัวหน้าครัวเรือนได้เพียง 1 คน');
            }
        })->validate();

        return $normalizedMembers;
    }

    private function createMembers(Household $household, array $members): void
    {
        if ($members === []) {
            return;
        }

        $household->members()->createMany($members);
    }
}

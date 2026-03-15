<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Household;
use App\Models\UserAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $communities = Community::orderBy('community_id')->get();

        return view('auth.register', compact('communities'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'community_id' => ['required', 'string', 'exists:community,community_id'],
            'house_no' => ['required', 'string', 'max:20', 'regex:/\d/'],
            'village_no' => ['nullable', 'string', 'max:10'],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['nullable', 'string', 'max:100'],
            'members.*.id_card' => ['nullable', 'string', 'max:20'],
            'members.*.relation' => ['nullable', 'string', 'max:50'],
            'members.*.is_head' => ['nullable'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'house_no.regex' => 'บ้านเลขที่ต้องมีตัวเลขอย่างน้อย 1 หลัก',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        $members = $this->validatedMembers($data['members'] ?? []);
        $data['account_no'] = $this->generateAccountNo($data['community_id'], $data['house_no']);

        Validator::make([
            'account_no' => $data['account_no'],
        ], [
            'account_no' => ['required', 'string', 'size:10', 'unique:household,account_no'],
        ], [
            'account_no.required' => 'กรุณากรอกเลขบัญชี',
            'account_no.size' => 'เลขบัญชีต้องมี 10 หลัก',
            'account_no.unique' => 'เลขบัญชีนี้ถูกใช้งานแล้ว กรุณาตรวจสอบข้อมูลอีกครั้ง',
        ])->validate();

        Validator::make([
            'username' => $data['account_no'],
        ], [
            'username' => ['required', 'string', 'max:50', 'unique:user_account,username'],
        ], [
            'username.unique' => 'เลขบัญชีนี้ถูกใช้งานเป็นชื่อผู้ใช้แล้ว กรุณาตรวจสอบข้อมูลอีกครั้ง',
        ])->validate();

        DB::transaction(function () use ($data, $members): void {
            $household = Household::create([
                'account_no' => $data['account_no'],
                'house_no' => $data['house_no'],
                'village_no' => $data['village_no'],
                'community_id' => $data['community_id'],
                'phone' => $data['phone'],
                'contact_person' => $data['contact_person'],
                'register_date' => now()->toDateString(),
                'active_status' => 'pending',
                'accumulated_months' => 0,
                'total_balance' => 0.00,
                'created_by' => null,
            ]);

            UserAccount::create([
                'username' => $household->account_no,
                'password' => $data['password'],
                'role' => 'member',
                'household_id' => $household->household_id,
                'staff_id' => null,
                'created_at' => now(),
                'last_login' => null,
                'is_active' => false,
            ]);

            $this->createMembers($household, $members);
        });

        return redirect()->route('login')->with(
            'status',
            'สมัครสมาชิกสำเร็จแล้ว ชื่อผู้ใช้คือเลขบัญชีที่สมัครไว้ และจะเข้าใช้งานได้หลัง staff/admin อนุมัติ'
        );
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

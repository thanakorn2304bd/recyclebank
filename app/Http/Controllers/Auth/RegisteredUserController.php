<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterHouseholdRequest;
use App\Models\Community;
use App\Models\Household;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
    public function store(RegisterHouseholdRequest $request): RedirectResponse
    {
        $householdAttributes = $request->householdAttributes();
        $members = $request->members();
        $password = $request->password();

        $memberAccount = DB::transaction(function () use ($householdAttributes, $members, $password): UserAccount {
            $household = Household::create($householdAttributes);

            $memberAccount = UserAccount::create([
                'username' => $household->account_no,
                'password' => $password,
                'role' => 'member',
                'household_id' => $household->household_id,
                'staff_id' => null,
                'created_at' => now(),
                'last_login' => null,
                'is_active' => false,
            ]);

            $this->createMembers($household, $members);

            return $memberAccount;
        });

        ActivityLogger::log(
            $memberAccount,
            'registration',
            'สมัครสมาชิกใหม่และส่งคำขออนุมัติบัญชี'
        );

        return redirect()->route('login')->with(
            'status',
            'สมัครสมาชิกสำเร็จแล้ว ชื่อผู้ใช้คือเลขบัญชีที่สมัครไว้ และจะเข้าใช้งานได้หลัง staff/admin อนุมัติ'
        );
    }

    private function createMembers(Household $household, array $members): void
    {
        if ($members === []) {
            return;
        }

        $household->members()->createMany($members);
    }
}

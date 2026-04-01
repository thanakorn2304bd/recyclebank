<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterHouseholdRequest;
use App\Models\Community;
use App\Models\Household;
use App\Models\PrivacyConsent;
use App\Models\PrivacyNoticeVersion;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use App\Support\Households\HouseholdViewDataFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request, HouseholdViewDataFactory $householdViewDataFactory): View
    {
        $communities = Community::orderBy('community_id')->get();
        $oldMembers = $householdViewDataFactory->oldMembers($request->old('members', []));
        $privacyNotice = $this->currentPrivacyNotice();

        return view('auth.register', compact('communities', 'oldMembers', 'privacyNotice'));
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
        $privacyNotice = $this->currentPrivacyNotice();

        $memberAccount = DB::transaction(function () use ($householdAttributes, $members, $password, $privacyNotice, $request): UserAccount {
            $household = Household::create($householdAttributes);

            $memberAccount = UserAccount::create([
                'username' => $household->account_no,
                'password' => $password,
                'password_changed_at' => now(),
                'role' => 'member',
                'household_id' => $household->household_id,
                'staff_id' => null,
                'created_at' => now(),
                'last_login' => null,
                'force_password_reset' => false,
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'is_active' => false,
            ]);

            $this->createMembers($household, $members);

            if ($privacyNotice) {
                PrivacyConsent::create([
                    'user_id' => $memberAccount->user_id,
                    'household_id' => $household->household_id,
                    'privacy_notice_version_id' => $privacyNotice->privacy_notice_version_id,
                    'consent_type' => 'registration',
                    'consented_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'consent_notes' => 'ผู้สมัครรับทราบประกาศคุ้มครองข้อมูลส่วนบุคคลก่อนส่งคำขอสมัครสมาชิก',
                    'created_at' => now(),
                ]);
            }

            return $memberAccount;
        });

        ActivityLogger::log(
            $memberAccount,
            'registration',
            'สมัครสมาชิกใหม่และส่งคำขออนุมัติบัญชี',
            [
                'entity_type' => 'user_account',
                'entity_id' => (string) $memberAccount->user_id,
                'metadata' => [
                    'privacy_notice_version' => $privacyNotice?->version_code,
                    'consent_type' => $privacyNotice ? 'registration' : null,
                ],
            ]
        );

        $message = 'สมัครสมาชิกสำเร็จแล้ว ขณะนี้คำขออยู่ระหว่างรออนุมัติจากเจ้าหน้าที่ ระบบจะแจ้งให้ใช้เลขบัญชีเป็นชื่อผู้ใช้หลังได้รับการยืนยัน';

        return redirect()->route('login')
            ->with('status', $message)
            ->with('approval_pending_notice', $message);
    }

    private function createMembers(Household $household, array $members): void
    {
        if ($members === []) {
            return;
        }

        $household->members()->createMany($members);
    }

    private function currentPrivacyNotice(): ?PrivacyNoticeVersion
    {
        return PrivacyNoticeVersion::query()
            ->where('is_active', true)
            ->where('effective_at', '<=', now())
            ->orderByDesc('effective_at')
            ->orderByDesc('privacy_notice_version_id')
            ->first();
    }
}

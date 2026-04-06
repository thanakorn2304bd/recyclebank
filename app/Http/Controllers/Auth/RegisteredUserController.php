<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterHouseholdRequest;
use App\Http\Requests\Auth\UploadRegistrationDocumentsRequest;
use App\Models\Community;
use App\Models\Household;
use App\Models\PrivacyConsent;
use App\Models\PrivacyNoticeVersion;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use App\Support\Auth\PendingHouseholdRegistrationStore;
use App\Support\Households\HouseholdViewDataFactory;
use App\Support\Households\RegistrationDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(
        Request $request,
        HouseholdViewDataFactory $householdViewDataFactory,
        PendingHouseholdRegistrationStore $pendingHouseholdRegistrationStore
    ): View {
        $registrationDraft = $pendingHouseholdRegistrationStore->get($request);
        $draftForm = is_array($registrationDraft['form'] ?? null) ? $registrationDraft['form'] : [];
        $communities = Community::orderBy('community_id')->get();
        $oldMembers = $householdViewDataFactory->oldMembers($request->old('members', $draftForm['members'] ?? []));
        $privacyNotice = $this->currentPrivacyNotice();
        $draftAccountNo = (string) ($registrationDraft['account_no'] ?? '');
        $draftToken = (string) ($registrationDraft['token'] ?? $pendingHouseholdRegistrationStore->requestToken($request) ?? '');

        return view('auth.register', compact('communities', 'oldMembers', 'privacyNotice', 'draftForm', 'draftAccountNo', 'draftToken'));
    }

    /**
     * Validate and save the first registration step in session.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(
        RegisterHouseholdRequest $request,
        PendingHouseholdRegistrationStore $pendingHouseholdRegistrationStore
    ): RedirectResponse {
        $privacyNotice = $this->currentPrivacyNotice();

        $householdAttributes = $request->householdAttributes();

        $draftToken = $pendingHouseholdRegistrationStore->put($request, [
            'account_no' => $householdAttributes['account_no'],
            'form' => $this->registrationFormDraft($request),
            'household_attributes' => $householdAttributes,
            'members' => $request->members(),
            'password_hash' => Hash::make($request->password()),
            'privacy_notice_version_id' => $privacyNotice?->privacy_notice_version_id,
        ]);

        return redirect()->route('register.documents', [
            'draft' => $draftToken,
        ]);
    }

    public function documents(
        Request $request,
        RegistrationDocumentService $registrationDocumentService,
        PendingHouseholdRegistrationStore $pendingHouseholdRegistrationStore
    ): View|RedirectResponse {
        $registration = $pendingHouseholdRegistrationStore->get($request);

        if ($registration === null) {
            return redirect()->route('register', array_filter([
                'draft' => $pendingHouseholdRegistrationStore->requestToken($request),
            ]))->withErrors([
                'registration' => 'กรุณากรอกข้อมูลสมัครสมาชิกก่อนเข้าสู่ขั้นตอนส่งเอกสารยืนยันตัวตน',
            ]);
        }

        $communityId = (string) data_get($registration, 'form.community_id', '');
        $community = Community::query()
            ->where('community_id', $communityId)
            ->first();

        return view('auth.register-documents', [
            'registration' => $registration,
            'community' => $community,
            'documentRequirements' => $registrationDocumentService->documentRequirements(),
            'registrationMembers' => $registrationDocumentService->normalizedMembers($registration['members'] ?? []),
            'draftToken' => (string) ($registration['token'] ?? ''),
        ]);
    }

    /**
     * Complete the registration after required documents are uploaded.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function complete(
        UploadRegistrationDocumentsRequest $request,
        RegistrationDocumentService $registrationDocumentService,
        PendingHouseholdRegistrationStore $pendingHouseholdRegistrationStore
    ): RedirectResponse {
        $registration = $pendingHouseholdRegistrationStore->get($request);

        if ($registration === null) {
            return redirect()->route('register')->withErrors([
                'registration' => 'ข้อมูลสมัครสมาชิกหมดอายุแล้ว กรุณากรอกใหม่อีกครั้ง',
            ]);
        }

        if (! $this->registrationDraftIsStillAvailable($registration)) {
            $pendingHouseholdRegistrationStore->forget($request);

            return redirect()->route('register')->withErrors([
                'registration' => 'เลขบัญชีที่ระบบเตรียมไว้ถูกใช้งานแล้ว กรุณากรอกข้อมูลสมัครใหม่อีกครั้ง',
            ]);
        }

        $householdAttributes = $registration['household_attributes'] ?? null;
        $members = $registration['members'] ?? [];
        $passwordHash = $registration['password_hash'] ?? null;

        if (! is_array($householdAttributes) || ! is_array($members) || ! is_string($passwordHash) || $passwordHash === '') {
            $pendingHouseholdRegistrationStore->forget($request);

            return redirect()->route('register')->withErrors([
                'registration' => 'ข้อมูลสมัครสมาชิกไม่ครบถ้วน กรุณากรอกใหม่อีกครั้ง',
            ]);
        }

        $storedDocuments = $registrationDocumentService->storeUploadedDocuments(
            $request,
            (string) ($registration['account_no'] ?? $householdAttributes['account_no'] ?? 'pending-registration'),
            $members
        );

        try {
            $privacyNotice = $this->resolvePendingPrivacyNotice($registration['privacy_notice_version_id'] ?? null);

            $memberAccount = DB::transaction(function () use ($householdAttributes, $members, $passwordHash, $privacyNotice, $request, $storedDocuments, $registrationDocumentService): UserAccount {
                $household = Household::create($householdAttributes);

                $memberAccount = UserAccount::create([
                    'username' => $household->account_no,
                    'password' => $passwordHash,
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

                $registrationDocumentService->attachStoredDocuments($household, $storedDocuments);

                return $memberAccount;
            });
        } catch (Throwable $throwable) {
            $registrationDocumentService->deleteStoredFiles($storedDocuments);

            throw $throwable;
        }

        $pendingHouseholdRegistrationStore->forget($request);

        ActivityLogger::log(
            $memberAccount,
            'registration',
            'สมัครสมาชิกใหม่พร้อมส่งเอกสารยืนยันตัวตนและคำขออนุมัติบัญชี',
            [
                'entity_type' => 'user_account',
                'entity_id' => (string) $memberAccount->user_id,
                'metadata' => [
                    'privacy_notice_version' => $privacyNotice?->version_code,
                    'consent_type' => $privacyNotice ? 'registration' : null,
                    'document_summary' => [
                        'household_copy' => collect($storedDocuments)->where('document_type', 'household_copy')->count(),
                        'national_id_copy' => collect($storedDocuments)->where('document_type', 'national_id_copy')->count(),
                    ],
                ],
            ]
        );

        $message = 'สมัครสมาชิกและส่งเอกสารยืนยันตัวตนเรียบร้อยแล้ว ขณะนี้คำขออยู่ระหว่างรออนุมัติจากเจ้าหน้าที่ ระบบจะแจ้งให้ใช้เลขบัญชีเป็นชื่อผู้ใช้หลังได้รับการยืนยัน';

        return redirect()->route('login')
            ->with('status', $message);
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

    private function registrationFormDraft(RegisterHouseholdRequest $request): array
    {
        return [
            'community_id' => (string) $request->input('community_id', ''),
            'house_no' => trim((string) $request->input('house_no', '')),
            'village_no' => $request->filled('village_no') ? trim((string) $request->input('village_no')) : null,
            'contact_person' => trim((string) $request->input('contact_person', '')),
            'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
            'members' => $request->members(),
            'accepted_privacy_notice' => true,
        ];
    }

    private function registrationDraftIsStillAvailable(array $registration): bool
    {
        $accountNo = (string) ($registration['account_no'] ?? data_get($registration, 'household_attributes.account_no', ''));

        if ($accountNo === '') {
            return false;
        }

        return ! Household::query()->where('account_no', $accountNo)->exists()
            && ! UserAccount::query()->where('username', $accountNo)->exists();
    }

    private function resolvePendingPrivacyNotice(mixed $privacyNoticeVersionId): ?PrivacyNoticeVersion
    {
        if (! is_numeric($privacyNoticeVersionId)) {
            return null;
        }

        return PrivacyNoticeVersion::query()->find((int) $privacyNoticeVersionId);
    }
}

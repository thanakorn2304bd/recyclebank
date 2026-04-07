<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResubmitRegistrationDocumentsRequest;
use App\Models\Household;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use App\Support\Households\HouseholdService;
use App\Support\Households\RegistrationDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class RegistrationStatusController extends Controller
{
    public const TRACKING_SESSION_KEY = 'auth.registration_status_user_id';

    public function show(Request $request, RegistrationDocumentService $registrationDocumentService): View|RedirectResponse
    {
        $trackedUser = $this->trackedUser($request);
        $trackedHousehold = $trackedUser?->household;

        if (! $trackedHousehold instanceof Household) {
            $request->session()->forget(self::TRACKING_SESSION_KEY);

            return redirect()->route('login');
        }

        return view('auth.registration-status', [
            'trackedUser' => $trackedUser,
            'trackedHousehold' => $trackedHousehold,
            'statusSummary' => $this->statusSummary($trackedHousehold),
            'documentRequirements' => $registrationDocumentService->documentRequirements(),
            'trackingMembers' => $registrationDocumentService->normalizedMembers($trackedHousehold->members),
            'documentLookup' => $registrationDocumentService->documentLookup($trackedHousehold),
        ]);
    }

    public function resubmitDocuments(
        ResubmitRegistrationDocumentsRequest $request,
        RegistrationDocumentService $registrationDocumentService,
        HouseholdService $householdService
    ): RedirectResponse {
        $trackedUser = $this->trackedUser($request);
        $trackedHousehold = $trackedUser?->household;

        if (! $trackedUser instanceof UserAccount || ! $trackedHousehold instanceof Household) {
            $request->session()->forget(self::TRACKING_SESSION_KEY);

            return redirect()->route('login')
                ->with('status', 'กรุณาเข้าสู่ระบบอีกครั้งก่อนแก้ไขเอกสาร');
        }

        if ($trackedHousehold->active_status !== 'rejected') {
            return redirect()->route('registration-status.show')
                ->withErrors(['tracking' => 'บัญชีนี้ไม่ได้อยู่ในสถานะไม่อนุมัติ จึงไม่สามารถส่งเอกสารใหม่ได้']);
        }

        $storedDocuments = $registrationDocumentService->storeUploadedDocuments(
            $request,
            (string) $trackedHousehold->account_no,
            $trackedHousehold->members
        );

        $oldStoredPaths = $registrationDocumentService->storedPathsForHousehold($trackedHousehold);

        try {
            DB::transaction(function () use (
                $trackedHousehold,
                $registrationDocumentService,
                $storedDocuments,
                $householdService
            ): void {
                $registrationDocumentService->deleteDocumentRecords($trackedHousehold);
                $registrationDocumentService->attachStoredDocuments($trackedHousehold, $storedDocuments);

                $trackedHousehold->forceFill([
                    'active_status' => 'pending',
                ])->save();

                $householdService->syncExistingMemberAccount($trackedHousehold);
            });
        } catch (Throwable $throwable) {
            $registrationDocumentService->deleteStoredFiles($storedDocuments);

            throw $throwable;
        }

        if ($oldStoredPaths !== []) {
            $registrationDocumentService->deleteStoredFiles(
                collect($oldStoredPaths)
                    ->map(fn ($path) => ['stored_path' => $path])
                    ->all()
            );
        }

        ActivityLogger::log(
            $trackedUser,
            'registration',
            'อัปโหลดเอกสารแก้ไขและส่งคำขอพิจารณาใหม่',
            [
                'entity_type' => 'household',
                'entity_id' => (string) $trackedHousehold->household_id,
                'metadata' => [
                    'resubmitted_document_count' => count($storedDocuments),
                ],
            ]
        );

        return redirect()->route('registration-status.show')
            ->with('status', 'ส่งเอกสารแก้ไขเรียบร้อยแล้ว ระบบได้ส่งคำขอกลับเข้าสู่สถานะรอเจ้าหน้าที่พิจารณาอีกครั้ง');
    }

    public function clearAccess(Request $request): RedirectResponse
    {
        $request->session()->forget([
            self::TRACKING_SESSION_KEY,
        ]);

        return redirect()->route('login');
    }

    private function trackedUser(Request $request): ?UserAccount
    {
        $userId = $request->session()->get(self::TRACKING_SESSION_KEY);

        if (! is_numeric($userId)) {
            return null;
        }

        $user = UserAccount::query()
            ->with([
                'household.reviewedByUser.staff',
                'household.members' => fn ($query) => $query->orderByDesc('is_head')->orderBy('full_name'),
                'household.registrationDocuments',
            ])
            ->where('user_id', (int) $userId)
            ->where('role', 'member')
            ->first();

        if (! $user instanceof UserAccount || ! $user->household instanceof Household) {
            $request->session()->forget(self::TRACKING_SESSION_KEY);

            return null;
        }

        return $user;
    }

    private function statusSummary(Household $household): array
    {
        if ($household->active_status === 'active') {
            return [
                'badge' => 'อนุมัติแล้ว',
                'badge_class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'title' => 'บัญชีนี้ได้รับการอนุมัติแล้ว',
                'description' => 'สามารถกลับไปเข้าสู่ระบบตามปกติด้วยเลขบัญชีและรหัสผ่านเดิมได้เลย',
            ];
        }

        if ($household->active_status === 'inactive') {
            return [
                'badge' => 'ปิดการใช้งาน',
                'badge_class' => 'border-gray-300 bg-gray-100 text-gray-600',
                'title' => 'บัญชีนี้ถูกปิดการใช้งาน',
                'description' => 'กรุณาติดต่อเจ้าหน้าที่เพื่อสอบถามข้อมูลเพิ่มเติม',
            ];
        }

        if ($household->active_status === 'rejected') {
            return [
                'badge' => 'ไม่อนุมัติ',
                'badge_class' => 'border-red-200 bg-red-50 text-red-700',
                'title' => 'คำขอสมัครสมาชิกไม่ผ่านการอนุมัติ',
                'description' => 'เจ้าหน้าที่พิจารณาแล้วไม่อนุมัติคำขอนี้ กรุณาตรวจสอบหมายเหตุด้านล่างสำหรับรายละเอียดเพิ่มเติม',
            ];
        }

        if ($household->reviewed_at !== null) {
            return [
                'badge' => 'ส่งแก้ไขแล้ว',
                'badge_class' => 'border-amber-200 bg-amber-50 text-amber-700',
                'title' => 'ระบบได้รับเอกสารชุดล่าสุดแล้ว',
                'description' => 'ขณะนี้คำขออยู่ระหว่างรอเจ้าหน้าที่ตรวจสอบอีกครั้ง กรุณาติดตามผลจากหน้านี้ได้ตลอดเวลา',
            ];
        }

        return [
            'badge' => 'รออนุมัติ',
            'badge_class' => 'border-amber-200 bg-amber-50 text-amber-700',
            'title' => 'คำขอสมัครสมาชิกอยู่ระหว่างรออนุมัติ',
            'description' => 'เจ้าหน้าที่กำลังตรวจสอบข้อมูลและเอกสารของครัวเรือนนี้ ก่อนเปิดให้เข้าใช้ระบบ',
        ];
    }
}

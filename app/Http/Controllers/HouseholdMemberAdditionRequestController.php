<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewHouseholdMemberAdditionRequest;
use App\Http\Requests\StoreHouseholdMemberAdditionRequest;
use App\Models\Household;
use App\Models\HouseholdMemberAdditionRequest;
use App\Models\HouseholdMemberAdditionRequestDocument;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use App\Support\Households\HouseholdMemberAdditionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HouseholdMemberAdditionRequestController extends Controller
{
    public function create(
        Request $request,
        Household $household,
        HouseholdMemberAdditionService $householdMemberAdditionService
    ): View|RedirectResponse {
        $this->authorize('view', $household);

        if ($householdMemberAdditionService->openRequestFor($household)) {
            return redirect()->route('households.show', $household)
                ->withErrors(['members' => 'ครัวเรือนนี้มีคำขอเพิ่มสมาชิกที่อยู่ระหว่างรอเจ้าหน้าที่พิจารณาอยู่แล้ว']);
        }

        $oldMembers = old('members', [['full_name' => '', 'id_card' => '', 'relation' => '']]);

        return view('households.member-additions.create', [
            'household' => $household->loadMissing('community', 'members'),
            'oldMembers' => is_array($oldMembers) && $oldMembers !== [] ? array_values($oldMembers) : [['full_name' => '', 'id_card' => '', 'relation' => '']],
            'documentRequirements' => $householdMemberAdditionService->documentRequirements(),
            'latestMemberAdditionRequest' => $householdMemberAdditionService->latestRequestFor($household),
        ]);
    }

    public function store(
        StoreHouseholdMemberAdditionRequest $request,
        Household $household,
        HouseholdMemberAdditionService $householdMemberAdditionService,
        CurrentUserIdResolver $currentUserIdResolver
    ): RedirectResponse {
        $this->authorize('view', $household);

        $memberAdditionRequest = $householdMemberAdditionService->createRequest(
            $household,
            $request->members(),
            $request->documentUploads(),
            $currentUserIdResolver->resolve($request)
        );

        ActivityLogger::forCurrentUser(
            'households.member-additions',
            "ยื่นคำขอเพิ่มสมาชิกครัวเรือน {$household->account_no}",
            [
                'entity_type' => 'household_member_addition_request',
                'entity_id' => (string) $memberAdditionRequest->member_addition_request_id,
                'after' => [
                    'status' => $memberAdditionRequest->status,
                    'requested_member_count' => $memberAdditionRequest->requestedMembers->count(),
                ],
                'metadata' => [
                    'household_id' => $household->household_id,
                    'household_account_no' => $household->account_no,
                ],
            ]
        );

        return redirect()->route('households.show', $household)
            ->with('success', 'ส่งคำขอเพิ่มสมาชิกพร้อมเอกสารประกอบเรียบร้อยแล้ว เจ้าหน้าที่จะตรวจสอบก่อนเพิ่มรายชื่อเข้าในครัวเรือน');
    }

    public function showDocument(
        Request $request,
        Household $household,
        HouseholdMemberAdditionRequest $memberAdditionRequest,
        HouseholdMemberAdditionRequestDocument $memberAdditionRequestDocument
    ): StreamedResponse {
        $this->authorize('view', $household);
        $this->ensureRequestBelongsToHousehold($household, $memberAdditionRequest);
        $this->ensureDocumentBelongsToRequest($memberAdditionRequest, $memberAdditionRequestDocument);

        $downloadName = $memberAdditionRequestDocument->original_name
            ?: basename((string) $memberAdditionRequestDocument->stored_path);
        $mimeType = $memberAdditionRequestDocument->mime_type ?: 'application/octet-stream';

        $content = $memberAdditionRequestDocument->getRawOriginal('content');

        if ($content !== null && $content !== '') {
            $disposition = $request->boolean('download') ? 'attachment' : 'inline';

            return response()->stream(function () use ($content): void {
                echo $content;
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $disposition.'; filename="'.$downloadName.'"',
                'Content-Length' => strlen($content),
            ]);
        }

        abort_unless(Storage::exists($memberAdditionRequestDocument->stored_path), 404);

        if ($request->boolean('download')) {
            return Storage::download($memberAdditionRequestDocument->stored_path, $downloadName);
        }

        return Storage::response($memberAdditionRequestDocument->stored_path, $downloadName, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function review(
        ReviewHouseholdMemberAdditionRequest $request,
        Household $household,
        HouseholdMemberAdditionRequest $memberAdditionRequest,
        HouseholdMemberAdditionService $householdMemberAdditionService,
        CurrentUserIdResolver $currentUserIdResolver
    ): RedirectResponse {
        $this->authorize('view', $household);
        $this->ensureRequestBelongsToHousehold($household, $memberAdditionRequest);

        $payload = $request->payload();
        $result = $householdMemberAdditionService->review(
            $memberAdditionRequest,
            $payload['decision'],
            $payload['review_notes'],
            $currentUserIdResolver->resolve($request)
        );

        $actionLabel = $payload['decision'] === 'approved'
            ? 'อนุมัติคำขอเพิ่มสมาชิก'
            : 'ตีกลับคำขอเพิ่มสมาชิก';

        ActivityLogger::forCurrentUser(
            'households.member-additions.review',
            "{$actionLabel} ครัวเรือน {$household->account_no}",
            [
                'entity_type' => 'household_member_addition_request',
                'entity_id' => (string) $memberAdditionRequest->member_addition_request_id,
                'before' => $result['before'],
                'after' => $result['after'],
                'metadata' => [
                    'decision' => $payload['decision'],
                    'added_member_count' => $result['added_member_count'],
                    'household_id' => $household->household_id,
                ],
            ]
        );

        $message = $payload['decision'] === 'approved'
            ? 'อนุมัติคำขอเพิ่มสมาชิกเรียบร้อยแล้ว ระบบได้เพิ่มสมาชิกใหม่เข้าในครัวเรือนนี้แล้ว'
            : 'บันทึกผลการพิจารณาคำขอเพิ่มสมาชิกเรียบร้อยแล้ว';

        return redirect()->route('households.show', $household)->with('success', $message);
    }

    private function ensureRequestBelongsToHousehold(
        Household $household,
        HouseholdMemberAdditionRequest $memberAdditionRequest
    ): void {
        abort_unless((int) $memberAdditionRequest->household_id === (int) $household->household_id, 404);
    }

    private function ensureDocumentBelongsToRequest(
        HouseholdMemberAdditionRequest $memberAdditionRequest,
        HouseholdMemberAdditionRequestDocument $memberAdditionRequestDocument
    ): void {
        abort_unless(
            (int) $memberAdditionRequestDocument->member_addition_request_id === (int) $memberAdditionRequest->member_addition_request_id,
            404
        );
    }
}

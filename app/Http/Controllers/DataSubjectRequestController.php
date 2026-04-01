<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDataSubjectRequestRequest;
use App\Models\DataSubjectRequest;
use App\Models\Household;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DataSubjectRequestController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());

        $requestsQuery = DataSubjectRequest::query()
            ->with(['household.community', 'assignedToUser.staff'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('request_no', 'like', "%{$q}%")
                        ->orWhere('requester_name', 'like', "%{$q}%")
                        ->orWhere('requester_contact', 'like', "%{$q}%")
                        ->orWhere('request_details', 'like', "%{$q}%")
                        ->orWhereHas('household', function ($householdQuery) use ($q) {
                            $householdQuery->where('account_no', 'like', "%{$q}%")
                                ->orWhere('contact_person', 'like', "%{$q}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status));

        $summary = [
            'total' => (clone $requestsQuery)->count(),
            'open' => (clone $requestsQuery)->whereIn('status', ['submitted', 'in_review'])->count(),
            'completed' => (clone $requestsQuery)->where('status', 'completed')->count(),
            'rejected' => (clone $requestsQuery)->where('status', 'rejected')->count(),
        ];

        $requests = $requestsQuery
            ->orderByDesc('submitted_at')
            ->orderByDesc('data_subject_request_id')
            ->paginate(20)
            ->withQueryString();

        return view('compliance.data_subject_requests.index', compact(
            'requests',
            'summary',
            'q',
            'status'
        ));
    }

    public function create()
    {
        ['households' => $households, 'assignees' => $assignees] = $this->formDependencies();

        return view('compliance.data_subject_requests.create', compact('households', 'assignees'));
    }

    public function store(SaveDataSubjectRequestRequest $request)
    {
        $payload = $request->payload();
        $dataSubjectRequest = DataSubjectRequest::create(array_merge($payload, [
            'request_no' => $this->nextRequestNo(),
            'due_at' => $payload['due_at'] ?? Carbon::parse($payload['submitted_at'])->addDays(30)->toDateString(),
            'closed_at' => in_array($payload['status'], ['completed', 'rejected'], true) ? now() : null,
        ]));

        ActivityLogger::forCurrentUser(
            'data_subject_requests',
            "สร้างคำขอเจ้าของข้อมูล {$dataSubjectRequest->request_no}",
            [
                'entity_type' => 'data_subject_request',
                'entity_id' => (string) $dataSubjectRequest->data_subject_request_id,
                'after' => $this->snapshot($dataSubjectRequest->fresh()),
            ]
        );

        return redirect()
            ->route('compliance.dsars.show', $dataSubjectRequest)
            ->with('success', "บันทึกคำขอเจ้าของข้อมูล {$dataSubjectRequest->request_no} เรียบร้อย");
    }

    public function show(DataSubjectRequest $dsar)
    {
        $dsar->load(['household.community', 'assignedToUser.staff']);
        ['households' => $households, 'assignees' => $assignees] = $this->formDependencies();

        return view('compliance.data_subject_requests.show', compact('dsar', 'households', 'assignees'));
    }

    public function update(SaveDataSubjectRequestRequest $request, DataSubjectRequest $dsar)
    {
        $before = $this->snapshot($dsar);
        $payload = $request->payload();
        $dsar->update(array_merge($payload, [
            'due_at' => $payload['due_at'] ?? Carbon::parse($payload['submitted_at'])->addDays(30)->toDateString(),
            'closed_at' => in_array($payload['status'], ['completed', 'rejected'], true)
                ? ($dsar->closed_at ?? now())
                : null,
        ]));

        ActivityLogger::forCurrentUser(
            'data_subject_requests',
            "อัปเดตคำขอเจ้าของข้อมูล {$dsar->request_no}",
            [
                'entity_type' => 'data_subject_request',
                'entity_id' => (string) $dsar->data_subject_request_id,
                'before' => $before,
                'after' => $this->snapshot($dsar->fresh()),
            ]
        );

        return redirect()
            ->route('compliance.dsars.show', $dsar)
            ->with('success', "อัปเดตคำขอ {$dsar->request_no} เรียบร้อย");
    }

    private function formDependencies(): array
    {
        return [
            'households' => Household::query()
                ->orderBy('account_no')
                ->get(['household_id', 'account_no', 'contact_person']),
            'assignees' => UserAccount::query()
                ->with('staff')
                ->whereIn('role', ['admin', 'staff'])
                ->orderBy('role')
                ->orderBy('username')
                ->get(['user_id', 'username', 'role', 'staff_id']),
        ];
    }

    private function nextRequestNo(): string
    {
        return 'DSAR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }

    private function snapshot(DataSubjectRequest $dataSubjectRequest): array
    {
        return [
            'request_no' => (string) $dataSubjectRequest->request_no,
            'household_id' => $dataSubjectRequest->household_id !== null ? (int) $dataSubjectRequest->household_id : null,
            'requester_name' => (string) $dataSubjectRequest->requester_name,
            'requester_contact' => $dataSubjectRequest->requester_contact,
            'request_type' => (string) $dataSubjectRequest->request_type,
            'status' => (string) $dataSubjectRequest->status,
            'submitted_at' => $dataSubjectRequest->submitted_at?->format('Y-m-d H:i:s'),
            'due_at' => $dataSubjectRequest->due_at?->format('Y-m-d'),
            'assigned_to' => $dataSubjectRequest->assigned_to !== null ? (int) $dataSubjectRequest->assigned_to : null,
            'closed_at' => $dataSubjectRequest->closed_at?->format('Y-m-d H:i:s'),
        ];
    }
}

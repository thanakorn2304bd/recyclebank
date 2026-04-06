<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSecurityIncidentRequest;
use App\Models\SecurityIncident;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use App\Support\Auth\CurrentUserIdResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SecurityIncidentController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePdpaEnabled();
        $q = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());
        $severity = trim($request->string('severity')->toString());

        $incidentsQuery = SecurityIncident::query()
            ->with(['reportedByUser.staff', 'assignedToUser.staff'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('incident_no', 'like', "%{$q}%")
                        ->orWhere('summary', 'like', "%{$q}%")
                        ->orWhere('affected_scope', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($severity !== '', fn ($query) => $query->where('severity', $severity));

        $summary = [
            'total' => (clone $incidentsQuery)->count(),
            'open' => (clone $incidentsQuery)->whereIn('status', ['open', 'contained'])->count(),
            'reported' => (clone $incidentsQuery)->where('status', 'reported')->count(),
            'critical' => (clone $incidentsQuery)->where('severity', 'critical')->count(),
        ];

        $incidents = $incidentsQuery
            ->orderByDesc('detected_at')
            ->orderByDesc('security_incident_id')
            ->paginate(20)
            ->withQueryString();

        return view('compliance.security_incidents.index', compact(
            'incidents',
            'summary',
            'q',
            'status',
            'severity'
        ));
    }

    public function create()
    {
        $this->ensurePdpaEnabled();
        $assignees = $this->assignees();

        return view('compliance.security_incidents.create', compact('assignees'));
    }

    public function store(
        SaveSecurityIncidentRequest $request,
        CurrentUserIdResolver $currentUserIdResolver
    ) {
        $this->ensurePdpaEnabled();
        $payload = $request->payload();
        $incident = SecurityIncident::create(array_merge($payload, [
            'incident_no' => $this->nextIncidentNo(),
            'reported_by' => $currentUserIdResolver->resolve($request),
            'closed_at' => $payload['status'] === 'closed' ? now() : null,
        ]));

        ActivityLogger::forCurrentUser(
            'security_incidents',
            "สร้างเหตุการณ์ข้อมูลส่วนบุคคล {$incident->incident_no}",
            [
                'entity_type' => 'security_incident',
                'entity_id' => (string) $incident->security_incident_id,
                'after' => $this->snapshot($incident->fresh()),
            ]
        );

        return redirect()
            ->route('compliance.incidents.show', $incident)
            ->with('success', "บันทึกเหตุการณ์ {$incident->incident_no} เรียบร้อย");
    }

    public function show(SecurityIncident $incident)
    {
        $this->ensurePdpaEnabled();
        $incident->load(['reportedByUser.staff', 'assignedToUser.staff']);
        $assignees = $this->assignees();

        return view('compliance.security_incidents.show', compact('incident', 'assignees'));
    }

    public function update(SaveSecurityIncidentRequest $request, SecurityIncident $incident)
    {
        $this->ensurePdpaEnabled();
        $before = $this->snapshot($incident);
        $payload = $request->payload();
        $incident->update(array_merge($payload, [
            'closed_at' => $payload['status'] === 'closed'
                ? ($incident->closed_at ?? now())
                : null,
        ]));

        ActivityLogger::forCurrentUser(
            'security_incidents',
            "อัปเดตเหตุการณ์ข้อมูลส่วนบุคคล {$incident->incident_no}",
            [
                'entity_type' => 'security_incident',
                'entity_id' => (string) $incident->security_incident_id,
                'before' => $before,
                'after' => $this->snapshot($incident->fresh()),
            ]
        );

        return redirect()
            ->route('compliance.incidents.show', $incident)
            ->with('success', "อัปเดตเหตุการณ์ {$incident->incident_no} เรียบร้อย");
    }

    private function assignees()
    {
        return UserAccount::query()
            ->with('staff')
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('role')
            ->orderBy('username')
            ->get(['user_id', 'username', 'role', 'staff_id']);
    }

    private function nextIncidentNo(): string
    {
        return 'INC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }

    private function snapshot(SecurityIncident $incident): array
    {
        return [
            'incident_no' => (string) $incident->incident_no,
            'severity' => (string) $incident->severity,
            'status' => (string) $incident->status,
            'reported_by' => $incident->reported_by !== null ? (int) $incident->reported_by : null,
            'assigned_to' => $incident->assigned_to !== null ? (int) $incident->assigned_to : null,
            'notification_required' => (bool) $incident->notification_required,
            'authority_notified_at' => $incident->authority_notified_at?->format('Y-m-d H:i:s'),
            'subject_notified_at' => $incident->subject_notified_at?->format('Y-m-d H:i:s'),
            'closed_at' => $incident->closed_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function ensurePdpaEnabled(): void
    {
        throw_unless((bool) config('features.pdpa', false), new NotFoundHttpException());
    }
}

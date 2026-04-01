<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityIncident extends Model
{
    protected $table = 'security_incident';

    protected $primaryKey = 'security_incident_id';

    public $timestamps = true;

    protected $fillable = [
        'incident_no',
        'severity',
        'status',
        'reported_by',
        'assigned_to',
        'occurred_at',
        'detected_at',
        'summary',
        'affected_scope',
        'affected_record_count',
        'notification_required',
        'authority_notified_at',
        'subject_notified_at',
        'impact_details',
        'containment_actions',
        'closed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'detected_at' => 'datetime',
        'notification_required' => 'boolean',
        'authority_notified_at' => 'datetime',
        'subject_notified_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function reportedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'reported_by', 'user_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(UserAccount::class, 'assigned_to', 'user_id');
    }
}

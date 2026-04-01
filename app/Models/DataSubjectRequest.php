<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSubjectRequest extends Model
{
    protected $table = 'data_subject_request';

    protected $primaryKey = 'data_subject_request_id';

    public $timestamps = true;

    protected $fillable = [
        'request_no',
        'household_id',
        'requester_name',
        'requester_contact',
        'request_type',
        'status',
        'submitted_at',
        'due_at',
        'assigned_to',
        'request_details',
        'resolution_notes',
        'closed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'due_at' => 'date',
        'closed_at' => 'datetime',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(UserAccount::class, 'assigned_to', 'user_id');
    }
}

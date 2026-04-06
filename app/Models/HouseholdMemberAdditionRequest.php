<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseholdMemberAdditionRequest extends Model
{
    protected $table = 'household_member_addition_request';

    protected $primaryKey = 'member_addition_request_id';

    public $timestamps = true;

    protected $fillable = [
        'household_id',
        'requested_by',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function requestedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'requested_by', 'user_id');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'reviewed_by', 'user_id');
    }

    public function requestedMembers()
    {
        return $this->hasMany(
            HouseholdMemberAdditionRequestMember::class,
            'member_addition_request_id',
            'member_addition_request_id'
        );
    }

    public function documents()
    {
        return $this->hasMany(
            HouseholdMemberAdditionRequestDocument::class,
            'member_addition_request_id',
            'member_addition_request_id'
        );
    }
}

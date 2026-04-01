<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $table = 'withdraw_request';

    protected $primaryKey = 'withdraw_request_id';

    public $timestamps = true;

    protected $fillable = [
        'request_no',
        'household_id',
        'requested_by',
        'requested_for_date',
        'requested_amount',
        'request_notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'approved_transaction_id',
    ];

    protected $casts = [
        'requested_for_date' => 'date',
        'requested_amount' => 'decimal:2',
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

    public function approvedTransaction()
    {
        return $this->belongsTo(Transaction::class, 'approved_transaction_id', 'transaction_id');
    }
}

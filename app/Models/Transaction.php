<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transaction';

    protected $primaryKey = 'transaction_id';

    public $timestamps = true;

    protected $fillable = [
        'household_id',
        'transaction_date',
        'transaction_type',
        'total_weight',
        'total_amount',
        'recorded_by',
        'is_reversal',
        'reversal_of_transaction_id',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_weight' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_reversal' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function recordedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'recorded_by', 'user_id');
    }

    public function reversedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'reversed_by', 'user_id');
    }

    public function reversalOf()
    {
        return $this->belongsTo(self::class, 'reversal_of_transaction_id', 'transaction_id');
    }

    public function reversalTransaction()
    {
        return $this->hasOne(self::class, 'reversal_of_transaction_id', 'transaction_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'transaction_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $table = 'household';
    protected $primaryKey = 'household_id';

    // timestamps = true (เพราะคุณเพิ่ม created_at/updated_at แล้ว)
    public $timestamps = true;

    protected $fillable = [
        'account_no',
        'house_no',
        'village_no',
        'community_id',
        'phone',
        'contact_person',
        'register_date',
        'active_status',
        'accumulated_months',
        'total_balance',
        'created_by',
    ];

    protected $casts = [
        'register_date' => 'date',
        'total_balance' => 'decimal:2',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id', 'community_id');
    }

    public function members()
    {
        return $this->hasMany(Member::class, 'household_id', 'household_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'household_id', 'household_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(UserAccount::class, 'created_by', 'user_id');
    }

    public function userAccounts()
    {
        return $this->hasMany(UserAccount::class, 'household_id', 'household_id');
    }
}

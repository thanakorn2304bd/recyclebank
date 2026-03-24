<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_account';

    protected $primaryKey = 'user_id';

    // ตารางนี้มี created_at/last_login แต่ไม่ใช่ created_at/updated_at คู่มาตรฐาน Laravel
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'role',
        'household_id',
        'staff_id',
        'created_at',
        'last_login',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function createdMaterialPrices()
    {
        return $this->hasMany(MaterialPrice::class, 'created_by', 'user_id');
    }

    public function recordedTransactions()
    {
        return $this->hasMany(Transaction::class, 'recorded_by', 'user_id');
    }

    public function logs()
    {
        return $this->hasMany(LogActivity::class, 'user_id', 'user_id');
    }
}

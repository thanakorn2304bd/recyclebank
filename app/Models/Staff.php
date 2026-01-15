<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'phone',
        'position',
    ];

    public function userAccounts()
    {
        return $this->hasMany(UserAccount::class, 'staff_id', 'staff_id');
    }
}

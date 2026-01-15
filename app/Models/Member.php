<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'member';
    protected $primaryKey = 'member_id';

    public $timestamps = true;

    protected $fillable = [
        'household_id',
        'full_name',
        'id_card',
        'is_head',
        'relation',
    ];

    protected $casts = [
        'is_head' => 'boolean',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }
}

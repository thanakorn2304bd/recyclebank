<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $table = 'community';
    protected $primaryKey = 'community_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'community_id',
        'community_name',
    ];

    public function households()
    {
        return $this->hasMany(Household::class, 'community_id', 'community_id');
    }
}

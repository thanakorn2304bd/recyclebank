<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $table = 'log_activity';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'timestamp',
        'module',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
        'before_values',
        'after_values',
        'metadata',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'before_values' => 'array',
        'after_values' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
}

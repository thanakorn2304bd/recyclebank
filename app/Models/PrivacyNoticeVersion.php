<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyNoticeVersion extends Model
{
    protected $table = 'privacy_notice_version';

    protected $primaryKey = 'privacy_notice_version_id';

    public $timestamps = true;

    protected $fillable = [
        'version_code',
        'title',
        'summary',
        'content',
        'effective_at',
        'is_active',
        'published_by',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function publishedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'published_by', 'user_id');
    }

    public function consents()
    {
        return $this->hasMany(PrivacyConsent::class, 'privacy_notice_version_id', 'privacy_notice_version_id');
    }
}

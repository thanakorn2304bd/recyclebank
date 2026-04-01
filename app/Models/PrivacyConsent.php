<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyConsent extends Model
{
    protected $table = 'privacy_consent';

    protected $primaryKey = 'privacy_consent_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'household_id',
        'privacy_notice_version_id',
        'consent_type',
        'consented_at',
        'ip_address',
        'user_agent',
        'consent_notes',
        'created_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function noticeVersion()
    {
        return $this->belongsTo(PrivacyNoticeVersion::class, 'privacy_notice_version_id', 'privacy_notice_version_id');
    }
}

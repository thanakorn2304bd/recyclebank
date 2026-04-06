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
        'password_changed_at',
        'role',
        'household_id',
        'staff_id',
        'created_at',
        'last_login',
        'force_password_reset',
        'failed_login_attempts',
        'locked_until',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'last_login' => 'datetime',
        'password_changed_at' => 'datetime',
        'is_active' => 'boolean',
        'force_password_reset' => 'boolean',
        'failed_login_attempts' => 'integer',
        'locked_until' => 'datetime',
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

    public function publishedPrivacyNotices()
    {
        return $this->hasMany(PrivacyNoticeVersion::class, 'published_by', 'user_id');
    }

    public function privacyConsents()
    {
        return $this->hasMany(PrivacyConsent::class, 'user_id', 'user_id');
    }

    public function assignedDataSubjectRequests()
    {
        return $this->hasMany(DataSubjectRequest::class, 'assigned_to', 'user_id');
    }

    public function reportedSecurityIncidents()
    {
        return $this->hasMany(SecurityIncident::class, 'reported_by', 'user_id');
    }

    public function assignedSecurityIncidents()
    {
        return $this->hasMany(SecurityIncident::class, 'assigned_to', 'user_id');
    }

    public function requestedWithdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class, 'requested_by', 'user_id');
    }

    public function reviewedWithdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class, 'reviewed_by', 'user_id');
    }

    public function requestedHouseholdMemberAdditionRequests()
    {
        return $this->hasMany(HouseholdMemberAdditionRequest::class, 'requested_by', 'user_id');
    }

    public function reviewedHouseholdMemberAdditionRequests()
    {
        return $this->hasMany(HouseholdMemberAdditionRequest::class, 'reviewed_by', 'user_id');
    }

    public function applyPassword(string $password, bool $forcePasswordReset = false): void
    {
        $this->password = $password;
        $this->password_changed_at = now();
        $this->force_password_reset = $forcePasswordReset;
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
    }

    public function clearLoginLockState(): void
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }
}

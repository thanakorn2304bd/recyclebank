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
        'id_card_last4',
        'id_card_hash',
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

    public function getMaskedIdCardAttribute(): ?string
    {
        $last4 = $this->id_card_last4 ?: self::extractIdCardLast4($this->id_card);

        if (! $last4) {
            return null;
        }

        return 'x-xxxx-xxxxx-'.$last4;
    }

    public static function normalizeIdCard(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) ($value ?? '')) ?? '';
    }

    public static function extractIdCardLast4(?string $value): ?string
    {
        $normalized = self::normalizeIdCard($value);

        return $normalized !== '' ? substr($normalized, -4) : null;
    }

    public static function hashIdCard(?string $value): ?string
    {
        $normalized = self::normalizeIdCard($value);

        return $normalized !== '' ? hash('sha256', $normalized) : null;
    }
}

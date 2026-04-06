<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseholdMemberAdditionRequestMember extends Model
{
    protected $table = 'household_member_addition_request_member';

    protected $primaryKey = 'member_addition_request_member_id';

    public $timestamps = true;

    protected $fillable = [
        'member_addition_request_id',
        'full_name',
        'id_card',
        'id_card_last4',
        'id_card_hash',
        'relation',
    ];

    public function memberAdditionRequest()
    {
        return $this->belongsTo(
            HouseholdMemberAdditionRequest::class,
            'member_addition_request_id',
            'member_addition_request_id'
        );
    }

    public function getMaskedIdCardAttribute(): ?string
    {
        $last4 = $this->id_card_last4 ?: Member::extractIdCardLast4($this->id_card);

        if (! $last4) {
            return null;
        }

        return 'x-xxxx-xxxxx-'.$last4;
    }
}

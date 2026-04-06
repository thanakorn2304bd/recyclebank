<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseholdMemberAdditionRequestDocument extends Model
{
    protected $table = 'household_member_addition_request_document';

    protected $primaryKey = 'member_addition_request_document_id';

    public $timestamps = true;

    protected $fillable = [
        'member_addition_request_id',
        'document_type',
        'member_position',
        'member_full_name',
        'member_relation',
        'member_id_card_last4',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'member_position' => 'integer',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function memberAdditionRequest()
    {
        return $this->belongsTo(
            HouseholdMemberAdditionRequest::class,
            'member_addition_request_id',
            'member_addition_request_id'
        );
    }
}

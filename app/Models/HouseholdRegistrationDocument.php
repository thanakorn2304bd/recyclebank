<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseholdRegistrationDocument extends Model
{
    protected $table = 'household_registration_document';

    protected $primaryKey = 'registration_document_id';

    public $timestamps = false;

    protected $fillable = [
        'household_id',
        'document_type',
        'member_position',
        'member_full_name',
        'member_relation',
        'member_id_card_last4',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'created_at',
    ];

    protected $casts = [
        'member_position' => 'integer',
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }
}

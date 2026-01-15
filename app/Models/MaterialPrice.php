<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialPrice extends Model
{
    protected $table = 'material_price';
    protected $primaryKey = 'price_id';

    // มี created_at แต่ไม่มี updated_at -> ปิด timestamps
    public $timestamps = false;

    protected $fillable = [
        'material_id',
        'price',
        'effective_date',
        'expired_date',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'effective_date' => 'date',
        'expired_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(UserAccount::class, 'created_by', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $table = 'transaction_detail';
    protected $primaryKey = 'detail_id';

    public $timestamps = true;

    protected $fillable = [
        'transaction_id',
        'material_id',
        'weight',
        'price_per_unit',
        'amount',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }

    
    public function material()
    {
        return $this->belongsTo(\App\Models\Material::class, 'material_id', 'material_id');
    }

}

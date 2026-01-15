<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'material';
    protected $primaryKey = 'material_id';

    public $timestamps = true;

    protected $fillable = [
        'category_id',
        'material_name',
        'unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id', 'category_id');
    }

    public function prices()
    {
        return $this->hasMany(MaterialPrice::class, 'material_id', 'material_id');
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'material_id', 'material_id');
    }
}

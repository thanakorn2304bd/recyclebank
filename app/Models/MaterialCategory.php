<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialCategory extends Model
{
    protected $table = 'material_category';
    protected $primaryKey = 'category_id';

    public $timestamps = true;

    protected $fillable = [
        'category_name',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class, 'category_id', 'category_id');
    }
}

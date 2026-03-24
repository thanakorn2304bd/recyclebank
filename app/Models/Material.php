<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function scopeJoinCurrentPriceAt(Builder $query, string $date): Builder
    {
        return $query
            ->leftJoinSub(self::currentPriceReferenceQuery($date), 'current_price_ref', function ($join) {
                $join->on('material.material_id', '=', 'current_price_ref.material_id');
            })
            ->leftJoin('material_price as current_price', 'current_price.price_id', '=', 'current_price_ref.price_id');
    }

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

    protected static function currentPriceReferenceQuery(string $date)
    {
        return DB::table('material_price as current_price')
            ->select('current_price.material_id', 'current_price.price_id')
            ->where('current_price.effective_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('current_price.expired_date')
                    ->orWhere('current_price.expired_date', '>=', $date);
            })
            ->whereNotExists(function ($query) use ($date) {
                $query->select(DB::raw(1))
                    ->from('material_price as newer_price')
                    ->whereColumn('newer_price.material_id', 'current_price.material_id')
                    ->where('newer_price.effective_date', '<=', $date)
                    ->where(function ($subQuery) use ($date) {
                        $subQuery->whereNull('newer_price.expired_date')
                            ->orWhere('newer_price.expired_date', '>=', $date);
                    })
                    ->where(function ($subQuery) {
                        $subQuery->whereColumn('newer_price.effective_date', '>', 'current_price.effective_date')
                            ->orWhere(function ($tieQuery) {
                                $tieQuery->whereColumn('newer_price.effective_date', 'current_price.effective_date')
                                    ->whereColumn('newer_price.price_id', '>', 'current_price.price_id');
                            });
                    });
            });
    }
}

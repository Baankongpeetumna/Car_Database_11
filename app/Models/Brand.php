<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Brand extends Model
{
    protected $table = 'BRAND';
    protected $primaryKey = 'brand_id';
    public $timestamps = false;

    protected $fillable = ['brand_name', 'country'];
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'brand_id', 'brand_id');
    }
}
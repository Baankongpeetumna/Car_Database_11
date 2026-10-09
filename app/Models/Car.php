<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $table = 'CAR';
    protected $primaryKey = 'car_id';
    public $timestamps = false;

    protected $fillable = [
        'model_name',
        'model_year',
        'color',
        'fuel_type',
        'transmission',
        'engine_cc',
        'mileage_km',
        'car_condition',
        'price',
        'stock_qty',
        'description',
        'image_url',
        'brand_id',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'car_id', 'car_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'ORDER_ITEM', 'car_id', 'order_id', 'car_id', 'order_id')
            ->withPivot('quantity', 'unit_price');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cart extends Model
{
    protected $table = 'CART';
    protected $primaryKey = 'cart_id';
    public $timestamps = false;

    protected $fillable = ['member_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'member_id');
    }

    // ของในตะกร้า(ตาราง CART_ITEM) อ่านจำนวนด้วย $car->pivot->quantity
    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'CART_ITEM', 'cart_id', 'car_id', 'cart_id', 'car_id')
            ->withPivot('quantity');
    }
}
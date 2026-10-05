<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $table = 'ORDERS';
    protected $primaryKey = 'order_id';
    public $timestamps = false;

    protected $fillable = [
        'status',
        'payment_method',
        'shipping_address',
        'subtotal',
        'discount_amount',
        'total_amount',
        'points_earned',
        'member_id',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'points_earned' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'member_id');
    }

    // ของในออเดอร์(ตาราง ORDER_ITEM) อ่านด้วย $car->pivot->quantity และ ->unit_price
    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'ORDER_ITEM', 'order_id', 'car_id', 'order_id', 'car_id')
            ->withPivot('quantity', 'unit_price');
    }
}
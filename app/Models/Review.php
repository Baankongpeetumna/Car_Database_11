<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    // รีวิวได้เฉพาะรถที่อยู่ในออเดอร์ที่ admin ยืนยันว่าสำเร็จแล้ว
    public const ELIGIBLE_ORDER_STATUS = 'completed';

    protected $table = 'REVIEW';
    protected $primaryKey = 'review_id';
    public $timestamps = false;

    protected $fillable = ['comment', 'rating', 'member_id', 'car_id'];

    protected $casts = [
        'created_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'member_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    // จำนวนรีวิวที่สมาชิกยังเขียนให้รถคันนี้ได้
    // = จำนวนออเดอร์ completed ที่มีรถคันนี้ - จำนวนรีวิวที่เขียนไปแล้ว
    // (ซื้อ 2 ออเดอร์ รีวิวได้ 2 ครั้ง)
    public static function remainingFor(User $member, Car $car): int
    {
        $purchases = Order::where('member_id', $member->member_id)
            ->where('status', self::ELIGIBLE_ORDER_STATUS)
            ->whereHas('cars', fn ($q) => $q->where('CAR.car_id', $car->car_id))
            ->count();

        $written = self::where('member_id', $member->member_id)
            ->where('car_id', $car->car_id)
            ->count();

        return max(0, $purchases - $written);
    }
}

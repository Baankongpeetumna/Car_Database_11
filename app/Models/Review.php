<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'REVIEW';
    protected $primaryKey = 'review_id';
    public $timestamps = false;

    protected $fillable = ['comment', 'member_id', 'car_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'member_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
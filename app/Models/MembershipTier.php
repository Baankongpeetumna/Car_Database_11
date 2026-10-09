<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipTier extends Model
{
    protected $table = 'MEMBERSHIP_TIER';
    protected $primaryKey = 'tier_id';
    public $timestamps = false;

    protected $fillable = ['tier_name', 'min_points', 'discount_percent'];

    protected $casts = [
        'min_points' => 'integer',
        'discount_percent' => 'decimal:2',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'tier_id', 'tier_id');
    }
}
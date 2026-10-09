<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipTier extends Model
{
    protected $table = 'MEMBERSHIP_TIER';

    protected $primaryKey = 'tier_id';

    public $timestamps = false;

    protected $fillable = [
        'tier_name',
        'min_points',
        'discount_percent',
        'color',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'discount_percent' => 'decimal:2',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'tier_id', 'tier_id');
    }

    public function getColorHexAttribute(): string
    {
        $color = trim((string) $this->color);

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return strtoupper($color);
        }

        return match (mb_strtolower(trim((string) $this->tier_name))) {
            'basic' => '#71717A',
            'bronze' => '#B45309',
            'silver' => '#0EA5E9',
            'gold' => '#F59E0B',
            'platinum' => '#8B5CF6',
            'diamond' => '#06B6D4',
            'elite' => '#EC4899',
            'legend' => '#10B981',
            'titan' => '#F97316',
            'king' => '#EF4444',
            default => '#6366F1',
        };
    }
}
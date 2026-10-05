<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'password',
    'phone',
    'address',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'MEMBER';

    protected $primaryKey = 'member_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'points' => 'integer',
            'tier_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'tier_id', 'tier_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'member_id', 'member_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'member_id', 'member_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'member_id', 'member_id');
    }

    /**
     * ชื่อเต็มสำหรับ Layout ของ Starter Kit
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }
}
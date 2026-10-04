<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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
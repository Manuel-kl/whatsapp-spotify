<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpotifyToken extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    public function willExpireSoon(): bool
    {
        return $this->expires_at < now()->addMinutes(5);
    }
}

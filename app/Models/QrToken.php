<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrToken extends Model
{
    # how long one code can be scanned, in seconds
    public const LIFETIME = 120;

    # the screen swaps to a new code this many seconds early
    public const SWAP_EARLY = 5;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function issue(): self
    {
        return static::create([
            'token' => Str::random(40),
            'expires_at' => now()->addSeconds(self::LIFETIME),
        ]);
    }

    public function scopeUsable($query)
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    public function ticket()
    {
        return $this->belongsTo(QueueTicket::class, 'ticket_id');
    }
}

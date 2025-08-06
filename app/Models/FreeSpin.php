<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class FreeSpin extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'used_amount',
        'source',
        'bet_value',
        'game_restriction',
        'expires_at',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'bet_value' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FreeSpinTransaction::class);
    }

    // Check if free spins are still valid
    public function isValid(): bool
    {
        return $this->is_active &&
            $this->getRemainingSpins() > 0 &&
            ($this->expires_at === null || $this->expires_at->isFuture());
    }

    // Get remaining spins
    public function getRemainingSpins(): int
    {
        return max(0, $this->amount - $this->used_amount);
    }

    // Check if expired
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->where(function($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereRaw('amount > used_amount');
    }

    public function scopeForGame($query, $gameId)
    {
        return $query->where(function($q) use ($gameId) {
            $q->whereNull('game_restriction')
                ->orWhere('game_restriction', $gameId);
        });
    }
}

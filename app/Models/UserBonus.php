<?php

declare(strict_types=1);

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bonus_type_id', 'status', 'amount', 'used_amount',
        'wagering_requirement', 'wagered_amount', 'expires_at',
        'activated_at', 'completed_at', 'metadata'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'wagering_requirement' => 'decimal:2',
        'wagered_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bonusType(): BelongsTo
    {
        return $this->belongsTo(BonusType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BonusTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getRemainingAmount(): int
    {
        return $this->amount - $this->used_amount;
    }

    public function getWageringProgress(): float
    {
        if ($this->wagering_requirement <= 0) {
            return 100;
        }
        return min(100, ($this->wagered_amount / $this->wagering_requirement) * 100);
    }

    public function isWageringComplete(): bool
    {
        return $this->wagered_amount >= $this->wagering_requirement;
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'used',
            'completed_at' => now(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}

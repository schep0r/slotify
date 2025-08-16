<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function userBonuses(): HasMany
    {
        return $this->hasMany(UserBonus::class);
    }

    public function bonusClaims(): HasMany
    {
        return $this->hasMany(BonusClaim::class);
    }

    public function isClaimable(): bool
    {
        return $this->is_active && $this->config['is_claimable'] ?? false;
    }

    public function getCooldownPeriod(): int
    {
        return $this->config['cooldown_hours'] ?? 24;
    }

    public function getMaxClaims(): ?int
    {
        return $this->config['max_claims'] ?? null;
    }
}

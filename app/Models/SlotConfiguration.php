<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SlotConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'theme',
        'reels',
        'rows',
        'paylines',
        'rtp_percentage',
        'volatility',
        'min_bet',
        'max_bet',
        'bet_increment',
        'bet_levels',
        'has_progressive_jackpot',
        'jackpot_seed',
        'jackpot_contribution_rate',
        'symbols',
        'wild_symbols',
        'scatter_symbols',
        'bonus_symbols',
        'paytable',
        'special_features',
        'has_free_spins',
        'free_spins_trigger_count',
        'free_spins_award',
        'free_spins_multiplier',
        'has_bonus_game',
        'bonus_game_config',
        'auto_play_enabled',
        'max_auto_spins',
        'is_active',
        'current_jackpot',
        'total_spins',
        'total_wagered',
        'total_paid_out',
        'description',
        'metadata',
    ];

    protected $casts = [
        'bet_levels' => 'array',
        'symbols' => 'array',
        'wild_symbols' => 'array',
        'scatter_symbols' => 'array',
        'bonus_symbols' => 'array',
        'paytable' => 'array',
        'special_features' => 'array',
        'bonus_game_config' => 'array',
        'metadata' => 'array',
        'rtp_percentage' => 'decimal:2',
        'min_bet' => 'decimal:2',
        'max_bet' => 'decimal:2',
        'bet_increment' => 'decimal:2',
        'jackpot_seed' => 'decimal:2',
        'jackpot_contribution_rate' => 'decimal:4',
        'current_jackpot' => 'decimal:2',
        'free_spins_multiplier' => 'decimal:2',
        'total_wagered' => 'decimal:2',
        'total_paid_out' => 'decimal:2',
        'has_progressive_jackpot' => 'boolean',
        'has_free_spins' => 'boolean',
        'has_bonus_game' => 'boolean',
        'auto_play_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the game that owns the slot configuration.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the game sessions for this slot configuration.
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    /**
     * Get the transactions related to this slot configuration through game sessions.
     */
    public function transactions(): hasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, GameSession::class);
    }

    /**
     * Scope a query to only include active configurations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by volatility.
     */
    public function scopeVolatility($query, $volatility)
    {
        return $query->where('volatility', $volatility);
    }

    /**
     * Scope a query to filter by RTP range.
     */
    public function scopeRtpRange($query, $minRtp, $maxRtp)
    {
        return $query->whereBetween('rtp_percentage', [$minRtp, $maxRtp]);
    }

    /**
     * Get the current RTP based on actual gameplay.
     */
    public function getActualRtpAttribute()
    {
        if ($this->total_wagered == 0) {
            return 0;
        }

        return ($this->total_paid_out / $this->total_wagered) * 100;
    }

    /**
     * Get the house edge percentage.
     */
    public function getHouseEdgeAttribute()
    {
        return 100 - $this->rtp_percentage;
    }

    /**
     * Check if bet amount is valid for this configuration.
     */
    public function isValidBet($amount): bool
    {
        return $amount >= $this->min_bet &&
            $amount <= $this->max_bet &&
            fmod($amount - $this->min_bet, $this->bet_increment) == 0;
    }

    /**
     * Get available bet levels.
     */
    public function getBetLevels(): array
    {
        if (!empty($this->bet_levels)) {
            return $this->bet_levels;
        }

        // Generate default bet levels if not specified
        $levels = [];
        $currentBet = $this->min_bet;

        while ($currentBet <= $this->max_bet) {
            $levels[] = $currentBet;
            $currentBet += $this->bet_increment;
        }

        return $levels;
    }

    /**
     * Update game statistics after a spin.
     */
    public function updateStats($betAmount, $winAmount): void
    {
        $this->increment('total_spins');
        $this->increment('total_wagered', $betAmount);
        $this->increment('total_paid_out', $winAmount);

        // Update progressive jackpot if enabled
        if ($this->has_progressive_jackpot) {
            $contribution = $betAmount * $this->jackpot_contribution_rate;
            $this->increment('current_jackpot', $contribution);
        }

        $this->save();
    }

    /**
     * Reset progressive jackpot to seed value.
     */
    public function resetJackpot(): void
    {
        if ($this->has_progressive_jackpot) {
            $this->current_jackpot = $this->jackpot_seed;
            $this->save();
        }
    }

    /**
     * Get symbol by ID.
     */
    public function getSymbol($symbolId)
    {
        return collect($this->symbols)->firstWhere('id', $symbolId);
    }

    /**
     * Get a winning combination payout.
     */
    public function getPayout($symbolId, $count, $paylineIndex = null)
    {
        $paytable = collect($this->paytable);

        $combination = $paytable->first(function ($combo) use ($symbolId, $count, $paylineIndex) {
            return $combo['symbol_id'] === $symbolId &&
                $combo['count'] === $count &&
                ($paylineIndex === null || in_array($paylineIndex, $combo['paylines'] ?? []));
        });

        return $combination['payout'] ?? 0;
    }

    /**
     * Check if the configuration is properly set up.
     */
    public function isConfigured(): bool
    {
        return !empty($this->symbols) &&
            !empty($this->paytable) &&
            $this->rtp_percentage > 0 &&
            $this->min_bet > 0;
    }
}

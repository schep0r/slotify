<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouletteConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'wheel_type',
        'min_bet',
        'max_bet',
        'table_limits',
        'special_rules',
        'rtp_percentage',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'table_limits' => 'array',
        'special_rules' => 'array',
        'metadata' => 'array',
        'rtp_percentage' => 'decimal:2',
        'min_bet' => 'decimal:2',
        'max_bet' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the game that owns the roulette configuration.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Scope a query to only include active configurations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if bet amount is valid for specific bet type.
     */
    public function isValidBet(string $betType, float $amount): bool
    {
        if (!isset($this->table_limits[$betType])) {
            return $amount >= $this->min_bet && $amount <= $this->max_bet;
        }

        $limits = $this->table_limits[$betType];
        return $amount >= $limits['min'] && $amount <= $limits['max'];
    }

    /**
     * Get table limits for a specific bet type.
     */
    public function getBetLimits(string $betType): array
    {
        return $this->table_limits[$betType] ?? [
            'min' => $this->min_bet,
            'max' => $this->max_bet
        ];
    }

    /**
     * Get all available bet types with their limits.
     */
    public function getAvailableBetTypes(): array
    {
        $defaultLimits = ['min' => $this->min_bet, 'max' => $this->max_bet];
        
        return [
            'straight' => $this->table_limits['straight'] ?? $defaultLimits,
            'split' => $this->table_limits['split'] ?? $defaultLimits,
            'street' => $this->table_limits['street'] ?? $defaultLimits,
            'corner' => $this->table_limits['corner'] ?? $defaultLimits,
            'line' => $this->table_limits['line'] ?? $defaultLimits,
            'dozen' => $this->table_limits['dozen'] ?? $defaultLimits,
            'column' => $this->table_limits['column'] ?? $defaultLimits,
            'red' => $this->table_limits['red'] ?? $defaultLimits,
            'black' => $this->table_limits['black'] ?? $defaultLimits,
            'odd' => $this->table_limits['odd'] ?? $defaultLimits,
            'even' => $this->table_limits['even'] ?? $defaultLimits,
            'low' => $this->table_limits['low'] ?? $defaultLimits,
            'high' => $this->table_limits['high'] ?? $defaultLimits,
        ];
    }

    /**
     * Check if the configuration is properly set up.
     */
    public function isConfigured(): bool
    {
        return !empty($this->wheel_type) &&
            $this->min_bet > 0 &&
            $this->max_bet > $this->min_bet &&
            !empty($this->table_limits);
    }

    /**
     * Get default table limits for roulette.
     */
    public static function getDefaultTableLimits(): array
    {
        return [
            'straight' => ['min' => 1.00, 'max' => 100.00],
            'split' => ['min' => 1.00, 'max' => 200.00],
            'street' => ['min' => 1.00, 'max' => 300.00],
            'corner' => ['min' => 1.00, 'max' => 400.00],
            'line' => ['min' => 1.00, 'max' => 600.00],
            'dozen' => ['min' => 1.00, 'max' => 1200.00],
            'column' => ['min' => 1.00, 'max' => 1200.00],
            'red' => ['min' => 1.00, 'max' => 1800.00],
            'black' => ['min' => 1.00, 'max' => 1800.00],
            'odd' => ['min' => 1.00, 'max' => 1800.00],
            'even' => ['min' => 1.00, 'max' => 1800.00],
            'low' => ['min' => 1.00, 'max' => 1800.00],
            'high' => ['min' => 1.00, 'max' => 1800.00],
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'game_session_id',
        'user_id',
        'game_id',
        'bet_amount',
        'win_amount',
        'net_result',
        'balance_before',
        'balance_after',
        'reel_result',
        'paylines_won',
        'multipliers',
        'bonus_features',
        'lines_played',
        'bet_per_line',
        'rng_seed',
        'rtp_contribution',
        'is_bonus_round',
        'bonus_type',
        'free_spins_remaining',
        'transaction_ref',
        'ip_address',
        'user_agent',
        'round_status',
        'completed_at',
        'completion_hash',
        'extra_data'
    ];

    protected $casts = [
        'bet_amount' => 'decimal:4',
        'win_amount' => 'decimal:4',
        'net_result' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'bet_per_line' => 'decimal:4',
        'rtp_contribution' => 'decimal:4',
        'reel_result' => 'array',
        'paylines_won' => 'array',
        'multipliers' => 'array',
        'bonus_features' => 'array',
        'extra_data' => 'array',
        'is_bonus_round' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($gameRound) {
            if (empty($gameRound->round_id)) {
                $gameRound->round_id = (string) Str::uuid();
            }

            // Auto-calculate net result
            $gameRound->net_result = $gameRound->win_amount - $gameRound->bet_amount;

            // Set completion timestamp
            if ($gameRound->round_status === 'completed' && !$gameRound->completed_at) {
                $gameRound->completed_at = now();
            }

            // Generate completion hash for integrity
            $gameRound->completion_hash = $gameRound->generateCompletionHash();
        });
    }

    // Relationships
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function isWinningRound(): bool
    {
        return $this->win_amount > 0;
    }

    public function isBigWin(float $multiplier = 10.0): bool
    {
        return $this->win_amount >= ($this->bet_amount * $multiplier);
    }

    public function getRtpPercentage(): float
    {
        if ($this->bet_amount <= 0) {
            return 0;
        }

        return ($this->win_amount / $this->bet_amount) * 100;
    }

    public function getMultiplier(): float
    {
        if ($this->bet_amount <= 0) {
            return 0;
        }

        return $this->win_amount / $this->bet_amount;
    }

    public function hasBonus(): bool
    {
        return $this->is_bonus_round || !empty($this->bonus_features);
    }

    public function getPaylineCount(): int
    {
        return is_array($this->paylines_won) ? count($this->paylines_won) : 0;
    }

    // Generate integrity hash
    public function generateCompletionHash(): string
    {
        $data = [
            'round_id' => $this->round_id,
            'user_id' => $this->user_id,
            'bet_amount' => $this->bet_amount,
            'win_amount' => $this->win_amount,
            'reel_result' => $this->reel_result,
            'rng_seed' => $this->rng_seed,
            'timestamp' => $this->completed_at ? $this->completed_at->timestamp : now()->timestamp
        ];

        return hash('sha256', json_encode($data));
    }

    // Verify round integrity
    public function verifyIntegrity(): bool
    {
        $expectedHash = $this->generateCompletionHash();
        return hash_equals($this->completion_hash, $expectedHash);
    }

    // Scopes
    public function scopeWinning($query)
    {
        return $query->where('win_amount', '>', 0);
    }

    public function scopeLosing($query)
    {
        return $query->where('win_amount', '=', 0);
    }

    public function scopeBigWins($query, float $multiplier = 10.0)
    {
        return $query->whereRaw('win_amount >= (bet_amount * ?)', [$multiplier]);
    }

    public function scopeByGame($query, string $gameId)
    {
        return $query->where('game_id', $gameId);
    }

    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeBonusRounds($query)
    {
        return $query->where('is_bonus_round', true);
    }

    public function scopeRegularRounds($query)
    {
        return $query->where('is_bonus_round', false);
    }

    // Analytics methods
    public static function calculateRtpForPeriod(Carbon $startDate, Carbon $endDate, ?string $gameId = null)
    {
        $query = static::whereBetween('created_at', [$startDate, $endDate])
            ->where('round_status', 'completed');

        if ($gameId) {
            $query->where('game_id', $gameId);
        }

        $totalBet = $query->sum('bet_amount');
        $totalWin = $query->sum('win_amount');

        return $totalBet > 0 ? ($totalWin / $totalBet) * 100 : 0;
    }

    public static function getBigWinners(Carbon $startDate, Carbon $endDate, float $minMultiplier = 50.0)
    {
        return static::whereBetween('created_at', [$startDate, $endDate])
            ->whereRaw('win_amount >= (bet_amount * ?)', [$minMultiplier])
            ->with(['user', 'gameSession'])
            ->orderByDesc('win_amount')
            ->get();
    }

    public static function getGameStats(string $gameId, Carbon $startDate, Carbon $endDate)
    {
        $rounds = static::where('game_id', $gameId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('round_status', 'completed');

        return [
            'total_rounds' => $rounds->count(),
            'total_bet' => $rounds->sum('bet_amount'),
            'total_win' => $rounds->sum('win_amount'),
            'rtp' => static::calculateRtpForPeriod($startDate, $endDate, $gameId),
            'avg_bet' => $rounds->avg('bet_amount'),
            'max_win' => $rounds->max('win_amount'),
            'winning_rounds' => $rounds->where('win_amount', '>', 0)->count(),
            'bonus_rounds' => $rounds->where('is_bonus_round', true)->count(),
        ];
    }

    // Validation
    public function validateRoundData(): array
    {
        $errors = [];

        if ($this->balance_after !== ($this->balance_before - $this->bet_amount + $this->win_amount)) {
            $errors[] = 'Balance calculation mismatch';
        }

        if ($this->net_result !== ($this->win_amount - $this->bet_amount)) {
            $errors[] = 'Net result calculation mismatch';
        }

        if ($this->bet_amount <= 0) {
            $errors[] = 'Invalid bet amount';
        }

        if ($this->win_amount < 0) {
            $errors[] = 'Invalid win amount';
        }

        if (empty($this->reel_result)) {
            $errors[] = 'Missing reel result';
        }

        return $errors;
    }
}

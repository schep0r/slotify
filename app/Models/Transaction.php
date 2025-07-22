<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'game_session_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'spin_result',
        'reference_id',
        'description',
        'metadata',
        'status',
        'processed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'spin_result' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Transaction types
     */
    public const TYPE_BET = 'bet';
    public const TYPE_WIN = 'win';
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Transaction statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all valid transaction types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_BET,
            self::TYPE_WIN,
            self::TYPE_DEPOSIT,
            self::TYPE_WITHDRAWAL,
            self::TYPE_BONUS,
            self::TYPE_REFUND,
            self::TYPE_ADJUSTMENT
        ];
    }

    /**
     * Get all valid transaction statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED
        ];
    }

    /**
     * Relationship: Transaction belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Transaction belongs to a game session
     */
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * Relationship: Get the game through the session
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Scope: Filter by transaction type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by transaction status
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by amount range
     */
    public function scopeAmountRange(Builder $query, float $minAmount, float $maxAmount): Builder
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    /**
     * Scope: Get completed transactions only
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Get game-related transactions (bets and wins)
     */
    public function scopeGameTransactions(Builder $query): Builder
    {
        return $query->whereIn('type', [self::TYPE_BET, self::TYPE_WIN]);
    }

    /**
     * Scope: Get financial transactions (deposits, withdrawals)
     */
    public function scopeFinancialTransactions(Builder $query): Builder
    {
        return $query->whereIn('type', [self::TYPE_DEPOSIT, self::TYPE_WITHDRAWAL]);
    }

    /**
     * Scope: Get large transactions (configurable amount)
     */
    public function scopeLargeTransactions(Builder $query, float $threshold = 1000): Builder
    {
        return $query->where('amount', '>', $threshold);
    }

    /**
     * Scope: Get suspicious transactions (multiple criteria)
     */
    public function scopeSuspicious(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('amount', '>', 10000) // Large amounts
            ->orWhere(function ($query) {
                // Rapid transactions
                $query->where('created_at', '>', Carbon::now()->subMinutes(5))
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(*) > 10');
            });
        });
    }

    /**
     * Accessor: Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Accessor: Check if transaction is positive (credit)
     */
    public function getIsPositiveAttribute(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Accessor: Check if transaction is negative (debit)
     */
    public function getIsNegativeAttribute(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Accessor: Get transaction type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_BET => 'Bet',
            self::TYPE_WIN => 'Win',
            self::TYPE_DEPOSIT => 'Deposit',
            self::TYPE_WITHDRAWAL => 'Withdrawal',
            self::TYPE_BONUS => 'Bonus',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => 'Unknown'
        };
    }

    /**
     * Accessor: Get status label with color
     */
    public function getStatusLabelAttribute(): array
    {
        return match($this->status) {
            self::STATUS_PENDING => ['label' => 'Pending', 'color' => 'yellow'],
            self::STATUS_COMPLETED => ['label' => 'Completed', 'color' => 'green'],
            self::STATUS_FAILED => ['label' => 'Failed', 'color' => 'red'],
            self::STATUS_CANCELLED => ['label' => 'Cancelled', 'color' => 'gray'],
            default => ['label' => 'Unknown', 'color' => 'gray']
        };
    }

    /**
     * Static method: Create a bet transaction
     */
    public static function createBet(
        int $userId,
        int $gameSessionId,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        array $spinResult = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'game_session_id' => $gameSessionId,
            'type' => self::TYPE_BET,
            'amount' => -abs($amount), // Bets are negative
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'spin_result' => $spinResult,
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'description' => 'Game bet'
        ]);
    }

    /**
     * Static method: Create a win transaction
     */
    public static function createWin(
        int $userId,
        int $gameSessionId,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        array $spinResult = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'game_session_id' => $gameSessionId,
            'type' => self::TYPE_WIN,
            'amount' => abs($amount), // Wins are positive
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'spin_result' => $spinResult,
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'description' => 'Game win'
        ]);
    }

    /**
     * Static method: Create a deposit transaction
     */
    public static function createDeposit(
        int $userId,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        string $referenceId = null,
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_DEPOSIT,
            'amount' => abs($amount), // Deposits are positive
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_id' => $referenceId,
            'metadata' => $metadata,
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'description' => 'Account deposit'
        ]);
    }

    /**
     * Static method: Create a withdrawal transaction
     */
    public static function createWithdrawal(
        int $userId,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        string $referenceId = null,
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_WITHDRAWAL,
            'amount' => -abs($amount), // Withdrawals are negative
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_id' => $referenceId,
            'metadata' => $metadata,
            'status' => self::STATUS_PENDING, // Withdrawals start as pending
            'description' => 'Account withdrawal'
        ]);
    }

    /**
     * Method: Mark transaction as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now()
        ]);
    }

    /**
     * Method: Mark transaction as failed
     */
    public function markAsFailed(string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'metadata' => array_merge($this->metadata ?? [], ['failure_reason' => $reason])
        ]);
    }

    /**
     * Method: Calculate balance change
     */
    public function getBalanceChange(): float
    {
        return $this->balance_after - $this->balance_before;
    }

    /**
     * Method: Check if transaction affects balance
     */
    public function affectsBalance(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Static method: Get user's transaction summary
     */
    public static function getUserSummary(int $userId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = self::where('user_id', $userId)->completed();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_bets' => $transactions->where('type', self::TYPE_BET)->sum('amount'),
            'total_wins' => $transactions->where('type', self::TYPE_WIN)->sum('amount'),
            'total_deposits' => $transactions->where('type', self::TYPE_DEPOSIT)->sum('amount'),
            'total_withdrawals' => $transactions->where('type', self::TYPE_WITHDRAWAL)->sum('amount'),
            'net_gaming' => $transactions->whereIn('type', [self::TYPE_BET, self::TYPE_WIN])->sum('amount'),
            'largest_win' => $transactions->where('type', self::TYPE_WIN)->max('amount') ?? 0,
            'largest_bet' => abs($transactions->where('type', self::TYPE_BET)->min('amount')) ?? 0,
        ];
    }

    /**
     * Static method: Get system-wide statistics
     */
    public static function getSystemStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = self::completed();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total_volume' => $query->sum(DB::raw('ABS(amount)')),
            'total_bets' => $query->ofType(self::TYPE_BET)->sum(DB::raw('ABS(amount)')),
            'total_wins' => $query->ofType(self::TYPE_WIN)->sum('amount'),
            'house_edge' => $query->gameTransactions()->sum('amount'), // Negative means house profit
            'total_deposits' => $query->ofType(self::TYPE_DEPOSIT)->sum('amount'),
            'total_withdrawals' => $query->ofType(self::TYPE_WITHDRAWAL)->sum(DB::raw('ABS(amount)')),
            'active_users' => $query->distinct('user_id')->count(),
        ];
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate reference ID for certain transaction types
        static::creating(function ($transaction) {
            if (in_array($transaction->type, [self::TYPE_DEPOSIT, self::TYPE_WITHDRAWAL]) && !$transaction->reference_id) {
                $transaction->reference_id = 'TXN-' . strtoupper(uniqid());
            }
        });

        // Log transaction creation
        static::created(function ($transaction) {
            Log::info('Transaction created', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount
            ]);
        });
    }
}

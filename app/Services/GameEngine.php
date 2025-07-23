<?php

namespace App\Services;

use App\Models\GameSession;
use App\Models\SlotConfiguration;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidBetException;
use App\Models\User;

/**
 * GameEngine.php - Main game engine for slot machine operations
 */
class GameEngine
{
    private RandomNumberGenerator $rng;
    private PayoutCalculator $payoutCalculator;
    private array $reels;
    private SlotConfiguration $config;

    public function __construct(
        RandomNumberGenerator $rng,
        PayoutCalculator      $payoutCalculator
    )
    {
        $this->rng = $rng;
        $this->payoutCalculator = $payoutCalculator;
//        $this->loadConfiguration();
    }

    /**
     * Execute a spin with the given bet amount
     */
    public function spin(float $betAmount, int $userId, array $activePaylines = null): array
    {
        $this->validateBet($betAmount);
        $user = $this->getUser($userId);

        if ($user->balance < $betAmount) {
            throw new InsufficientBalanceException('Insufficient balance for this bet');
        }

        // Generate random positions for each reel
        $reelPositions = $this->generateReelPositions();

        // Get the symbols that are visible on the reels
        $visibleSymbols = $this->getVisibleSymbols($reelPositions);

        // Calculate payouts
        $payoutResult = $this->payoutCalculator->calculatePayout(
            $visibleSymbols,
            $betAmount,
            $activePaylines ?? $this->config->default_paylines
        );

        // Update user balance
        $newBalance = $user->balance - $betAmount + $payoutResult['totalPayout'];
        $user->update(['balance' => $newBalance]);

        // Log the game session
        $this->logGameSession($userId, $betAmount, $payoutResult, $reelPositions);

        return [
            'reelPositions' => $reelPositions,
            'visibleSymbols' => $visibleSymbols,
            'winningLines' => $payoutResult['winningLines'],
            'totalPayout' => $payoutResult['totalPayout'],
            'newBalance' => $newBalance,
            'isJackpot' => $payoutResult['isJackpot'] ?? false,
            'multiplier' => $payoutResult['multiplier'] ?? 1,
            'freeSpinsAwarded' => $payoutResult['freeSpinsAwarded'] ?? 0
        ];
    }

    /**
     * Generate random positions for each reel
     */
    private function generateReelPositions(): array
    {
        $positions = [];
        foreach ($this->reels as $reelIndex => $reel) {
            $positions[] = $this->rng->generateReelPosition(count($reel));
        }
        return $positions;
    }

    /**
     * Get visible symbols based on reel positions
     */
    private function getVisibleSymbols(array $positions): array
    {
        $visible = [];
        $rows = $this->config->rows;

        foreach ($positions as $reelIndex => $position) {
            $reel = $this->reels[$reelIndex];
            $reelSymbols = [];

            for ($row = 0; $row < $rows; $row++) {
                $symbolIndex = ($position + $row) % count($reel);
                $reelSymbols[] = $reel[$symbolIndex];
            }

            $visible[] = $reelSymbols;
        }

        return $visible;
    }

    /**
     * Load slot machine configuration
     */
    private function loadConfiguration(): void
    {
        $this->config = SlotConfiguration::first();

        // Default 5-reel configuration with various symbols
        $this->reels = [
            // Reel 1
            ['cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'seven', 'cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'wild', 'scatter'],
            // Reel 2
            ['lemon', 'orange', 'plum', 'bell', 'bar', 'seven', 'cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'wild', 'scatter', 'bonus'],
            // Reel 3
            ['orange', 'plum', 'bell', 'bar', 'seven', 'cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'seven', 'wild', 'scatter', 'jackpot'],
            // Reel 4
            ['plum', 'bell', 'bar', 'seven', 'cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'seven', 'wild', 'scatter', 'bonus', 'cherry'],
            // Reel 5
            ['bell', 'bar', 'seven', 'cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'seven', 'wild', 'scatter', 'jackpot', 'bonus', 'lemon']
        ];
    }

    private function validateBet(float $betAmount): void
    {
        if ($betAmount < $this->config->min_bet || $betAmount > $this->config->max_bet) {
            throw new InvalidBetException("Bet must be between {$this->config->min_bet} and {$this->config->max_bet}");
        }
    }

    private function getUser(int $userId)
    {
        return User::findOrFail($userId);
    }

    private function logGameSession(int $userId, float $bet, array $payout, array $positions): void
    {
        GameSession::create([
            'user_id' => $userId,
            'bet_amount' => $bet,
            'payout_amount' => $payout['totalPayout'],
            'reel_positions' => json_encode($positions),
            'winning_lines' => json_encode($payout['winningLines']),
            'is_jackpot' => $payout['isJackpot'] ?? false,
            'played_at' => now()
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BetValidatorInterface;
use App\Contracts\GameLoggerInterface;
use App\Contracts\PayoutCalculatorInterface;
use App\Contracts\ReelGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\Models\Game;
use App\Models\User;

/**
 * GameEngine - Orchestrates the main game flow following SOLID principles
 *
 * Single Responsibility: Coordinates game spin workflow
 * Open/Closed: Extensible through dependency injection
 * Liskov Substitution: Uses interfaces for all dependencies
 * Interface Segregation: Each dependency has focused interface
 * Dependency Inversion: Depends on abstractions, not concretions
 */
class GameEngine
{
    public function __construct(
        private BetValidatorInterface $betValidator,
        private ReelGeneratorInterface $reelGenerator,
        private PayoutCalculatorInterface $payoutCalculator,
        private TransactionManagerInterface $transactionManager,
        private GameLoggerInterface $gameLogger,
        private GameSessionService $gameSessionService
    ) {}

    /**
     * Execute a spin with the given bet amount
     *
     * Main orchestration method that coordinates all game steps
     */
    public function spin(float $betAmount, int $userId, Game $game, ?array $activePaylines = null): array
    {
        // Step 1: Validate bet and user
        $user = $this->getUser($userId);
        $this->betValidator->validate($game, $user, $betAmount);

        // Step 2: Get or create game session
        $gameSession = $this->gameSessionService->getOrCreateUserSession($user, $game);

        // Step 3: Generate reel results
        $spinResult = $this->reelGenerator->getVisibleSymbols($game);
        $visibleSymbols = $spinResult->symbols;

        // Step 4: Calculate payouts
        $payoutResult = $this->payoutCalculator->calculatePayout(
            $game,
            $visibleSymbols,
            $betAmount,
            $activePaylines ?? [0]
        );

        // Step 5: Process transactions
        $newBalance = $this->transactionManager->processSpinTransaction(
            $user,
            $gameSession,
            $betAmount,
            $payoutResult
        );

        // Step 6: Log game round
        $this->gameLogger->logGameRound($gameSession, $payoutResult, $betAmount, $visibleSymbols);

        // Step 7: Return game result
        return $this->buildGameResult($spinResult->positions, $visibleSymbols, $payoutResult, $newBalance);
    }

    /**
     * Get user by ID
     */
    private function getUser(int $userId): User
    {
        return User::findOrFail($userId);
    }

    /**
     * Build the final game result array
     */
    private function buildGameResult(
        array $reelPositions,
        array $visibleSymbols,
        array $payoutResult,
        float $newBalance
    ): array {
        return [
            'reelPositions' => $reelPositions,
            'visibleSymbols' => $visibleSymbols,
            'winningLines' => $payoutResult['winningLines'],
            'totalPayout' => $payoutResult['totalPayout'],
            'newBalance' => $newBalance,
            'isJackpot' => $payoutResult['isJackpot'] ?? false,
            'multiplier' => $payoutResult['multiplier'] ?? 1,
            'freeSpinsAwarded' => $payoutResult['freeSpinsAwarded'] ?? 0,
            'scatterResult' => $payoutResult['scatterResult'] ?? [],
            'wildPositions' => $payoutResult['wildPositions'] ?? []
        ];
    }
}

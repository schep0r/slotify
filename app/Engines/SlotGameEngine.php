<?php

declare(strict_types=1);

namespace App\Engines;

use App\Contracts\BetValidatorInterface;
use App\Contracts\GameEngineInterface;
use App\Contracts\GameLoggerInterface;
use App\Contracts\PayoutCalculatorInterface;
use App\Contracts\ReelGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\Enums\GameType;
use App\Managers\GameSessionManager;
use App\Models\Game;
use App\Models\User;
use InvalidArgumentException;

/**
 * GameEngine - Orchestrates the main game flow following SOLID principles
 *
 * Single Responsibility: Coordinates game spin workflow
 * Open/Closed: Extensible through dependency injection
 * Liskov Substitution: Uses interfaces for all dependencies
 * Interface Segregation: Each dependency has focused interface
 * Dependency Inversion: Depends on abstractions, not concretions
 */
class SlotGameEngine implements GameEngineInterface
{
    public function __construct(
        private BetValidatorInterface $betValidator,
        private ReelGeneratorInterface $reelGenerator,
        private PayoutCalculatorInterface $payoutCalculator,
        private TransactionManagerInterface $transactionManager,
        private GameLoggerInterface $gameLogger,
        private GameSessionManager $gameSessionManager
    ) {}

    /**
     * Execute a spin with the given bet amount
     *
     * Main orchestration method that coordinates all game steps
     */
    public function play(User $user, Game $game, array $gameData): array
    {
        $betAmount = $gameData['betAmount'];

        // Step 1: Validate bet and user
        $this->betValidator->validate($game, $user, $betAmount);

        // Step 2: Get or create game session
        $gameSession = $this->gameSessionManager->getOrCreateUserSession($user, $game);

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
            'gameType' => $this->getGameType(),
            'betAmount' => $payoutResult['betAmount'],
            'winAmount' => $payoutResult['totalPayout'],
            'newBalance' => $newBalance,
            'gameData' => [
                'betAmount' => $payoutResult['betAmount'],
                'winAmount' => $payoutResult['totalPayout'],
                'gameData' => [
                    'reelPositions' => $reelPositions,
                    'visibleSymbols' => $visibleSymbols,
                    'winningLines' => $payoutResult['winningLines'],
                    'isJackpot' => $payoutResult['isJackpot'] ?? false,
                    'multiplier' => $payoutResult['multiplier'] ?? 1,
                    'freeSpinsAwarded' => $payoutResult['freeSpinsAwarded'] ?? 0,
                    'scatterResult' => $payoutResult['scatterResult'] ?? [],
                    'wildPositions' => $payoutResult['wildPositions'] ?? []
                ]
            ],
        ];
    }

    public function getGameType(): string
    {
        return GameType::SLOT->value;
    }

    public function validateInput(array $gameData, Game $game, User $user): void
    {
        $activePaylines = $gameData['activePaylines'] ?? [0];
        $maxPaylines = count($game->paylinesConfiguration->value ?? []);

        foreach ($activePaylines as $payline) {
            if ($payline >= $maxPaylines) {
                throw new InvalidArgumentException("Invalid payline: {$payline}");
            }
        }
    }

    public function getRequiredInputs(): array
    {
        return [
            'betAmount' => 'required|numeric|min:0.01',
            'activePaylines' => 'array|nullable',
            'useFreeSpins' => 'boolean|nullable',
        ];
    }

    public function getConfigurationRequirements(): array
    {
        return [
            'reels' => 'required|array',
            'rows' => 'required|integer|min:1',
            'paylines' => 'required|array',
            'paytable' => 'required|array',
            'rtp' => 'required|numeric|between:80,99',
        ];
    }
}

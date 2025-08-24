<?php

declare(strict_types=1);

namespace App\Engines;

use App\Contracts\BetValidatorInterface;
use App\Contracts\GameEngineInterface;
use App\Contracts\GameLoggerInterface;
use App\Contracts\PayoutCalculatorInterface;
use App\Contracts\ReelGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\DTOs\GameResultDto;
use App\DTOs\SlotGameDataDto;
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
        private readonly BetValidatorInterface $betValidator,
        private readonly ReelGeneratorInterface $reelGenerator,
        private readonly PayoutCalculatorInterface $payoutCalculator,
        private readonly TransactionManagerInterface $transactionManager,
        private readonly GameLoggerInterface $gameLogger,
        private readonly GameSessionManager $gameSessionManager
    ) {}

    /**
     * Execute a spin with the given bet amount
     *
     * Main orchestration method that coordinates all game steps
     */
    public function play(User $user, Game $game, array $gameData): GameResultDto
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
        $newBalance = $this->transactionManager->processGameTransaction(
            $user,
            $gameSession,
            $betAmount,
            $payoutResult['totalPayout']
        );

        // Step 6: Log game round
        $this->gameLogger->logGameRound($gameSession, $payoutResult, $betAmount, $visibleSymbols);

        // Step 7: Return game result
        return $this->buildGameResult($spinResult->positions, $visibleSymbols, $payoutResult, $newBalance);
    }

    /**
     * Build the final game result DTO
     */
    private function buildGameResult(
        array $reelPositions,
        array $visibleSymbols,
        array $payoutResult,
        float $newBalance
    ): GameResultDto {
        $slotGameData = new SlotGameDataDto(
            betAmount: $payoutResult['betAmount'],
            winAmount: $payoutResult['totalPayout'],
            reelPositions: $reelPositions,
            visibleSymbols: $visibleSymbols,
            winningLines: $payoutResult['winningLines'],
            isJackpot: $payoutResult['isJackpot'] ?? false,
            multiplier: $payoutResult['multiplier'] ?? 1.0,
            freeSpinsAwarded: $payoutResult['freeSpinsAwarded'] ?? 0,
            scatterResult: $payoutResult['scatterResult'] ?? [],
            wildPositions: $payoutResult['wildPositions'] ?? []
        );

        return new GameResultDto(
            gameType: $this->getGameType(),
            betAmount: $payoutResult['betAmount'],
            winAmount: $payoutResult['totalPayout'],
            newBalance: $newBalance,
            gameData: $slotGameData
        );
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

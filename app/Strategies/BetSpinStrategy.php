<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Contracts\BetValidatorInterface;
use App\Contracts\GameLoggerInterface;
use App\Contracts\PayoutCalculatorInterface;
use App\Contracts\ReelGeneratorInterface;
use App\Contracts\SpinStrategyInterface;
use App\Contracts\TransactionManagerInterface;
use App\DTOs\GameResultDto;
use App\DTOs\SlotGameDataDto;
use App\Enums\GameType;
use App\Managers\GameSessionManager;
use App\Models\Game;
use App\Models\User;

/**
 * Strategy for handling regular bet spins
 */
class BetSpinStrategy implements SpinStrategyInterface
{
    public function __construct(
        private readonly BetValidatorInterface $betValidator,
        private readonly ReelGeneratorInterface $reelGenerator,
        private readonly PayoutCalculatorInterface $payoutCalculator,
        private readonly TransactionManagerInterface $transactionManager,
        private readonly GameLoggerInterface $gameLogger,
        private readonly GameSessionManager $gameSessionManager
    ) {}

    public function execute(User $user, Game $game, array $gameData): GameResultDto
    {
        $betAmount = $gameData['betAmount'];
        $activePaylines = $gameData['activePaylines'] ?? [0];

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
            $activePaylines
        );

        // Step 5: Process transactions (deduct bet, add winnings)
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

    public function canHandle(array $gameData): bool
    {
        return !($gameData['useFreeSpins'] ?? false) && isset($gameData['betAmount']);
    }

    public function getRequiredInputs(): array
    {
        return [
            'betAmount' => 'required|numeric|min:0.01',
            'activePaylines' => 'array|nullable',
        ];
    }

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
            gameType: GameType::SLOT->value,
            betAmount: $payoutResult['betAmount'],
            winAmount: $payoutResult['totalPayout'],
            newBalance: $newBalance,
            gameData: $slotGameData
        );
    }
}
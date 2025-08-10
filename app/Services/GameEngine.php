<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameSession;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidBetException;
use App\Models\Transaction;
use App\Models\User;

/**
 * GameEngine.php - Main game engine for slot machine operations
 */
class GameEngine
{
    private RandomNumberGenerator $rng;
    private PayoutCalculator $payoutCalculator;
    private GameRoundService $gameRoundService;
    private GameSessionService $gameSessionService;

    public function __construct(
        RandomNumberGenerator $rng,
        PayoutCalculator $payoutCalculator,
        GameRoundService $gameRoundService,
        GameSessionService $gameSessionService
    )
    {
        $this->rng = $rng;
        $this->payoutCalculator = $payoutCalculator;
        $this->gameRoundService = $gameRoundService;
        $this->gameSessionService = $gameSessionService;
    }

    /**
     * Execute a spin with the given bet amount
     */
    public function spin(float $betAmount, int $userId, Game $game): array
    {

        $this->validateBet($betAmount, $game);
        $user = $this->getUser($userId);
        $gameSession = $this->gameSessionService->getOrCreateUserSession($user, $game);

        if ($user->balance < $betAmount) {
            throw new InsufficientBalanceException('Insufficient balance for this bet');
        }

        // Generate random positions for each reel
        $reelPositions = $this->generateReelPositions($game);

        // Get the symbols that are visible on the reels
        $visibleSymbols = $this->getVisibleSymbols($reelPositions, $game);

        // Calculate payouts
        $payoutResult = $this->payoutCalculator->calculatePayout(
            $game,
            $visibleSymbols,
            $betAmount,
            [0]
        );

        // Update user balance
        $newBalance = $user->balance - $betAmount + $payoutResult['totalPayout'];

        if ($payoutResult['totalPayout'] > 0) {
            Transaction::createWin($user->id, $gameSession->id, $payoutResult['totalPayout'], $user->balance, $newBalance, $payoutResult);
        } else {
            Transaction::createBet($user->id, $gameSession->id, $betAmount, $user->balance, $newBalance, $payoutResult);
        }

        $user->update(['balance' => $newBalance]);
        // Log the game session
        $this->logGameRound($gameSession, $payoutResult, $betAmount, $visibleSymbols);

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
    private function generateReelPositions(Game $game): array
    {
        $positions = [];
        foreach ($game->reelsConfiguration->value as $reelIndex => $reel) {
            $positions[] = $this->rng->generateReelPosition(count($reel));
        }

        return $positions;
    }

    /**
     * Get visible symbols based on reel positions
     */
    private function getVisibleSymbols(array $positions, Game $game): array
    {
        $visible = [];
        $reels = $game->reelsConfiguration->value;
        $rows = $game->rowsConfiguration->value;

        foreach ($positions as $reelIndex => $position) {
            $reel = $reels[$reelIndex];
            $reelSymbols = [];

            for ($row = 0; $row < $rows; $row++) {
                $symbolIndex = ($position + $row) % count($reel);
                $reelSymbols[] = $reel[$symbolIndex];
            }

            $visible[] = $reelSymbols;
        }

        return $visible;
    }

    private function validateBet(float $betAmount, Game $game): void
    {
        if ($betAmount < $game->min_bet || $betAmount > $game->max_bet) {
            throw new InvalidBetException("Bet must be between {$game->min_bet} and {$game->max_bet}");
        }
    }

    private function getUser(int $userId)
    {
        return User::findOrFail($userId);
    }

    private function logGameRound(GameSession $gameSession, array $spinData, float $betAmount, array $visibleSymbols): void
    {
        $spinData = array_merge(
            $spinData,
            [
                'bet_amount' => $betAmount,
                'win_amount' => $spinData['totalPayout'],
                'reel_result' => $visibleSymbols,
            ]
        );

        $this->gameRoundService->processSpin(
            $gameSession,
            $spinData,
        );
    }
}

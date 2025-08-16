<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GameLoggerInterface;
use App\Models\GameSession;

class GameLogger implements GameLoggerInterface
{
    public function __construct(
        private GameRoundService $gameRoundService
    ) {}

    public function logGameRound(
        GameSession $gameSession,
        array $spinData,
        float $betAmount,
        array $visibleSymbols
    ): void {
        $spinData = array_merge(
            $spinData,
            [
                'bet_amount' => $betAmount,
                'win_amount' => $spinData['totalPayout'],
                'reel_result' => $visibleSymbols,
            ]
        );

        $this->gameRoundService->processSpin($gameSession, $spinData);
    }
}
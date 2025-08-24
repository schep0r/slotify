<?php

declare(strict_types=1);

namespace App\Engines;

use App\Contracts\RandomNumberGeneratorInterface;
use App\Enums\GameType;
use App\Models\Game;
use App\Models\User;
use App\Services\Games\Roulette\RoulettePayoutCalculator;
use App\Services\Games\Roulette\RouletteWheelGenerator;

class RouletteGameEngine
{
    public function __construct(
        \App\Contracts\BetValidatorInterface $betValidator,
        \App\Contracts\TransactionManagerInterface $transactionManager,
        \App\Contracts\GameLoggerInterface $gameLogger,
        \App\Managers\GameSessionManager $gameSessionManager,
        private RandomNumberGeneratorInterface $rng,
        private RoulettePayoutCalculator $payoutCalculator,
        private RouletteWheelGenerator $wheelGenerator
    ) {
        parent::__construct($betValidator, $transactionManager, $gameLogger, $gameSessionManager);
    }

    public function getGameType(): string
    {
        return GameType::ROULETTE->value;
    }

    public function getRequiredInputs(): array
    {
        return [
            'bets' => 'required|array|min:1',
            'bets.*.type' => 'required|string|in:straight,split,street,corner,line,dozen,column,red,black,odd,even,low,high',
            'bets.*.amount' => 'required|numeric|min:0.01',
            'bets.*.numbers' => 'array|nullable'
        ];
    }

    public function getConfigurationRequirements(): array
    {
        return [
            'wheel_type' => 'required|string|in:european,american',
            'min_bet' => 'required|numeric|min:0.01',
            'max_bet' => 'required|numeric',
            'table_limits' => 'required|array'
        ];
    }

    public function validateInput(array $gameData, Game $game, User $user): void
    {
        $totalBetAmount = collect($gameData['bets'])->sum('amount');

        // Validate total bet amount
        $this->betValidator->validate($game, $user, $totalBetAmount);

        // Validate individual bets
        foreach ($gameData['bets'] as $bet) {
            $this->validateBet($bet, $game);
        }
    }

    protected function executeGameLogic(array $gameData, Game $game, User $user): array
    {
        $bets = $gameData['bets'];
        $totalBetAmount = collect($bets)->sum('amount');

        // Spin the wheel
        $winningNumber = $this->wheelGenerator->spin($game);

        // Calculate payouts for all bets
        $totalWinAmount = 0;
        $betResults = [];

        foreach ($bets as $bet) {
            $payout = $this->payoutCalculator->calculatePayout($bet, $winningNumber, $game);
            $totalWinAmount += $payout;

            $betResults[] = [
                'type' => $bet['type'],
                'amount' => $bet['amount'],
                'numbers' => $bet['numbers'] ?? [],
                'payout' => $payout,
                'won' => $payout > 0
            ];
        }

        return [
            'betAmount' => $totalBetAmount,
            'winAmount' => $totalWinAmount,
            'gameData' => [
                'winningNumber' => $winningNumber,
                'bets' => $betResults,
                'wheelType' => $this->getGameConfiguration($game)->wheel_type
            ]
        ];
    }

    protected function buildGameResult(array $gameResult, float $newBalance): array
    {
        return [
            'gameType' => $this->getGameType(),
            'betAmount' => $gameResult['betAmount'],
            'winAmount' => $gameResult['winAmount'],
            'newBalance' => $newBalance,
            'gameData' => $gameResult['gameData']
        ];
    }

    private function validateBet(array $bet, Game $game): void
    {
        $config = $this->getGameConfiguration($game);

        // Validate bet amount against table limits
        $betType = $bet['type'];
        $betAmount = $bet['amount'];

        if (isset($config->table_limits[$betType])) {
            $limits = $config->table_limits[$betType];

            if ($betAmount < $limits['min'] || $betAmount > $limits['max']) {
                throw new \InvalidArgumentException(
                    "Bet amount {$betAmount} is outside limits for {$betType} ({$limits['min']}-{$limits['max']})"
                );
            }
        }

        // Validate numbers for specific bet types
        if (in_array($betType, ['straight', 'split', 'street', 'corner', 'line']) && empty($bet['numbers'])) {
            throw new \InvalidArgumentException("Numbers are required for {$betType} bet");
        }
    }
}

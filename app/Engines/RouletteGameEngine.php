<?php

declare(strict_types=1);

namespace App\Engines;

use App\Contracts\BetValidatorInterface;
use App\Contracts\GameEngineInterface;
use App\Contracts\GameLoggerInterface;
use App\Contracts\RandomNumberGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\DTOs\GameResultDto;
use App\DTOs\RouletteBetResultDto;
use App\DTOs\RouletteGameDataDto;
use App\Enums\GameType;
use App\Managers\GameSessionManager;
use App\Models\Game;
use App\Models\User;
use App\Services\Games\Roulette\RoulettePayoutCalculator;
use App\Services\Games\Roulette\RouletteWheelGenerator;
use InvalidArgumentException;

class RouletteGameEngine implements GameEngineInterface
{
    public function __construct(
        private BetValidatorInterface $betValidator,
        private TransactionManagerInterface $transactionManager,
        private GameLoggerInterface $gameLogger,
        private GameSessionManager $gameSessionManager,
        private RandomNumberGeneratorInterface $rng,
        private RoulettePayoutCalculator $payoutCalculator,
        private RouletteWheelGenerator $wheelGenerator
    ) {
    }

    /**
     * Execute a roulette game round
     */
    public function play(User $user, Game $game, array $gameData): GameResultDto
    {
        // Step 1: Validate input and bets
        $this->validateInput($gameData, $game, $user);
        
        $bets = $gameData['bets'];
        $totalBetAmount = collect($bets)->sum('amount');

        // Step 2: Validate total bet amount
        $this->betValidator->validate($game, $user, $totalBetAmount);

        // Step 3: Get or create game session
        $gameSession = $this->gameSessionManager->getOrCreateUserSession($user, $game);

        // Step 4: Spin the wheel
        $winningNumber = $this->wheelGenerator->spin($game);

        // Step 5: Calculate payouts for all bets
        $totalWinAmount = 0;
        $betResults = [];

        foreach ($bets as $bet) {
            $payout = $this->payoutCalculator->calculatePayout($bet, $winningNumber, $game);
            $totalWinAmount += $payout;

            $betResults[] = new RouletteBetResultDto(
                type: $bet['type'],
                amount: $bet['amount'],
                numbers: $bet['numbers'] ?? [],
                payout: $payout,
                won: $payout > 0
            );
        }

        // Step 6: Process transactions
        $newBalance = $this->transactionManager->processSpinTransaction(
            $user,
            $gameSession,
            $totalBetAmount,
            ['totalPayout' => $totalWinAmount, 'betAmount' => $totalBetAmount]
        );

        // Step 7: Log game round
        $this->gameLogger->logGameRound($gameSession, ['totalPayout' => $totalWinAmount], $totalBetAmount, []);

        // Step 8: Return game result
        return $this->buildGameResult($totalBetAmount, $totalWinAmount, $winningNumber, $betResults, $game, $newBalance);
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

    /**
     * Build the final game result DTO
     */
    private function buildGameResult(
        float $totalBetAmount,
        float $totalWinAmount,
        int $winningNumber,
        array $betResults,
        Game $game,
        float $newBalance
    ): GameResultDto {
        $config = $this->getGameConfiguration($game);
        
        $rouletteGameData = new RouletteGameDataDto(
            winningNumber: $winningNumber,
            bets: $betResults,
            wheelType: $config->wheel_type ?? 'european'
        );

        return new GameResultDto(
            gameType: $this->getGameType(),
            betAmount: $totalBetAmount,
            winAmount: $totalWinAmount,
            newBalance: $newBalance,
            gameData: $rouletteGameData
        );
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
                throw new InvalidArgumentException(
                    "Bet amount {$betAmount} is outside limits for {$betType} ({$limits['min']}-{$limits['max']})"
                );
            }
        }

        // Validate numbers for specific bet types
        if (in_array($betType, ['straight', 'split', 'street', 'corner', 'line']) && empty($bet['numbers'])) {
            throw new InvalidArgumentException("Numbers are required for {$betType} bet");
        }
    }

    private function getGameConfiguration(Game $game)
    {
        return $game->rouletteConfiguration;
    }
}
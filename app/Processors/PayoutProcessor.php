<?php

declare(strict_types=1);

namespace App\Processors;

use App\Contracts\PayoutCalculatorInterface;
use App\Models\Game;
use App\Services\WildResultService;
use App\Services\ScatterResultService;
use App\Processors\JackpotProcessor;

class PayoutProcessor implements PayoutCalculatorInterface
{
    private array $paytable = [];
    private array $paylines = [];
    private WildResultService $wildResultService;
    private ScatterResultService $scatterResultService;
    private JackpotProcessor $jackpotProcessor;

    public function __construct(
        WildResultService $wildResultService,
        ScatterResultService $scatterResultService,
        JackpotProcessor $jackpotProcessor
    ) {
        $this->wildResultService = $wildResultService;
        $this->scatterResultService = $scatterResultService;
        $this->jackpotProcessor = $jackpotProcessor;
    }

    /**
     * Calculate total payout for a spin result
     */
    public function calculatePayout(
        Game $game,
        array $visibleSymbols,
        float $betAmount,
        array $activePaylines
    ): array {
        $winningLines = [];
        $totalPayout = 0;
        $isJackpot = false;
        $freeSpinsAwarded = 0;
        $this->initConfigurations($game);

        // Check each active payline
        foreach ($activePaylines as $paylineIndex) {
            if (!isset($this->paylines[$paylineIndex])) continue;

            $payline = $this->paylines[$paylineIndex];
            $lineResult = $this->checkPayline($visibleSymbols, $payline, $betAmount);

            if ($lineResult['payout'] > 0) {
                $winningLines[] = [
                    'payline' => $paylineIndex,
                    'symbols' => $lineResult['symbols'],
                    'count' => $lineResult['count'],
                    'payout' => $lineResult['payout'],
                    'symbol' => $lineResult['winningSymbol']
                ];

                $totalPayout += $lineResult['payout'];
            }
        }

        // Check for scatter bonuses using dedicated service
        $scatterResult = $this->scatterResultService->checkScatterBonus($game, $visibleSymbols, $betAmount);
        if ($scatterResult['payout'] > 0) {
            $totalPayout += $scatterResult['payout'];
            $freeSpinsAwarded = $scatterResult['freeSpins'];
        }

        // Apply wild multipliers using dedicated service
//        $multiplier = $this->wildResultService->calculateWildMultiplier($visibleSymbols);
//        $totalPayout *= $multiplier;

        // Progressive jackpot check via dedicated processor
        if ($this->jackpotProcessor->isProgressiveJackpot($visibleSymbols)) {
            $jackpotAmount = $this->jackpotProcessor->getProgressiveJackpotAmount();
            $totalPayout += $jackpotAmount;
            $isJackpot = true;
        }

        return [
            'winningLines' => $winningLines,
            'totalPayout' => round($totalPayout, 2),
            'isJackpot' => $isJackpot,
//            'multiplier' => $multiplier,
            'freeSpinsAwarded' => $freeSpinsAwarded,
            'scatterResult' => $scatterResult,
            'wildPositions' => $this->wildResultService->getWildPositions($visibleSymbols)
        ];
    }

    /**
     * Check a single payline for winning combinations
     */
    private function checkPayline(
        array $visibleSymbols,
        array $payline,
        float $betAmount
    ): array {
        $symbols = [];

        // Extract symbols along the payline
        foreach ($payline as $reelIndex => $row) {
            $symbols[] = $visibleSymbols[$reelIndex][$row];
        }

        $winningSymbol = null;
        $count = 0;
        $payout = 0;

        // Check for winning combinations (left to right)
        $firstSymbol = $symbols[0];
        if ($firstSymbol === WildResultService::SYMBOL_WILD) {
            // Handle wild as first symbol using wild service
            $firstSymbol = $this->wildResultService->findBestWildSubstitute($symbols);
        }

        $consecutiveCount = 1;
        for ($i = 1; $i < count($symbols); $i++) {
            $currentSymbol = $symbols[$i];

            if ($currentSymbol === $firstSymbol || $currentSymbol === WildResultService::SYMBOL_WILD) {
                $consecutiveCount++;
            } else {
                break;
            }
        }

        // Check if we have a winning combination
        if ($consecutiveCount >= 3 && isset($this->paytable[$firstSymbol][$consecutiveCount])) {
            $winningSymbol = $firstSymbol;
            $count = $consecutiveCount;
            $basePayout = $this->paytable[$firstSymbol][$consecutiveCount];

            // Calculate wild contribution for this payline
            $wildContribution = $this->wildResultService->calculateWildContribution(
                $symbols,
                $this->paytable,
                $betAmount / count($this->paylines)
            );

            $payout = $basePayout * ($betAmount / count($this->paylines)) * $wildContribution['multiplier'];
        }

        return [
            'symbols' => $symbols,
            'winningSymbol' => $winningSymbol,
            'count' => $count,
            'payout' => $payout
        ];
    }


    private function initConfigurations(Game $game): void
    {
        $this->paylines = $game->paylinesConfiguration->value;
        $this->paytable = $game->paytableConfiguration->value;
    }
}

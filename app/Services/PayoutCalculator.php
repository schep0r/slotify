<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SlotConfiguration;
use Illuminate\Support\Collection;

class PayoutCalculator
{
    private array $paytable;
    private array $paylines;
    private RandomNumberGenerator $rng;

    public function __construct(RandomNumberGenerator $rng)
    {
        $this->rng = $rng;
        $this->initializePaytable();
        $this->initializePaylines();
    }

    /**
     * Calculate total payout for a spin result
     */
    public function calculatePayout(array $visibleSymbols, float $betAmount, array $activePaylines): array
    {
        $winningLines = [];
        $totalPayout = 0;
        $isJackpot = false;
        $multiplier = 1;
        $freeSpinsAwarded = 0;

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

                if ($lineResult['winningSymbol'] === 'jackpot' && $lineResult['count'] >= 3) {
                    $isJackpot = true;
                }
            }
        }

        // Check for scatter bonuses
        $scatterResult = $this->checkScatterBonus($visibleSymbols, $betAmount);
        if ($scatterResult['payout'] > 0) {
            $totalPayout += $scatterResult['payout'];
            $freeSpinsAwarded = $scatterResult['freeSpins'];
        }

        // Apply wild multipliers
        $multiplier = $this->calculateWildMultiplier($visibleSymbols);
        $totalPayout *= $multiplier;

        // Progressive jackpot check
        if ($this->checkProgressiveJackpot($visibleSymbols)) {
            $jackpotAmount = $this->getProgressiveJackpotAmount();
            $totalPayout += $jackpotAmount;
            $isJackpot = true;
        }

        return [
            'winningLines' => $winningLines,
            'totalPayout' => round($totalPayout, 2),
            'isJackpot' => $isJackpot,
            'multiplier' => $multiplier,
            'freeSpinsAwarded' => $freeSpinsAwarded
        ];
    }

    /**
     * Check a single payline for winning combinations
     */
    private function checkPayline(array $visibleSymbols, array $payline, float $betAmount): array
    {
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
        if ($firstSymbol === 'wild') {
            // Handle wild as first symbol
            $firstSymbol = $this->findBestWildSubstitute($symbols);
        }

        $consecutiveCount = 1;
        for ($i = 1; $i < count($symbols); $i++) {
            $currentSymbol = $symbols[$i];

            if ($currentSymbol === $firstSymbol || $currentSymbol === 'wild') {
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
            $payout = $basePayout * ($betAmount / count($this->paylines));
        }

        return [
            'symbols' => $symbols,
            'winningSymbol' => $winningSymbol,
            'count' => $count,
            'payout' => $payout
        ];
    }

    /**
     * Check for scatter symbol bonuses
     */
    private function checkScatterBonus(array $visibleSymbols, float $betAmount): array
    {
        $scatterCount = 0;

        // Count scatter symbols across all reels
        foreach ($visibleSymbols as $reel) {
            foreach ($reel as $symbol) {
                if ($symbol === 'scatter') {
                    $scatterCount++;
                }
            }
        }

        $payout = 0;
        $freeSpins = 0;

        if ($scatterCount >= 3) {
            // Scatter payouts are usually multiplied by total bet
            $scatterMultiplier = [3 => 2, 4 => 10, 5 => 100];
            $payout = ($scatterMultiplier[$scatterCount] ?? 0) * $betAmount;

            // Award free spins
            $freeSpinAwards = [3 => 10, 4 => 15, 5 => 25];
            $freeSpins = $freeSpinAwards[$scatterCount] ?? 0;
        }

        return [
            'payout' => $payout,
            'freeSpins' => $freeSpins,
            'scatterCount' => $scatterCount
        ];
    }

    /**
     * Calculate multiplier from wild symbols
     */
    private function calculateWildMultiplier(array $visibleSymbols): int
    {
        $wildCount = 0;
        foreach ($visibleSymbols as $reel) {
            foreach ($reel as $symbol) {
                if ($symbol === 'wild') {
                    $wildCount++;
                }
            }
        }

        // Each wild symbol adds 1x multiplier
        return 1 + $wildCount;
    }

    /**
     * Check for progressive jackpot win
     */
    private function checkProgressiveJackpot(array $visibleSymbols): bool
    {
        // Very rare jackpot combination (5 jackpot symbols on center line)
        $centerLine = [];
        foreach ($visibleSymbols as $reel) {
            $centerLine[] = $reel[1]; // Middle row
        }

        return count(array_filter($centerLine, fn($symbol) => $symbol === 'jackpot')) === 5;
    }

    private function getProgressiveJackpotAmount(): float
    {
        // This would typically be stored in database and incremented with each bet
        return 10000.00; // Example jackpot amount
    }

    private function findBestWildSubstitute(array $symbols): string
    {
        // Find the most frequent non-wild symbol
        $symbolCounts = array_count_values(array_filter($symbols, fn($s) => $s !== 'wild'));
        return $symbolCounts ? array_search(max($symbolCounts), $symbolCounts) : 'cherry';
    }

    private function initializePaytable(): void
    {
        $this->paytable = [
            'cherry' => [3 => 5, 4 => 20, 5 => 100],
            'lemon' => [3 => 5, 4 => 20, 5 => 100],
            'orange' => [3 => 10, 4 => 30, 5 => 150],
            'plum' => [3 => 10, 4 => 30, 5 => 150],
            'bell' => [3 => 15, 4 => 50, 5 => 200],
            'bar' => [3 => 25, 4 => 75, 5 => 300],
            'seven' => [3 => 50, 4 => 150, 5 => 500],
            'wild' => [3 => 100, 4 => 400, 5 => 1000],
            'jackpot' => [3 => 500, 4 => 2000, 5 => 5000],
            'bonus' => [3 => 25, 4 => 100, 5 => 400]
        ];
    }

    private function initializePaylines(): void
    {
        // Standard 25-payline configuration (5x3 grid)
        $this->paylines = [
            // Horizontal lines
            [0, 0, 0, 0, 0], // Top row
            [1, 1, 1, 1, 1], // Middle row
            [2, 2, 2, 2, 2], // Bottom row

            // Diagonal lines
            [0, 1, 2, 1, 0], // V shape
            [2, 1, 0, 1, 2], // Inverted V

            // Zigzag patterns
            [0, 0, 1, 2, 2],
            [2, 2, 1, 0, 0],
            [1, 0, 0, 0, 1],
            [1, 2, 2, 2, 1],
            [0, 1, 0, 1, 0],
            [2, 1, 2, 1, 2],
            [1, 0, 1, 2, 1],
            [1, 2, 1, 0, 1],
            [0, 0, 2, 0, 0],
            [2, 2, 0, 2, 2],
            [0, 2, 2, 2, 0],
            [2, 0, 0, 0, 2],
            [1, 1, 0, 1, 1],
            [1, 1, 2, 1, 1],
            [0, 1, 1, 1, 0],
            [2, 1, 1, 1, 2],
            [0, 2, 0, 2, 0],
            [2, 0, 2, 0, 2],
            [1, 0, 2, 0, 1],
            [1, 2, 0, 2, 1]
        ];
    }
}

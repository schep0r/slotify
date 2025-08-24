<?php

declare(strict_types=1);

namespace App\Services\Games\Roulette;

use App\Models\Game;

class RoulettePayoutCalculator
{
    private const PAYOUTS = [
        'straight' => 35,    // Single number
        'split' => 17,       // Two numbers
        'street' => 11,      // Three numbers (row)
        'corner' => 8,       // Four numbers
        'line' => 5,         // Six numbers (two rows)
        'dozen' => 2,        // 12 numbers
        'column' => 2,       // 12 numbers
        'red' => 1,          // 18 numbers
        'black' => 1,        // 18 numbers
        'odd' => 1,          // 18 numbers
        'even' => 1,         // 18 numbers
        'low' => 1,          // 1-18
        'high' => 1,         // 19-36
    ];

    private const RED_NUMBERS = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

    public function calculatePayout(array $bet, int $winningNumber, Game $game): float
    {
        $betType = $bet['type'];
        $betAmount = $bet['amount'];
        $numbers = $bet['numbers'] ?? [];

        if (!$this->isBetWinning($bet, $winningNumber)) {
            return 0;
        }

        $multiplier = self::PAYOUTS[$betType] ?? 0;
        return $betAmount * $multiplier;
    }

    private function isBetWinning(array $bet, int $winningNumber): bool
    {
        $betType = $bet['type'];
        $numbers = $bet['numbers'] ?? [];

        return match ($betType) {
            'straight' => in_array($winningNumber, $numbers),
            'split' => in_array($winningNumber, $numbers),
            'street' => in_array($winningNumber, $numbers),
            'corner' => in_array($winningNumber, $numbers),
            'line' => in_array($winningNumber, $numbers),
            'dozen' => $this->isDozenWin($winningNumber, $numbers[0] ?? 1),
            'column' => $this->isColumnWin($winningNumber, $numbers[0] ?? 1),
            'red' => $winningNumber > 0 && in_array($winningNumber, self::RED_NUMBERS),
            'black' => $winningNumber > 0 && !in_array($winningNumber, self::RED_NUMBERS),
            'odd' => $winningNumber > 0 && $winningNumber % 2 === 1,
            'even' => $winningNumber > 0 && $winningNumber % 2 === 0,
            'low' => $winningNumber >= 1 && $winningNumber <= 18,
            'high' => $winningNumber >= 19 && $winningNumber <= 36,
            default => false
        };
    }

    private function isDozenWin(int $winningNumber, int $dozen): bool
    {
        if ($winningNumber === 0) return false;

        return match ($dozen) {
            1 => $winningNumber >= 1 && $winningNumber <= 12,
            2 => $winningNumber >= 13 && $winningNumber <= 24,
            3 => $winningNumber >= 25 && $winningNumber <= 36,
            default => false
        };
    }

    private function isColumnWin(int $winningNumber, int $column): bool
    {
        if ($winningNumber === 0) return false;

        return match ($column) {
            1 => $winningNumber % 3 === 1,
            2 => $winningNumber % 3 === 2,
            3 => $winningNumber % 3 === 0,
            default => false
        };
    }
}
<?php

declare(strict_types=1);

namespace App\Processors\Slot;

class JackpotProcessor
{
    /**
     * Determine if the current visible symbols hit the progressive jackpot
     */
    public function isProgressiveJackpot(array $visibleSymbols): bool
    {
        // Very rare jackpot combination (5 jackpot symbols on center line)
        $centerLine = [];
        foreach ($visibleSymbols as $reel) {
            $centerLine[] = $reel[3]; // Middle row
        }

        return count(array_filter($centerLine, fn($symbol) => $symbol === 'jackpot')) === 5;
    }

    /**
     * Get the current progressive jackpot amount
     * In real implementation, this could be retrieved from DB or cache
     */
    public function getProgressiveJackpotAmount(): float
    {
        // This would typically be stored in database and incremented with each bet
        return 10000.00; // Example jackpot amount
    }
}

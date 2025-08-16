<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use App\Models\GameSession;

interface TransactionManagerInterface
{
    /**
     * Process bet and win transactions
     */
    public function processSpinTransaction(
        User $user,
        GameSession $gameSession,
        float $betAmount,
        array $payoutResult
    ): float;
}
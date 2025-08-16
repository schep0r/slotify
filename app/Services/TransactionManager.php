<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TransactionManagerInterface;
use App\Models\GameSession;
use App\Models\Transaction;
use App\Models\User;

class TransactionManager implements TransactionManagerInterface
{
    public function processSpinTransaction(
        User $user,
        GameSession $gameSession,
        float $betAmount,
        array $payoutResult
    ): float {
        $newBalance = $user->balance - $betAmount + $payoutResult['totalPayout'];

        if ($payoutResult['totalPayout'] > 0) {
            Transaction::createWin(
                $user->id,
                $gameSession->id,
                $payoutResult['totalPayout'],
                $user->balance,
                $newBalance,
                $payoutResult
            );
        } else {
            Transaction::createBet(
                $user->id,
                $gameSession->id,
                $betAmount,
                $user->balance,
                $newBalance,
                $payoutResult
            );
        }

        $user->update(['balance' => $newBalance]);

        return $newBalance;
    }
}
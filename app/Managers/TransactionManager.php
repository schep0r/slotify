<?php

declare(strict_types=1);

namespace App\Managers;

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

    public function processGameTransaction(
        User $user,
        GameSession $gameSession,
        float $betAmount,
        float $winAmount
    ): float {
        $newBalance = $user->balance - $betAmount + $winAmount;

        // Create bet transaction
        if ($betAmount > 0) {
            Transaction::createBet(
                $user->id,
                $gameSession->id,
                $betAmount,
                $user->balance,
                $user->balance - $betAmount,
                ['game_type' => $gameSession->game->type->value]
            );
        }

        // Create win transaction if there's a win
        if ($winAmount > 0) {
            Transaction::createWin(
                $user->id,
                $gameSession->id,
                $winAmount,
                $user->balance - $betAmount,
                $newBalance,
                ['game_type' => $gameSession->game->type->value]
            );
        }

        $user->update(['balance' => $newBalance]);

        return $newBalance;
    }
}
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BetValidatorInterface;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidBetException;
use App\Models\Game;
use App\Models\User;

class BetValidator implements BetValidatorInterface
{
    public function validate(Game $game, User $user, float $betAmount): void
    {
        $this->validateBet($betAmount, $game);
        $this->validateBalance($user, $betAmount);
    }

    public function validateBet(float $betAmount, Game $game): void
    {
        if ($betAmount < $game->min_bet || $betAmount > $game->max_bet) {
            throw new InvalidBetException("Bet must be between {$game->min_bet} and {$game->max_bet}");
        }
    }

    public function validateBalance(User $user, float $betAmount): void
    {
        if ($user->balance < $betAmount) {
            throw new InsufficientBalanceException('Insufficient balance for this bet');
        }
    }
}

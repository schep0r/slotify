<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Game;
use App\Models\User;

interface BetValidatorInterface
{
    /**
     * Run all validating
     */
    public function validate(Game $game, User $user, float $betAmount): void;

    /**
     * Validate bet amount against game limits
     */
    public function validateBet(float $betAmount, Game $game): void;

    /**
     * Validate user has sufficient balance
     */
    public function validateBalance(User $user, float $betAmount): void;
}

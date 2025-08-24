<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Requests\PlayGameRequest;
use App\Models\Game;
use App\Models\User;

interface GameProcessorInterface
{
    public function process(Game $game, User $user, PlayGameRequest $playGameRequest): array;
}

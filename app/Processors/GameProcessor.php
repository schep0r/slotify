<?php

declare(strict_types=1);

namespace App\Processors;

use App\Contracts\GameProcessorInterface;
use App\Engines\SlotGameEngine;
use App\Http\Requests\PlayGameRequest;
use App\Models\Game;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

readonly class GameProcessor implements GameProcessorInterface
{
    public function __construct(
        private SlotGameEngine $gameEngine
    ) {}

    public function process(Game $game, User $user, PlayGameRequest $playGameRequest): array
    {
        $gameData = $playGameRequest->collect()->toArray();

        try {
            DB::beginTransaction();
            $result = $this->gameEngine->play($user, $game, $gameData);

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();

        // Convert DTO to array for API response
        return $result->toArray();
    }
}

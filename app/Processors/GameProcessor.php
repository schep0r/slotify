<?php

declare(strict_types=1);

namespace App\Processors;

use App\Contracts\GameProcessorInterface;
use App\Factories\GameEngineFactory;
use App\Http\Requests\PlayGameRequest;
use App\Models\Game;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

readonly class GameProcessor implements GameProcessorInterface
{
    public function __construct(
        private GameEngineFactory $gameEngineFactory
    ) {}

    public function process(Game $game, User $user, PlayGameRequest $playGameRequest): array
    {
        $gameData = $playGameRequest->collect()->toArray();
        $gameEngine = $this->gameEngineFactory->createForGame($game);

        try {
            DB::beginTransaction();
            $result = $gameEngine->play($user, $game, $gameData);

        } catch (Exception $exception) {
            $result = [];
            DB::rollBack();
            throw $exception;
        }

        DB::commit();

        return $result;
    }
}

<?php

namespace App\Http\Controllers;

use App\Contracts\GameProcessorInterface;
use App\Http\Requests\PlayGameRequest;
use App\Models\Game;

class UniversalGameController extends Controller
{
    private GameProcessorInterface $gameProcessor;

    public function __construct(GameProcessorInterface $gameProcessor)
    {
        $this->gameProcessor = $gameProcessor;
    }

    /**
     * Get game information
     */
    public function info(Game $game)
    {
        return response()->json([
            'game' => $game,
            'configuration' => $game->getGameConfiguration(),
        ]);
    }

    /**
     * Play a game round using the universal game processor
     */
    public function play(PlayGameRequest $request, Game $game)
    {
        $user = auth()->user();

        $result = $this->gameProcessor->process($game, $user, $request);

        return response()->json($result);
    }
}

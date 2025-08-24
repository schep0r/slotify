<?php

namespace App\Http\Controllers;

use App\Contracts\GameProcessorInterface;
use App\Enums\GameType;
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
     * Get all available game types
     */
    public function gameTypes()
    {
        return response()->json([
            'gameTypes' => array_map(fn($type) => $type->value, GameType::cases())
        ]);
    }

    /**
     * Get games by type
     */
    public function gamesByType(string $gameType)
    {
        $games = Game::where('type', $gameType)
            ->where('is_active', true)
            ->get();

        return response()->json(['games' => $games]);
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
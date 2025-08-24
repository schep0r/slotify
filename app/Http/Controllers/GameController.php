<?php

namespace App\Http\Controllers;

use App\Contracts\GameProcessorInterface;
use App\Http\Requests\PlayGameRequest;
use App\Models\Game;

class GameController extends Controller
{
    private GameProcessorInterface $gameProcessor;

    public function __construct(GameProcessorInterface $gameProcessor)
    {
        $this->gameProcessor = $gameProcessor;
    }

    public function index()
    {
        return Game::all();
    }

    public function show(Game $game)
    {
        return $game;
    }

    public function config(Game $game)
    {
        return $game;
    }

    public function paytable(Game $game)
    {
        return $game;
    }

    public function play(PlayGameRequest $request, Game $game)
    {
        $user = auth()->user();

        return $this->gameProcessor->process($game, $user, $request);
    }
}

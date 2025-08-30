<?php

namespace App\Http\Controllers;

use App\Contracts\GameEngineInterface;
use App\Http\Requests\PlayGameRequest;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function __construct(
        private readonly GameEngineInterface $gameEngine,
    ) {}

    public function index()
    {
        return Game::all();
    }

    public function show(Game $game)
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

        return $this->gameEngine->play($user, $game, $request->toArray());
    }

    public function settings(Game $game): JsonResponse
    {
        $paytable = $game->paytableConfiguration->value;

        $result = [];
        foreach ($paytable as $symbol => $combinations) {
            foreach ($combinations as $count => $amount) {
                $result[] = [
                    'combo' => implode(' ', array_fill(0, $count, $symbol)),
                    'payout' => $amount,
                ];
            }
        }

        usort($result, function($a, $b) {
            return $a['payout'] <=> $b['payout'];
        });

        return response()->json([
            'success' => true,
            'payouts' => $result,
        ]);
    }
}

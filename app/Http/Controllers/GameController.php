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

        $result = $this->gameEngine->play($user, $game, $request->toArray());

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    public function settings(Game $game): JsonResponse
    {
        $paytable = $game->paytableConfiguration->value;
        $reels = $game->reelsConfiguration->value;
        $rows = $game->rowsConfiguration->value;

        $paytableConverted = [];
        $visibleSymbols = [];

        foreach ($paytable as $symbol => $combinations) {
            foreach ($combinations as $count => $amount) {
                $paytableConverted[] = [
                    'combo' => implode(' ', array_fill(0, $count, $symbol)),
                    'payout' => $amount . 'x',
                ];
            }
        }

        foreach ($reels as $reel) {
            $visibleSymbols[] = array_slice($reel, rand(0, count($reel) - $rows), $rows);
        }

        usort($paytableConverted, function($a, $b) {
            return $b['payout'] <=> $a['payout'];
        });

        return response()->json([
            'success' => true,
            'payouts' => $paytableConverted,
            'visible_symbols' => $visibleSymbols
        ]);
    }
}

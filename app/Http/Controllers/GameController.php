<?php

namespace App\Http\Controllers;

use App\Services\GameEngine;
use App\Http\Requests\SpinRequest;
use App\Models\GameSession;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    private GameEngine $gameEngine;

    public function __construct(GameEngine $gameEngine)
    {
        $this->gameEngine = $gameEngine;
    }

    public function spin(SpinRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = auth()->user();
            $gameId = $request->game_id;
            $betAmount = $request->bet_amount;

            // Validate balance
            if ($user->balance < $betAmount) {
                return response()->json(['error' => 'Insufficient balance'], 400);
            }

            // Create or get game session
            $session = GameSession::firstOrCreate([
                'user_id' => $user->id,
                'game_id' => $gameId,
                'session_token' => $request->session_token
            ]);

            // Deduct bet amount
            $user->decrement('balance', $betAmount);

            // Record bet transaction
            Transaction::create([
                'user_id' => $user->id,
                'game_session_id' => $session->id,
                'type' => 'bet',
                'amount' => -$betAmount,
                'balance_before' => $user->balance + $betAmount,
                'balance_after' => $user->balance
            ]);

            // Generate spin result
            $result = $this->gameEngine->spin($gameId, $betAmount);

            // Process winnings
            if ($result['total_win'] > 0) {
                $user->increment('balance', $result['total_win']);

                Transaction::create([
                    'user_id' => $user->id,
                    'game_session_id' => $session->id,
                    'type' => 'win',
                    'amount' => $result['total_win'],
                    'balance_before' => $user->balance - $result['total_win'],
                    'balance_after' => $user->balance,
                    'spin_result' => $result
                ]);
            }

            // Update session stats
            $session->increment('total_spins');
            $session->increment('total_bet', $betAmount);
            $session->increment('total_win', $result['total_win']);

            return response()->json([
                'result' => $result,
                'balance' => $user->fresh()->balance,
                'session_stats' => $session->fresh()
            ]);
        });
    }
}

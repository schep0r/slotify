<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Str;

class GameSessionService
{
    public function getOrCreateUserSession(User $user, Game $game)
    {
        $activeSession = GameSession::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->where('status', 'active')
            ->first()
        ;

        if ($activeSession) {
            return $activeSession;
        }

        return $this->startGameSession($user, $game->id);
    }

    public function startGameSession(User $user, int $gameId)
    {
        return GameSession::create([
            'user_id' => $user->id,
            'game_id' => $gameId,
            'session_token' => Str::uuid(),
            'started_at' => now(),
            'status' => 'active',
            'total_spins' => 0,
            'total_bet' => 0,
            'total_win' => 0,
        ]);
    }
}

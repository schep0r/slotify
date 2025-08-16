<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Str;

class GameSessionService
{
    public const  SESSION_LIFETIME = 86400;

    public function getOrCreateUserSession(User $user, Game $game)
    {
        $activeSession = GameSession::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->where('status', GameSession::STATUS_ACTIVE)
            ->first()
        ;

        if (!$activeSession) {
            return $this->startGameSession($user, $game->id);
        }

        $lastUpdated = $activeSession->updated_at;
        if ($lastUpdated && $lastUpdated->lt(now()->subSeconds(self::SESSION_LIFETIME))) {
            $activeSession->status = GameSession::STATUS_CLOSED;
            $activeSession->save();

            return $this->startGameSession($user, $game->id);
        }

        return $activeSession;
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

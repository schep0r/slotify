<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\GameSession;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GameSessionMiddleware
{
    /**
     * Handle an incoming request.
     * This middleware ensures that game sessions are properly managed,
     * validated, and secured before allowing game operations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to game-related routes
        if (!$this->isGameRoute($request)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Authentication required',
                'code' => 'AUTH_REQUIRED'
            ], 401);
        }

        $user = Auth::user();
        $sessionToken = $request->input('session_token') ?? $request->header('X-Session-Token');
        $gameId = $request->input('game_id') ?? $request->route('gameId');

        // For starting a new session, we don't need to validate existing session
        if ($request->is('api/*/game/start')) {
            return $this->handleNewSession($request, $next, $user, $gameId);
        }

        // Validate session token is provided
        if (!$sessionToken) {
            return response()->json([
                'error' => 'Session token required',
                'code' => 'SESSION_TOKEN_REQUIRED'
            ], 400);
        }

        // Validate and get the game session
        $gameSession = $this->validateGameSession($sessionToken, $user->id, $gameId);

        if (!$gameSession) {
            return response()->json([
                'error' => 'Invalid or expired game session',
                'code' => 'INVALID_SESSION'
            ], 400);
        }

        // Check if session has expired
        if ($this->isSessionExpired($gameSession)) {
            $this->endSession($gameSession);
            return response()->json([
                'error' => 'Game session has expired',
                'code' => 'SESSION_EXPIRED'
            ], 400);
        }

        // Check if game is still active
        if (!$gameSession->game->is_active) {
            return response()->json([
                'error' => 'Game is currently unavailable',
                'code' => 'GAME_UNAVAILABLE'
            ], 503);
        }

        // Update session activity
        $this->updateSessionActivity($gameSession);

        // Add session to request for use in controllers
        $request->attributes->set('game_session', $gameSession);
        $request->attributes->set('game', $gameSession->game);

        // Continue with the request
        $response = $next($request);

        // Log session activity after request completion
        $this->logSessionActivity($gameSession, $request, $response);

        return $response;
    }

    /**
     * Check if the current route is a game-related route
     */
    private function isGameRoute(Request $request): bool
    {
        $gameRoutes = [
            'api/*/game/start',
            'api/*/game/spin',
            'api/*/game/end',
            'api/*/game/autoplay/*',
            'api/*/game/session/*'
        ];

        foreach ($gameRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle new session creation
     */
    private function handleNewSession(Request $request, Closure $next, $user, $gameId)
    {
        // Validate game exists and is active
        $game = Game::where('id', $gameId)->where('is_active', true)->first();

        if (!$game) {
            return response()->json([
                'error' => 'Game not found or inactive',
                'code' => 'GAME_NOT_FOUND'
            ], 404);
        }

        // Check if user has sufficient balance for minimum bet
        if ($user->balance < $game->min_bet) {
            return response()->json([
                'error' => 'Insufficient balance to start game',
                'code' => 'INSUFFICIENT_BALANCE',
                'required' => $game->min_bet,
                'available' => $user->balance
            ], 400);
        }

        // Check for existing active sessions (limit concurrent sessions)
        $activeSessions = GameSession::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where('updated_at', '>', Carbon::now()->subMinutes(30))
            ->count();

        if ($activeSessions >= 3) { // Limit to 3 concurrent sessions
            return response()->json([
                'error' => 'Maximum concurrent sessions reached',
                'code' => 'MAX_SESSIONS_REACHED',
                'limit' => 3
            ], 400);
        }

        // Add game to request for controller use
        $request->attributes->set('game', $game);

        return $next($request);
    }

    /**
     * Validate game session
     */
    private function validateGameSession(string $sessionToken, int $userId, int $gameId): ?GameSession
    {
        // Try to get from cache first for performance
        $cacheKey = "game_session:{$sessionToken}";
        $gameSession = Cache::get($cacheKey);

        if (!$gameSession) {
            // Get from database
            $gameSession = GameSession::with('game')
                ->where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->whereNull('ended_at')
                ->first();

            if ($gameSession) {
                // Cache for 5 minutes
                Cache::put($cacheKey, $gameSession, 300);
            }
        }

        // Validate session belongs to correct game
        if ($gameSession && $gameSession->game_id != $gameId) {
            return null;
        }

        return $gameSession;
    }

    /**
     * Check if session has expired
     */
    private function isSessionExpired(GameSession $gameSession): bool
    {
        $sessionTimeout = config('game.session_timeout', 1800); // 30 minutes default
        $lastActivity = $gameSession->updated_at;

        return Carbon::now()->diffInSeconds($lastActivity) > $sessionTimeout;
    }

    /**
     * Update session activity timestamp
     */
    private function updateSessionActivity(GameSession $gameSession): void
    {
        $gameSession->touch();

        // Update cache
        $cacheKey = "game_session:{$gameSession->session_token}";
        Cache::put($cacheKey, $gameSession, 300);
    }

    /**
     * End expired session
     */
    private function endSession(GameSession $gameSession): void
    {
        $gameSession->update([
            'ended_at' => Carbon::now(),
            'end_reason' => 'expired'
        ]);

        // Remove from cache
        $cacheKey = "game_session:{$gameSession->session_token}";
        Cache::forget($cacheKey);
    }

    /**
     * Log session activity for audit and analytics
     */
    private function logSessionActivity(GameSession $gameSession, Request $request, $response): void
    {
        // Only log if logging is enabled
        if (!config('game.log_session_activity', true)) {
            return;
        }

        $logData = [
            'session_id' => $gameSession->id,
            'user_id' => $gameSession->user_id,
            'game_id' => $gameSession->game_id,
            'action' => $this->getActionFromRequest($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $this->sanitizeRequestData($request),
            'response_code' => $response->getStatusCode(),
            'timestamp' => Carbon::now()
        ];

        // Log to database or external service
        \Log::channel('game_activity')->info('Session Activity', $logData);
    }

    /**
     * Get action name from request
     */
    private function getActionFromRequest(Request $request): string
    {
        $uri = $request->getRequestUri();

        if (str_contains($uri, '/spin')) {
            return 'spin';
        } elseif (str_contains($uri, '/autoplay')) {
            return 'autoplay';
        } elseif (str_contains($uri, '/end')) {
            return 'end_session';
        }

        return 'unknown';
    }

    /**
     * Sanitize request data for logging (remove sensitive info)
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();

        // Remove sensitive fields
        $sensitiveFields = ['password', 'token', 'api_key'];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}

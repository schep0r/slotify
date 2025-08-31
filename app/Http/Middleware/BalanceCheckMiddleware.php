<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceCheckMiddleware
{
    /**
     * Ensure user has sufficient balance for game actions
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check balance for game-related endpoints that require betting
        if (!$this->requiresBalanceCheck($request)) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'error' => 'Authentication required',
                'code' => 'UNAUTHENTICATED'
            ], 401);
        }

        $betAmount = $this->extractBetAmount($request);

        if ($betAmount > 0 && $user->balance < $betAmount) {
            return response()->json([
                'error' => 'Insufficient balance',
                'code' => 'INSUFFICIENT_BALANCE',
                'required' => $betAmount,
                'available' => $user->balance
            ], 400);
        }

        return $next($request);
    }

    /**
     * Determine if the request requires balance checking
     */
    private function requiresBalanceCheck(Request $request): bool
    {
        $patterns = [
            'api/*/game/*/play',
            'api/*/game/spin',
            'api/*/slot/*/spin',
            'api/games/*/play'
        ];

        foreach ($patterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract bet amount from request based on different possible field names
     */
    private function extractBetAmount(Request $request): float
    {
        // Try different possible field names for bet amount
        $possibleFields = ['betAmount', 'bet_amount', 'amount', 'totalBet'];

        foreach ($possibleFields as $field) {
            if ($request->has($field)) {
                return (float) $request->input($field, 0);
            }
        }

        // For multiple bets (like in some slot games), sum them up
        if ($request->has('bets') && is_array($request->input('bets'))) {
            return array_sum(array_column($request->input('bets'), 'amount'));
        }

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class BalanceCheckMiddleware
{
    /**
     * Ensure user has sufficient balance for game actions
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->is('api/*/game/spin')) {
            return $next($request);
        }

        $user = Auth::user();
        $betAmount = $request->input('bet_amount', 0);

        if ($user->balance < $betAmount) {
            return response()->json([
                'error' => 'Insufficient balance',
                'code' => 'INSUFFICIENT_BALANCE',
                'required' => $betAmount,
                'available' => $user->balance
            ], 400);
        }

        return $next($request);
    }
}

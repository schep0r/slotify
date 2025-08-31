<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SpinRateLimitMiddleware
{
    /**
     * Handle spin rate limiting
     */
    public function handle(Request $request, \Closure $next)
    {
        $user = Auth::user();
        $key = "spin_rate_limit:{$user->id}";

        // Allow 1 spin per second
        $attempts = Cache::get($key, 0);

        if ($attempts >= 1) {
            return response()->json([
                'error' => 'Please wait before spinning again',
                'code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => 1
            ], 429);
        }

        Cache::put($key, $attempts + 1, 1);

        return $next($request);
    }
}

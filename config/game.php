<?php

declare(strict_types=1);

return [
    'session_timeout' => env('GAME_SESSION_TIMEOUT', 1800), // 30 minutes
    'max_concurrent_sessions' => env('GAME_MAX_CONCURRENT_SESSIONS', 3),
    'spin_rate_limit' => env('GAME_SPIN_RATE_LIMIT', 1), // spins per second
    'log_session_activity' => env('GAME_LOG_ACTIVITY', true),
    'session_cleanup_interval' => env('GAME_SESSION_CLEANUP_INTERVAL', 300), // 5 minutes
];

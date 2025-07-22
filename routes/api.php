<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GameConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    });

    // Public game information (no auth required)
    Route::prefix('games')->group(function () {
        Route::get('/', [GameConfigController::class, 'index']); // List available games
        Route::get('/{gameId}', [GameConfigController::class, 'show']); // Game details
        Route::get('/{gameId}/config', [GameConfigController::class, 'config']); // Game configuration
        Route::get('/{gameId}/paytable', [GameConfigController::class, 'paytable']); // Payout table
    });

    // Protected API routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Authentication management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/user', [AuthController::class, 'user']);
        });

        // User management
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'profile']);
            Route::put('/profile', [UserController::class, 'updateProfile']);
            Route::get('/balance', [UserController::class, 'balance']);
            Route::get('/stats', [UserController::class, 'stats']);
            Route::post('/deposit', [UserController::class, 'deposit']);
            Route::post('/withdraw', [UserController::class, 'withdraw']);
        });

        // Game session management
        Route::prefix('game')->group(function () {
            Route::post('/start', [GameController::class, 'startSession']);
            Route::post('/end', [GameController::class, 'endSession']);
            Route::get('/session/{sessionToken}', [GameController::class, 'getSession']);

            // Main game actions
            Route::post('/spin', [GameController::class, 'spin']);
            Route::post('/autoplay/start', [GameController::class, 'startAutoplay']);
            Route::post('/autoplay/stop', [GameController::class, 'stopAutoplay']);
            Route::get('/history', [GameController::class, 'gameHistory']);
        });

        // Transaction management
        Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionController::class, 'index']);
            Route::get('/{transactionId}', [TransactionController::class, 'show']);
            Route::get('/session/{sessionId}', [TransactionController::class, 'bySession']);
            Route::get('/export', [TransactionController::class, 'export']);
        });

        // Game statistics and reporting
        Route::prefix('stats')->group(function () {
            Route::get('/summary', [UserController::class, 'gamingSummary']);
            Route::get('/session/{sessionId}', [UserController::class, 'sessionStats']);
            Route::get('/daily', [UserController::class, 'dailyStats']);
            Route::get('/monthly', [UserController::class, 'monthlyStats']);
        });

        // Admin routes (if user is admin)
        Route::middleware(['admin'])->prefix('admin')->group(function () {
            // Game management
            Route::apiResource('games', GameConfigController::class);
            Route::post('/games/{gameId}/activate', [GameConfigController::class, 'activate']);
            Route::post('/games/{gameId}/deactivate', [GameConfigController::class, 'deactivate']);

            // User management
            Route::get('/users', [UserController::class, 'adminIndex']);
            Route::get('/users/{userId}', [UserController::class, 'adminShow']);
            Route::post('/users/{userId}/balance', [UserController::class, 'adminUpdateBalance']);
            Route::post('/users/{userId}/suspend', [UserController::class, 'suspend']);
            Route::post('/users/{userId}/unsuspend', [UserController::class, 'unsuspend']);

            // System statistics
            Route::get('/stats/system', [TransactionController::class, 'systemStats']);
            Route::get('/stats/games', [TransactionController::class, 'gameStats']);
            Route::get('/stats/users', [TransactionController::class, 'userStats']);

            // Transaction monitoring
            Route::get('/transactions/suspicious', [TransactionController::class, 'suspicious']);
            Route::get('/transactions/large', [TransactionController::class, 'largeTransactions']);
        });
    });
});

Route::middleware(['auth:sanctum', 'game.session', 'balance.check', 'spin.rate.limit'])
    ->post('/game/spin', [GameController::class, 'spin']);

Route::middleware(['auth:sanctum', 'game.session'])
    ->group(function () {
        Route::post('/game/start', [GameController::class, 'startSession']);
        Route::post('/game/end', [GameController::class, 'endSession']);
        Route::post('/game/autoplay/start', [GameController::class, 'startAutoplay']);
    });

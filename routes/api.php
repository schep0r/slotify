<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;

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
        Route::get('/', [GameController::class, 'index']); // List available games
        Route::get('/{game}', [GameController::class, 'show']); // Game details
        Route::get('/{game}/settings', [GameController::class, 'settings']); // Game settings
    });

    // Protected API routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Authentication management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
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
            // Session management (no balance check needed)
            Route::post('/start', [GameController::class, 'startSession']);
            Route::post('/end', [GameController::class, 'endSession']);
            Route::get('/session/{sessionToken}', [GameController::class, 'getSession']);
            Route::get('/history', [GameController::class, 'gameHistory']);

            // Game actions that require balance check
            Route::middleware(['balance.check', 'spin.rate.limit'])->group(function () {
                Route::post('/spin', [GameController::class, 'spin']);
                Route::post('/{game}/play', [\App\Http\Controllers\UniversalGameController::class, 'play']);
            });

            // Autoplay management (balance checked per spin, not on start/stop)
            Route::post('/autoplay/start', [GameController::class, 'startAutoplay']);
            Route::post('/autoplay/stop', [GameController::class, 'stopAutoplay']);
        });
    });
});



Route::middleware('auth:api')->group(function () {
    Route::prefix('free-spins')->group(function () {
        Route::get('/available', [\App\Http\Controllers\FreeSpinController::class, 'getAvailableSpins']);
        Route::post('/use', [\App\Http\Controllers\FreeSpinController::class, 'useSpin']);
        Route::get('/stats', [\App\Http\Controllers\FreeSpinController::class, 'getStats']);

        // Admin routes
        Route::middleware('admin')->group(function () {
            Route::post('/award', [\App\Http\Controllers\FreeSpinController::class, 'awardSpins']);
        });
    });
});

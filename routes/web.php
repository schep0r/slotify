<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main application routes
//Route::get('/', [WebController::class, 'index'])->name('home');

// Authentication routes (if using Laravel Breeze/UI)
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebController::class, 'login'])->name('login');
    Route::get('/register', [WebController::class, 'register'])->name('register');
});

// Authenticated web routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    Route::get('/game/{gameId}', [WebController::class, 'game'])->name('game.show');
    Route::get('/profile', [WebController::class, 'profile'])->name('profile');
    Route::get('/transactions', [WebController::class, 'transactions'])->name('transactions');
});

// Catch-all route for Vue.js SPA (should be last)
//Route::get('/{any}', [WebController::class, 'app'])->where('any', '.*');


Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

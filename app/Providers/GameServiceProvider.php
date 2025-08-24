<?php

namespace App\Providers;

use App\Engines\RouletteGameEngine;
use App\Factories\GameEngineFactory;
use App\Services\Games\Roulette\RoulettePayoutCalculator;
use App\Services\Games\Roulette\RouletteWheelGenerator;
use App\Services\Games\SlotGameEngine;
use Illuminate\Support\ServiceProvider;

class GameServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the game engine factory
        $this->app->singleton(GameEngineFactory::class);

        // Register game engines
        $this->app->bind(SlotGameEngine::class);
        $this->app->bind(RouletteGameEngine::class);

        // Register roulette-specific services
        $this->app->bind(RoulettePayoutCalculator::class);
        $this->app->bind(RouletteWheelGenerator::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

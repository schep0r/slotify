<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register game-related services
        $this->app->singleton(\App\Services\WildResultService::class);
        $this->app->singleton(\App\Services\ScatterResultService::class);
        $this->app->singleton(\App\Processors\JackpotProcessor::class);

        // Register interfaces with their implementations
        $this->app->bind(
            \App\Contracts\ReelGeneratorInterface::class,
            \App\Services\ReelGenerator::class
        );

        $this->app->bind(
            \App\Contracts\PayoutCalculatorInterface::class,
            \App\Processors\PayoutProcessor::class
        );

        $this->app->bind(
            \App\Contracts\BetValidatorInterface::class,
            \App\Services\BetValidator::class
        );

        $this->app->bind(
            \App\Contracts\TransactionManagerInterface::class,
            \App\Services\TransactionManager::class
        );

        $this->app->bind(
            \App\Contracts\GameLoggerInterface::class,
            \App\Services\GameLogger::class
        );

        // Check if RandomNumberGeneratorInterface binding exists
        if (!$this->app->bound(\App\Contracts\RandomNumberGeneratorInterface::class)) {
            $this->app->bind(
                \App\Contracts\RandomNumberGeneratorInterface::class,
                \App\Services\RandomNumberGenerator::class
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

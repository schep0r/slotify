<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\GameEngine;
use Illuminate\Console\Command;
use Throwable;

class GameSpinConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Accepts:
     *  - gameId (required): The ID of the game to spin
     *  - --bet: Bet amount (default: 1)
     *  - --user: User ID to attribute the spin to (default: 1)
     *
     * Example:
     *  php artisan game:spin 5 --bet=2 --user=1
     *
     * @var string
     */
    protected $signature = 'game:spin {gameId : The ID of the game to spin} {--bet=1 : Bet amount} {--user=1 : User ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spin a specified game and display the result.';

    /**
     * Execute the console command.
     */
    public function handle(GameEngine $gameEngine)
    {
        $gameId = (int) $this->argument('gameId');
        $betAmount = (float) $this->option('bet');
        $userId = (int) $this->option('user');

        if ($gameId <= 0) {
            $this->error('Invalid gameId provided.');
            return Command::FAILURE;
        }

        // Ensure the game exists (basic validation)
        $game = Game::find($gameId);
        if (!$game) {
            $this->error("Game with ID {$gameId} not found.");
            return Command::FAILURE;
        }

        $this->info("Spinning game '{$game->name}' (ID: {$gameId}) with bet {$betAmount} for user {$userId}...");

        $result = null;
        $errors = [];

        // Try different known signatures due to inconsistencies in the codebase
        try {
            // Signature variant 1 (from current GameEngine): spin(float $betAmount, int $userId, array $activePaylines = null)
            $result = $gameEngine->spin($betAmount, $userId, $game);
        } catch (Throwable $e1) {
            $errors[] = $e1->getMessage();
            try {
                // Signature variant 2 (as used by GameController): spin(int $gameId, float $betAmount)
                // Use call_user_func to bypass strict static signature expectations
                $result = call_user_func([$gameEngine, 'spin'], $gameId, $betAmount);
            } catch (Throwable $e2) {
                $errors[] = $e2->getMessage();
            }
        }

        if ($result === null) {
            $this->error('Failed to spin the game using available methods.');
            foreach ($errors as $msg) {
                $this->line(" - " . $msg);
            }
            return Command::FAILURE;
        }

        // Output result nicely
        $this->line(json_encode($result, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}

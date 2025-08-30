<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Requests\PlayGameRequest;
use App\Models\Game;
use App\Models\User;
use App\Processors\GameProcessor;
use Illuminate\Console\Command;
use Throwable;

class GamePlayConsole extends Command
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
     *  php artisan game:play 5 --bet=2 --user=1 --plays-count=10
     *
     * @var string
     */
    protected $signature = 'game:play {gameId : The ID of the game to spin} {--bet=1 : Bet amount} {--user=1 : User ID} {--plays-count=100 : How many plays should be?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Play a specified game and display the result.';

    /**
     * Execute the console command.
     */
    public function handle(GameProcessor $gameProcessor)
    {
        $gameId = (int) $this->argument('gameId');
        $betAmount = (float) $this->option('bet');
        $userId = (int) $this->option('user');
        $spinCount = (int) $this->option('plays-count');

        if ($gameId <= 0) {
            $this->error('Invalid gameId provided.');
            return Command::FAILURE;
        }

        // Ensure the game exists (basic validation)
        $game = Game::find($gameId);
        $user = User::find($userId);

        if (!$game) {
            $this->error("Game with ID {$gameId} not found.");
            return Command::FAILURE;
        }

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        $this->info("Playing game '{$game->name}' (ID: {$gameId}) with bet {$betAmount} for user {$userId}...");

        $playRequest = new PlayGameRequest(['betAmount' => $betAmount]);
        $result = null;
        $errors = [];

        $totalPlays = 0;
        $totalWins = 0;
        $totalBets = 0;

        for ($i = 0; $i < $spinCount; $i++) {
            try {
                $result = $gameProcessor->process($game, $user, $playRequest);

                $totalPlays++;
                $totalWins += $result['winAmount'] ?? 0;
                $totalBets += $betAmount;
            } catch (Throwable $e1) {
                $errors[] = $e1->getMessage();
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
        $this->line(json_encode(
            [
                'total_spins' => $totalPlays,
                'total_wins' => $totalWins,
                'total_bets' => $totalBets,
                'rtp' => $totalWins / $totalBets * 100,
            ],
            JSON_PRETTY_PRINT))
        ;

        if (count($errors) > 0) {
            foreach ($errors as $msg) {
                $this->line(" - " . $msg);
            }
        }

        return Command::SUCCESS;
    }
}

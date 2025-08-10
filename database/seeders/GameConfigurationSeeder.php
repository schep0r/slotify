<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameConfiguration;
use Illuminate\Database\Seeder;

class GameConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For each game, create a few configuration entries
        Game::query()->each(function (Game $game) {
            // Ensure distinct keys for a game to satisfy unique(game_id, key)
            $keys = ['paylines', 'reels', 'rows_count', 'bonus_features'];

            foreach ($keys as $index => $key) {
                // Determine data_type and stored value based on key
                [$dataType, $storedValue] = match ($key) {
                    'bonus_features' => [
                        'json', json_encode([
                            'features' => ['free_spins']
                        ])
                    ],
                    default => ['integer', (string) random_int(1, 100)],
                };

                GameConfiguration::factory()
                    ->forGame($game)
                    ->state([
                        'key' => $key,
                        'data_type' => $dataType,
                        'value' => $storedValue,
                        'sort_order' => $index,
                    ])
                    ->create();
            }
        });
    }
}

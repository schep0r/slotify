<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\RouletteConfiguration;
use App\Enums\GameType;
use Illuminate\Database\Seeder;

class RouletteConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create European Roulette game
        $europeanGame = Game::create([
            'name' => 'European Roulette',
            'slug' => 'european-roulette',
            'provider' => 'Slotify',
            'status' => 'active',
            'type' => GameType::ROULETTE->value,
            'min_bet' => 1.00,
            'max_bet' => 1000.00,
            'rtp' => 97.30,
            'is_active' => true,
        ]);

        RouletteConfiguration::create([
            'game_id' => $europeanGame->id,
            'wheel_type' => 'european',
            'min_bet' => 1.00,
            'max_bet' => 1000.00,
            'table_limits' => [
                'straight' => ['min' => 1.00, 'max' => 100.00],
                'split' => ['min' => 1.00, 'max' => 200.00],
                'street' => ['min' => 1.00, 'max' => 300.00],
                'corner' => ['min' => 1.00, 'max' => 400.00],
                'line' => ['min' => 1.00, 'max' => 600.00],
                'dozen' => ['min' => 1.00, 'max' => 1200.00],
                'column' => ['min' => 1.00, 'max' => 1200.00],
                'red' => ['min' => 1.00, 'max' => 1800.00],
                'black' => ['min' => 1.00, 'max' => 1800.00],
                'odd' => ['min' => 1.00, 'max' => 1800.00],
                'even' => ['min' => 1.00, 'max' => 1800.00],
                'low' => ['min' => 1.00, 'max' => 1800.00],
                'high' => ['min' => 1.00, 'max' => 1800.00],
            ],
            'special_rules' => [],
            'rtp_percentage' => 97.30,
            'is_active' => true,
            'description' => 'Classic European Roulette with single zero',
        ]);

        // Create American Roulette game
        $americanGame = Game::create([
            'name' => 'American Roulette',
            'slug' => 'american-roulette',
            'provider' => 'Slotify',
            'status' => 'active',
            'type' => GameType::ROULETTE->value,
            'min_bet' => 1.00,
            'max_bet' => 1000.00,
            'rtp' => 94.74,
            'is_active' => true,
        ]);

        RouletteConfiguration::create([
            'game_id' => $americanGame->id,
            'wheel_type' => 'american',
            'min_bet' => 1.00,
            'max_bet' => 1000.00,
            'table_limits' => [
                'straight' => ['min' => 1.00, 'max' => 100.00],
                'split' => ['min' => 1.00, 'max' => 200.00],
                'street' => ['min' => 1.00, 'max' => 300.00],
                'corner' => ['min' => 1.00, 'max' => 400.00],
                'line' => ['min' => 1.00, 'max' => 600.00],
                'dozen' => ['min' => 1.00, 'max' => 1200.00],
                'column' => ['min' => 1.00, 'max' => 1200.00],
                'red' => ['min' => 1.00, 'max' => 1800.00],
                'black' => ['min' => 1.00, 'max' => 1800.00],
                'odd' => ['min' => 1.00, 'max' => 1800.00],
                'even' => ['min' => 1.00, 'max' => 1800.00],
                'low' => ['min' => 1.00, 'max' => 1800.00],
                'high' => ['min' => 1.00, 'max' => 1800.00],
            ],
            'special_rules' => [],
            'rtp_percentage' => 94.74,
            'is_active' => true,
            'description' => 'American Roulette with double zero (00)',
        ]);
    }
}
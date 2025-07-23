<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 random games
        Game::factory()->count(20)->create();

        // Create some specific games
        Game::factory()->create([
            'name' => 'Lucky Sevens',
            'slug' => 'lucky-sevens',
            'provider' => 'slotify',
            'status' => 'active',
            'type' => 'classic',
            'min_bet' => 0.25,
            'max_bet' => 50.00,
            'reels' => 3,
            'rows' => 3,
            'paylines' => 5,
            'rtp' => 96.50,
            'is_active' => true,
        ]);

        Game::factory()->create([
            'name' => 'Mega Fortune',
            'slug' => 'mega-fortune',
            'provider' => 'slotify',
            'status' => 'active',
            'type' => 'progressive',
            'min_bet' => 1.00,
            'max_bet' => 100.00,
            'reels' => 5,
            'rows' => 3,
            'paylines' => 25,
            'rtp' => 94.75,
            'is_active' => true,
        ]);

        Game::factory()->create([
            'name' => 'Space Adventure',
            'slug' => 'space-adventure',
            'provider' => 'slotify',
            'status' => 'active',
            'type' => 'video',
            'min_bet' => 0.50,
            'max_bet' => 75.00,
            'reels' => 5,
            'rows' => 4,
            'paylines' => 40,
            'rtp' => 95.20,
            'is_active' => true,
        ]);
    }
}

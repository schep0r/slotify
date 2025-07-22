<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Game;
use App\Models\SlotConfiguration;
use Illuminate\Database\Seeder;

class SlotConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have games to attach configurations to
        $games = Game::all();

        if ($games->isEmpty()) {
            $this->command->warn('No games found. Please run GameSeeder first.');

            return;
        }

        // Classic Fruit Slot Configuration
        SlotConfiguration::create([
            'game_id' => $games->random()->id,
            'name' => 'Classic Fruit Slot',
            'theme' => 'fruit',
            'reels' => 5,
            'rows' => 3,
            'paylines' => 25,
            'rtp_percentage' => 96.50,
            'volatility' => 'medium',
            'min_bet' => 0.25,
            'max_bet' => 125.00,
            'bet_increment' => 0.25,
            'bet_levels' => [0.25, 0.50, 1.00, 2.50, 5.00, 10.00, 25.00, 50.00, 125.00],
            'has_progressive_jackpot' => false,
            'symbols' => [
                ['id' => 'CHERRY', 'name' => 'Cherry', 'value' => 1, 'frequency' => 25, 'payouts' => [3 => 5, 4 => 25, 5 => 100]],
                ['id' => 'LEMON', 'name' => 'Lemon', 'value' => 2, 'frequency' => 20, 'payouts' => [3 => 10, 4 => 50, 5 => 200]],
                ['id' => 'ORANGE', 'name' => 'Orange', 'value' => 3, 'frequency' => 18, 'payouts' => [3 => 15, 4 => 75, 5 => 300]],
                ['id' => 'PLUM', 'name' => 'Plum', 'value' => 4, 'frequency' => 15, 'payouts' => [3 => 20, 4 => 100, 5 => 400]],
                ['id' => 'GRAPES', 'name' => 'Grapes', 'value' => 5, 'frequency' => 12, 'payouts' => [3 => 25, 4 => 125, 5 => 500]],
                ['id' => 'WATERMELON', 'name' => 'Watermelon', 'value' => 6, 'frequency' => 8, 'payouts' => [3 => 50, 4 => 250, 5 => 1000]],
                ['id' => 'SEVEN', 'name' => 'Seven', 'value' => 10, 'frequency' => 2, 'payouts' => [3 => 100, 4 => 500, 5 => 2500]],
            ],
            'wild_symbols' => [
                ['id' => 'WILD', 'name' => 'Wild', 'substitutes_all' => true, 'frequency' => 3, 'multiplier' => 1]
            ],
            'scatter_symbols' => [
                ['id' => 'SCATTER', 'name' => 'Scatter', 'min_count' => 3, 'triggers_free_spins' => true, 'frequency' => 4, 'payouts' => [3 => 100, 4 => 500, 5 => 2000]]
            ],
            'paytable' => [
                ['symbol_id' => 'CHERRY', 'count' => 3, 'payout' => 5],
                ['symbol_id' => 'CHERRY', 'count' => 4, 'payout' => 25],
                ['symbol_id' => 'CHERRY', 'count' => 5, 'payout' => 100],
                ['symbol_id' => 'LEMON', 'count' => 3, 'payout' => 10],
                ['symbol_id' => 'LEMON', 'count' => 4, 'payout' => 50],
                ['symbol_id' => 'LEMON', 'count' => 5, 'payout' => 200],
                ['symbol_id' => 'SEVEN', 'count' => 3, 'payout' => 100],
                ['symbol_id' => 'SEVEN', 'count' => 4, 'payout' => 500],
                ['symbol_id' => 'SEVEN', 'count' => 5, 'payout' => 2500],
            ],
            'has_free_spins' => true,
            'free_spins_trigger_count' => 3,
            'free_spins_award' => 15,
            'free_spins_multiplier' => 2.00,
            'has_bonus_game' => false,
            'auto_play_enabled' => true,
            'max_auto_spins' => 100,
            'is_active' => true,
            'description' => 'A classic fruit-themed slot machine with traditional symbols and exciting bonus features.',
        ]);

        // Egyptian Adventure Slot Configuration
        SlotConfiguration::create([
            'game_id' => $games->random()->id,
            'name' => 'Egyptian Adventure',
            'theme' => 'egyptian',
            'reels' => 5,
            'rows' => 3,
            'paylines' => 20,
            'rtp_percentage' => 95.80,
            'volatility' => 'high',
            'min_bet' => 0.20,
            'max_bet' => 100.00,
            'bet_increment' => 0.20,
            'bet_levels' => [0.20, 0.40, 1.00, 2.00, 5.00, 10.00, 20.00, 50.00, 100.00],
            'has_progressive_jackpot' => true,
            'jackpot_seed' => 10000.00,
            'jackpot_contribution_rate' => 0.0150,
            'current_jackpot' => 15000.00,
            'symbols' => [
                ['id' => 'ANKH', 'name' => 'Ankh', 'value' => 1, 'frequency' => 30, 'payouts' => [3 => 5, 4 => 20, 5 => 80]],
                ['id' => 'SCARAB', 'name' => 'Scarab', 'value' => 2, 'frequency' => 25, 'payouts' => [3 => 8, 4 => 35, 5 => 120]],
                ['id' => 'EYE_OF_HORUS', 'name' => 'Eye of Horus', 'value' => 3, 'frequency' => 20, 'payouts' => [3 => 12, 4 => 50, 5 => 200]],
                ['id' => 'PHARAOH', 'name' => 'Pharaoh', 'value' => 5, 'frequency' => 15, 'payouts' => [3 => 25, 4 => 100, 5 => 500]],
                ['id' => 'CLEOPATRA', 'name' => 'Cleopatra', 'value' => 8, 'frequency' => 8, 'payouts' => [3 => 50, 4 => 200, 5 => 1000]],
                ['id' => 'PYRAMID', 'name' => 'Pyramid', 'value' => 10, 'frequency' => 5, 'payouts' => [3 => 75, 4 => 300, 5 => 1500]],
                ['id' => 'GOLDEN_MASK', 'name' => 'Golden Mask', 'value' => 15, 'frequency' => 2, 'payouts' => [3 => 150, 4 => 750, 5 => 5000]],
            ],
            'wild_symbols' => [
                ['id' => 'WILD_PHARAOH', 'name' => 'Wild Pharaoh', 'substitutes_all' => true, 'frequency' => 4, 'multiplier' => 2, 'special_feature' => 'expanding']
            ],
            'scatter_symbols' => [
                ['id' => 'BOOK_OF_RA', 'name' => 'Book of Ra', 'min_count' => 3, 'triggers_free_spins' => true, 'frequency' => 3, 'payouts' => [3 => 200, 4 => 1000, 5 => 5000]]
            ],
            'bonus_symbols' => [
                ['id' => 'TREASURE_CHEST', 'name' => 'Treasure Chest', 'frequency' => 5]
            ],
            'paytable' => [
                ['symbol_id' => 'ANKH', 'count' => 3, 'payout' => 5],
                ['symbol_id' => 'ANKH', 'count' => 4, 'payout' => 20],
                ['symbol_id' => 'ANKH', 'count' => 5, 'payout' => 80],
                ['symbol_id' => 'GOLDEN_MASK', 'count' => 3, 'payout' => 150],
                ['symbol_id' => 'GOLDEN_MASK', 'count' => 4, 'payout' => 750],
                ['symbol_id' => 'GOLDEN_MASK', 'count' => 5, 'payout' => 5000],
            ],
            'has_free_spins' => true,
            'free_spins_trigger_count' => 3,
            'free_spins_award' => 12,
            'free_spins_multiplier' => 3.00,
            'has_bonus_game' => true,
            'bonus_game_config' => [
                'type' => 'pick_and_win',
                'trigger_count' => 3,
                'max_picks' => 5,
                'prizes' => [50, 100, 250, 500, 1000, 2500],
            ],
            'special_features' => [
                'expanding_wilds' => true,
                'mystery_symbols' => true,
            ],
            'auto_play_enabled' => true,
            'max_auto_spins' => 50,
            'is_active' => true,
            'description' => 'Embark on an ancient Egyptian adventure with expanding wilds, free spins, and a progressive jackpot.',
        ]);

        // Diamond Deluxe Slot Configuration
        SlotConfiguration::create([
            'game_id' => $games->random()->id,
            'name' => 'Diamond Deluxe',
            'theme' => 'luxury',
            'reels' => 5,
            'rows' => 4,
            'paylines' => 40,
            'rtp_percentage' => 97.20,
            'volatility' => 'low',
            'min_bet' => 0.40,
            'max_bet' => 200.00,
            'bet_increment' => 0.40,
            'bet_levels' => [0.40, 0.80, 2.00, 4.00, 10.00, 20.00, 40.00, 100.00, 200.00],
            'has_progressive_jackpot' => false,
            'symbols' => [
                ['id' => 'DIAMOND', 'name' => 'Diamond', 'value' => 20, 'frequency' => 3, 'payouts' => [3 => 200, 4 => 1000, 5 => 10000]],
                ['id' => 'RUBY', 'name' => 'Ruby', 'value' => 15, 'frequency' => 5, 'payouts' => [3 => 150, 4 => 750, 5 => 3000]],
                ['id' => 'EMERALD', 'name' => 'Emerald', 'value' => 12, 'frequency' => 8, 'payouts' => [3 => 100, 4 => 500, 5 => 2000]],
                ['id' => 'SAPPHIRE', 'name' => 'Sapphire', 'value' => 10, 'frequency' => 10, 'payouts' => [3 => 80, 4 => 400, 5 => 1500]],
                ['id' => 'GOLD_BAR', 'name' => 'Gold Bar', 'value' => 8, 'frequency' => 12, 'payouts' => [3 => 60, 4 => 300, 5 => 1000]],
                ['id' => 'SILVER_COIN', 'name' => 'Silver Coin', 'value' => 5, 'frequency' => 15, 'payouts' => [3 => 40, 4 => 200, 5 => 600]],
                ['id' => 'BRONZE_MEDAL', 'name' => 'Bronze Medal', 'value' => 3, 'frequency' => 20, 'payouts' => [3 => 20, 4 => 100, 5 => 300]],
            ],
            'wild_symbols' => [
                ['id' => 'WILD_DIAMOND', 'name' => 'Wild Diamond', 'substitutes_all' => true, 'frequency' => 2, 'multiplier' => 3, 'special_feature' => 'multiplier']
            ],
            'scatter_symbols' => [
                ['id' => 'LUXURY_SCATTER', 'name' => 'Luxury Scatter', 'min_count' => 3, 'triggers_free_spins' => true, 'frequency' => 4, 'payouts' => [3 => 300, 4 => 1500, 5 => 7500]]
            ],
            'paytable' => [
                ['symbol_id' => 'DIAMOND', 'count' => 3, 'payout' => 200],
                ['symbol_id' => 'DIAMOND', 'count' => 4, 'payout' => 1000],
                ['symbol_id' => 'DIAMOND', 'count' => 5, 'payout' => 10000],
                ['symbol_id' => 'BRONZE_MEDAL', 'count' => 3, 'payout' => 20],
                ['symbol_id' => 'BRONZE_MEDAL', 'count' => 4, 'payout' => 100],
                ['symbol_id' => 'BRONZE_MEDAL', 'count' => 5, 'payout' => 300],
            ],
            'has_free_spins' => true,
            'free_spins_trigger_count' => 3,
            'free_spins_award' => 10,
            'free_spins_multiplier' => 1.50,
            'has_bonus_game' => false,
            'auto_play_enabled' => true,
            'max_auto_spins' => 200,
            'is_active' => true,
            'description' => 'A luxurious slot experience with precious gems, high RTP, and steady payouts.',
        ]);

        // Pirate\'s Treasure Slot Configuration
        SlotConfiguration::create([
            'game_id' => $games->random()->id,
            'name' => 'Pirate\'s Treasure',
            'theme' => 'pirate',
            'reels' => 5,
            'rows' => 3,
            'paylines' => 30,
            'rtp_percentage' => 94.50,
            'volatility' => 'high',
            'min_bet' => 0.30,
            'max_bet' => 150.00,
            'bet_increment' => 0.30,
            'bet_levels' => [0.30, 0.60, 1.50, 3.00, 7.50, 15.00, 30.00, 75.00, 150.00],
            'has_progressive_jackpot' => true,
            'jackpot_seed' => 5000.00,
            'jackpot_contribution_rate' => 0.0200,
            'current_jackpot' => 8500.00,
            'symbols' => [
                ['id' => 'PARROT', 'name' => 'Parrot', 'value' => 1, 'frequency' => 28, 'payouts' => [3 => 3, 4 => 15, 5 => 60]],
                ['id' => 'COMPASS', 'name' => 'Compass', 'value' => 2, 'frequency' => 22, 'payouts' => [3 => 6, 4 => 25, 5 => 100]],
                ['id' => 'ANCHOR', 'name' => 'Anchor', 'value' => 3, 'frequency' => 18, 'payouts' => [3 => 10, 4 => 40, 5 => 150]],
                ['id' => 'PIRATE_SHIP', 'name' => 'Pirate Ship', 'value' => 5, 'frequency' => 12, 'payouts' => [3 => 20, 4 => 80, 5 => 400]],
                ['id' => 'PIRATE_CAPTAIN', 'name' => 'Pirate Captain', 'value' => 8, 'frequency' => 8, 'payouts' => [3 => 40, 4 => 160, 5 => 800]],
                ['id' => 'TREASURE_MAP', 'name' => 'Treasure Map', 'value' => 12, 'frequency' => 5, 'payouts' => [3 => 60, 4 => 300, 5 => 1500]],
                ['id' => 'SKULL_CROSSBONES', 'name' => 'Skull & Crossbones', 'value' => 20, 'frequency' => 2, 'payouts' => [3 => 100, 4 => 600, 5 => 4000]],
            ],
            'wild_symbols' => [
                ['id' => 'PIRATE_WILD', 'name' => 'Pirate Wild', 'substitutes_all' => true, 'frequency' => 3, 'multiplier' => 2, 'special_feature' => 'sticky']
            ],
            'scatter_symbols' => [
                ['id' => 'TREASURE_CHEST', 'name' => 'Treasure Chest', 'min_count' => 3, 'triggers_free_spins' => true, 'frequency' => 4, 'payouts' => [3 => 150, 4 => 750, 5 => 3750]]
            ],
            'bonus_symbols' => [
                ['id' => 'CANNON', 'name' => 'Cannon', 'frequency' => 6]
            ],
            'paytable' => [
                ['symbol_id' => 'PARROT', 'count' => 3, 'payout' => 3],
                ['symbol_id' => 'PARROT', 'count' => 4, 'payout' => 15],
                ['symbol_id' => 'PARROT', 'count' => 5, 'payout' => 60],
                ['symbol_id' => 'SKULL_CROSSBONES', 'count' => 3, 'payout' => 100],
                ['symbol_id' => 'SKULL_CROSSBONES', 'count' => 4, 'payout' => 600],
                ['symbol_id' => 'SKULL_CROSSBONES', 'count' => 5, 'payout' => 4000],
            ],
            'has_free_spins' => true,
            'free_spins_trigger_count' => 3,
            'free_spins_award' => 8,
            'free_spins_multiplier' => 4.00,
            'has_bonus_game' => true,
            'bonus_game_config' => [
                'type' => 'treasure_hunt',
                'trigger_count' => 3,
                'locations' => 12,
                'treasures' => 5,
                'multipliers' => [1, 2, 3, 5, 10],
            ],
            'special_features' => [
                'sticky_wilds' => true,
                'cannon_feature' => true,
                'treasure_hunt_bonus' => true,
            ],
            'auto_play_enabled' => true,
            'max_auto_spins' => 75,
            'is_active' => true,
            'description' => 'Set sail for adventure with sticky wilds, treasure hunt bonus, and a progressive jackpot.',
        ]);

        // Space Adventure Slot Configuration
        SlotConfiguration::create([
            'game_id' => $games->random()->id,
            'name' => 'Space Adventure',
            'theme' => 'space',
            'reels' => 5,
            'rows' => 3,
            'paylines' => 15,
            'rtp_percentage' => 93.80,
            'volatility' => 'medium',
            'min_bet' => 0.15,
            'max_bet' => 75.00,
            'bet_increment' => 0.15,
            'bet_levels' => [0.15, 0.30, 0.75, 1.50, 3.75, 7.50, 15.00, 37.50, 75.00],
            'has_progressive_jackpot' => false,
            'symbols' => [
                ['id' => 'ASTRONAUT', 'name' => 'Astronaut', 'value' => 1, 'frequency' => 25, 'payouts' => [3 => 4, 4 => 20, 5 => 80]],
                ['id' => 'ROCKET', 'name' => 'Rocket', 'value' => 2, 'frequency' => 20, 'payouts' => [3 => 8, 4 => 40, 5 => 160]],
                ['id' => 'SATELLITE', 'name' => 'Satellite', 'value' => 3, 'frequency' => 18, 'payouts' => [3 => 12, 4 => 60, 5 => 240]],
                ['id' => 'UFO', 'name' => 'UFO', 'value' => 5, 'frequency' => 15, 'payouts' => [3 => 20, 4 => 100, 5 => 500]],
                ['id' => 'ALIEN', 'name' => 'Alien', 'value' => 8, 'frequency' => 10, 'payouts' => [3 => 40, 4 => 200, 5 => 1000]],
                ['id' => 'PLANET', 'name' => 'Planet', 'value' => 12, 'frequency' => 6, 'payouts' => [3 => 60, 4 => 360, 5 => 1800]],
                ['id' => 'GALAXY', 'name' => 'Galaxy', 'value' => 25, 'frequency' => 1, 'payouts' => [3 => 125, 4 => 750, 5 => 6250]],
            ],
            'wild_symbols' => [
                ['id' => 'COSMIC_WILD', 'name' => 'Cosmic Wild', 'substitutes_all' => true, 'frequency' => 4, 'multiplier' => 1]
            ],
            'scatter_symbols' => [
                ['id' => 'BLACK_HOLE', 'name' => 'Black Hole', 'min_count' => 3, 'triggers_free_spins' => true, 'frequency' => 5, 'payouts' => [3 => 100, 4 => 500, 5 => 2500]]
            ],
            'paytable' => [
                ['symbol_id' => 'ASTRONAUT', 'count' => 3, 'payout' => 4],
                ['symbol_id' => 'ASTRONAUT', 'count' => 4, 'payout' => 20],
                ['symbol_id' => 'ASTRONAUT', 'count' => 5, 'payout' => 80],
                ['symbol_id' => 'GALAXY', 'count' => 3, 'payout' => 125],
                ['symbol_id' => 'GALAXY', 'count' => 4, 'payout' => 750],
                ['symbol_id' => 'GALAXY', 'count' => 5, 'payout' => 6250],
            ],
            'has_free_spins' => true,
            'free_spins_trigger_count' => 3,
            'free_spins_award' => 20,
            'free_spins_multiplier' => 2.50,
            'has_bonus_game' => false,
            'auto_play_enabled' => true,
            'max_auto_spins' => 150,
            'is_active' => true,
            'description' => 'Explore the cosmos with this space-themed slot featuring cosmic wilds and black hole scatters.',
        ]);

        $this->command->info('SlotConfiguration seeder completed successfully!');
        $this->command->info('Created 5 slot configurations with different themes and features.');
    }
}

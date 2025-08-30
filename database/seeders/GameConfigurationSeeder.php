<?php

namespace Database\Seeders;

use App\Enums\GameConfigurationType;
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
        // Get all slot games to configure
        $slotGames = Game::all();

        foreach ($slotGames as $game) {
            $this->createSlotGameConfiguration($game);
        }
    }

    /**
     * Create configuration for slot games
     */
    private function createSlotGameConfiguration(Game $game): void
    {
        // Paylines configuration - array of win line templates
        $paylines = $this->generatePaylines($game);
        GameConfiguration::create([
            'game_id' => $game->id,
            'key' => GameConfigurationType::PAYLINES->value,
            'value' => json_encode($paylines),
            'data_type' => 'json',
            'description' => 'Payline templates defining winning line positions',
            'is_configurable' => true,
            'sort_order' => 1,
        ]);

        // Reels configuration - array of symbols for each reel
        $reels = $this->generateReelSymbols($game);
        GameConfiguration::create([
            'game_id' => $game->id,
            'key' => GameConfigurationType::REELS->value,
            'value' => json_encode($reels),
            'data_type' => 'json',
            'description' => 'Symbol arrays for each reel',
            'is_configurable' => true,
            'sort_order' => 2,
        ]);

        // Rows configuration
        GameConfiguration::create([
            'game_id' => $game->id,
            'key' => GameConfigurationType::ROWS_COUNT->value,
            'value' => (string) $game->rows,
            'data_type' => 'integer',
            'description' => 'Number of rows visible on each reel',
            'is_configurable' => false,
            'sort_order' => 3,
        ]);

        // Paytable configuration
        $paytable = $this->generateSlotPaytable($game);
        GameConfiguration::create([
            'game_id' => $game->id,
            'key' => GameConfigurationType::PAYTABLE->value,
            'value' => json_encode($paytable),
            'data_type' => 'json',
            'description' => 'Payout table for symbol combinations',
            'is_configurable' => true,
            'sort_order' => 4,
        ]);

        // Scatter configuration
        $scatterConfig = $this->generateScatterConfiguration($game);
        GameConfiguration::create([
            'game_id' => $game->id,
            'key' => GameConfigurationType::SCATTER_CONFIG->value,
            'value' => json_encode($scatterConfig),
            'data_type' => 'json',
            'description' => 'Scatter symbol configuration and payouts',
            'is_configurable' => true,
            'sort_order' => 5,
        ]);

        // Bonus features configuration
        $bonusFeatures = $this->generateBonusFeatures($game);
        GameConfiguration::create([
            'game_id' => $game->id,
            'key' => GameConfigurationType::BONUS_FEATURES->value,
            'value' => json_encode($bonusFeatures),
            'data_type' => 'json',
            'description' => 'Available bonus features and their configurations',
            'is_configurable' => true,
            'sort_order' => 6,
        ]);
    }

    /**
     * Generate paylines array with win line templates
     */
    private function generatePaylines(Game $game): array
    {
        $paylines = [];
        $rows = $game->rows;
        $reels = $game->reels;

        // Generate common payline patterns based on game dimensions
        if ($rows == 3 && $reels >= 3) {
            // Standard 3-row paylines
            $paylines = [
                [1, 1, 1, 1, 1], // Top row
                [2, 2, 2, 2, 2], // Middle row
                [3, 3, 3, 3, 3], // Bottom row
                [1, 2, 3, 2, 1], // V shape
                [3, 2, 1, 2, 3], // Inverted V shape
                [2, 1, 1, 1, 2], // Top V
                [2, 3, 3, 3, 2], // Bottom V
                [1, 1, 2, 3, 3], // Diagonal down
                [3, 3, 2, 1, 1], // Diagonal up
                [2, 1, 2, 3, 2], // Zigzag 1
                [2, 3, 2, 1, 2], // Zigzag 2
                [1, 2, 1, 2, 1], // Up-down pattern
                [3, 2, 3, 2, 3], // Down-up pattern
                [1, 3, 1, 3, 1], // Jump pattern 1
                [3, 1, 3, 1, 3], // Jump pattern 2
            ];
        } elseif ($rows == 4 && $reels >= 5) {
            // 4-row paylines
            $paylines = [
                [1, 1, 1, 1, 1], // Top row
                [2, 2, 2, 2, 2], // Second row
                [3, 3, 3, 3, 3], // Third row
                [4, 4, 4, 4, 4], // Bottom row
                [1, 2, 3, 4, 4], // Diagonal down
                [4, 3, 2, 1, 1], // Diagonal up
                [2, 1, 2, 3, 4], // Wave pattern 1
                [3, 4, 3, 2, 1], // Wave pattern 2
                [1, 3, 1, 3, 1], // Jump pattern
                [4, 2, 4, 2, 4], // Jump pattern 2
                [2, 3, 2, 3, 2], // Middle zigzag
                [3, 2, 3, 2, 3], // Middle zigzag 2
                [1, 4, 1, 4, 1], // Extreme jump
                [4, 1, 4, 1, 4], // Extreme jump 2
                [2, 1, 3, 4, 2], // Complex pattern 1
                [3, 4, 2, 1, 3], // Complex pattern 2
                [1, 2, 4, 3, 1], // Complex pattern 3
                [4, 3, 1, 2, 4], // Complex pattern 4
                [2, 4, 1, 3, 2], // Complex pattern 5
                [3, 1, 4, 2, 3], // Complex pattern 6
            ];
        } else {
            // Default simple paylines for other configurations
            for ($row = 1; $row <= $rows; $row++) {
                $paylines[] = array_fill(0, $reels, $row);
            }
        }

        // Trim paylines to match the actual number of paylines for the game
        return array_slice($paylines, 0, $game->paylines);
    }

    /**
     * Generate reel symbols array for each reel
     */
    private function generateReelSymbols(Game $game): array
    {
        $baseSymbols = [
            'CHERRY',
            'LEMON',
            'ORANGE',
            'PLUM',
            'GRAPES',
            'WATERMELON',
            'SEVEN',
            'BAR',
            'WILD',
            'SCATTER'
        ];

        $reels = [];

        for ($reelIndex = 0; $reelIndex < $game->reels; $reelIndex++) {
            $reelSymbols = [];

            // Create a weighted distribution of symbols for each reel
            $symbolWeights = [
                'CHERRY' => 8,      // Most common
                'LEMON' => 7,
                'ORANGE' => 6,
                'PLUM' => 5,
                'GRAPES' => 4,
                'WATERMELON' => 3,
                'BAR' => 3,
                'SEVEN' => 2,       // Less common, higher value
                'WILD' => 1,        // Rare
                'SCATTER' => 2,     // Moderate for bonus triggers
            ];

            // Generate reel strip (typically 30-50 symbols per reel)
            $reelLength = 32; // Standard reel length

            for ($position = 0; $position < $reelLength; $position++) {
                // Select symbol based on weights
                $symbol = $this->selectWeightedSymbol($symbolWeights);
                $reelSymbols[] = $symbol;
            }

            // Ensure each reel has at least one scatter for bonus potential
            if (!in_array('SCATTER', $reelSymbols)) {
                $randomPosition = rand(0, $reelLength - 1);
                $reelSymbols[$randomPosition] = 'SCATTER';
            }

            // Ensure each reel has at least one wild
            if (!in_array('WILD', $reelSymbols)) {
                $randomPosition = rand(0, $reelLength - 1);
                // Don't overwrite scatter
                if ($reelSymbols[$randomPosition] !== 'SCATTER') {
                    $reelSymbols[$randomPosition] = 'WILD';
                }
            }

            $reels[] = $reelSymbols;
        }

        return $reels;
    }

    /**
     * Select a symbol based on weighted probabilities
     */
    private function selectWeightedSymbol(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weights as $symbol => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $symbol;
            }
        }

        // Fallback
        return array_key_first($weights);
    }

    /**
     * Generate a realistic paytable for slot games
     */
    private function generateSlotPaytable(Game $game): array
    {
        $baseSymbols = [
            ['symbol' => 'CHERRY', 'name' => 'Cherry', 'value' => 1],
            ['symbol' => 'LEMON', 'name' => 'Lemon', 'value' => 2],
            ['symbol' => 'ORANGE', 'name' => 'Orange', 'value' => 3],
            ['symbol' => 'PLUM', 'name' => 'Plum', 'value' => 4],
            ['symbol' => 'GRAPES', 'name' => 'Grapes', 'value' => 5],
            ['symbol' => 'WATERMELON', 'name' => 'Watermelon', 'value' => 8],
            ['symbol' => 'SEVEN', 'name' => 'Seven', 'value' => 15],
            ['symbol' => 'BAR', 'name' => 'Bar', 'value' => 10],
            ['symbol' => 'WILD', 'name' => 'Wild', 'value' => 20],
        ];

        $paytable = [];

        foreach ($baseSymbols as $symbol) {
            // Generate payouts for 3, 4, and 5 of a kind
            $baseMultiplier = $symbol['value'];

            if ($game->reels >= 3) {
                $paytable[$symbol['symbol']][3] = $baseMultiplier * 5;
            }

            if ($game->reels >= 4) {
                $paytable[$symbol['symbol']][3] = $baseMultiplier * 10;
            }

            if ($game->reels >= 5) {
                $paytable[$symbol['symbol']][5] = $baseMultiplier * 25;
            }
        }

        return $paytable;
    }

    /**
     * Generate scatter symbol configuration
     */
    private function generateScatterConfiguration(Game $game): array
    {
        return [
            'symbol_id' => 'SCATTER',
            'symbol_name' => 'Scatter',
            'min_count' => 3,
            'triggers_free_spins' => true,
            'free_spins_count' => 15,
            'free_spins_multiplier' => 2.0,
            'payouts' => [
                3 => 100,
                4 => 500,
                5 => 2000
            ],
            'frequency' => 4, // Appears on average 1 in 25 spins per reel
            'can_appear_on_reels' => range(1, $game->reels)
        ];
    }

    /**
     * Generate bonus features configuration
     */
    private function generateBonusFeatures(Game $game): array
    {
        return [
            'free_spins' => [
                'enabled' => true,
                'trigger_symbols' => ['SCATTER'],
                'min_trigger_count' => 3,
                'awards' => [
                    3 => 15,
                    4 => 20,
                    5 => 25
                ],
                'multiplier' => 2.0,
                'retrigger_enabled' => true
            ],
            'wild_features' => [
                'enabled' => true,
                'expanding_wilds' => $game->reels >= 5,
                'sticky_wilds' => false,
                'wild_multiplier' => 1.0,
                'wild_symbols' => ['WILD']
            ],
            'bonus_game' => [
                'enabled' => false,
                'trigger_symbols' => ['BONUS'],
                'min_trigger_count' => 3,
                'type' => 'pick_and_win'
            ],
            'progressive_jackpot' => [
                'enabled' => $game->max_bet >= 50.00, // Only for higher bet games
                'contribution_rate' => 0.01, // 1% of bet
                'seed_amount' => 1000.00
            ]
        ];
    }
}

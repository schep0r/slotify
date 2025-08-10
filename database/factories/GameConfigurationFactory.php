<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameConfiguration>
 */
class GameConfigurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = GameConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Possible configuration keys and their intended data types
        $options = [
            'paylines' => 'integer',
            'reels' => 'integer',
            'rows_count' => 'integer',
            'bonus_features' => 'json',
        ];

        $key = $this->faker->randomElement(array_keys($options));
        $dataType = $options[$key];

        // Generate a suitable raw value according to data type
        $rawValue = match ($dataType) {
            'integer' => $this->faker->numberBetween(1, 100),
            'decimal' => $this->faker->randomFloat(2, 0.01, 100.00),
            'boolean' => $this->faker->boolean(),
            'json' => [
                'features' => $this->faker->randomElements(
                    ['free_spins', 'multipliers', 'bonus_game', 'wild_reels', 'sticky_wilds'],
                    $this->faker->numberBetween(1, 3)
                )
            ],
            default => $this->faker->word(),
        };

        // Persist as string/text in DB; JSON as encoded string, boolean as 0/1 string
        $storedValue = match ($dataType) {
            'json' => json_encode($rawValue),
            'boolean' => $rawValue ? '1' : '0',
            default => (string)$rawValue,
        };

        return [
            'game_id' => Game::factory(),
            'key' => $key,
            'value' => $storedValue,
            'data_type' => $dataType,
            'description' => $this->faker->optional()->sentence(),
            'is_configurable' => $this->faker->boolean(70),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Convenience state to attach to an existing Game instance.
     */
    public function forGame(Game $game): self
    {
        return $this->state(fn () => [
            'game_id' => $game->id,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\RouletteConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RouletteConfiguration>
 */
class RouletteConfigurationFactory extends Factory
{
    protected $model = RouletteConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'wheel_type' => $this->faker->randomElement(['european', 'american']),
            'min_bet' => 1.00,
            'max_bet' => 1000.00,
            'table_limits' => RouletteConfiguration::getDefaultTableLimits(),
            'special_rules' => [],
            'rtp_percentage' => $this->faker->randomFloat(2, 94.00, 98.00),
            'is_active' => true,
            'description' => $this->faker->sentence(),
            'metadata' => [],
        ];
    }

    /**
     * Configure for European roulette
     */
    public function european(): static
    {
        return $this->state(fn (array $attributes) => [
            'wheel_type' => 'european',
            'rtp_percentage' => 97.30,
        ]);
    }

    /**
     * Configure for American roulette
     */
    public function american(): static
    {
        return $this->state(fn (array $attributes) => [
            'wheel_type' => 'american',
            'rtp_percentage' => 94.74,
        ]);
    }

    /**
     * Configure as inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'slug' => $this->faker->slug(),
            'provider' => 'lotto649',
            'status' => 'active',
            'type' => $this->faker->randomElement(['classic', 'video', 'progressive']),
            'min_bet' => $this->faker->randomFloat(2, 0.1, 5),
            'max_bet' => $this->faker->randomFloat(2, 10, 100),
            'reels' => $this->faker->numberBetween(3, 7),
            'rows' => $this->faker->numberBetween(3, 5),
            'paylines' => $this->faker->numberBetween(10, 50),
            'rtp' => $this->faker->randomFloat(2, 90, 98),
            'configuration' => json_encode([
                'symbols' => ['wild', 'scatter', 'bonus', 'high1', 'high2', 'high3', 'low1', 'low2', 'low3', 'low4'],
                'features' => $this->faker->randomElements(['free_spins', 'multipliers', 'bonus_game', 'wild_reels'], $this->faker->numberBetween(1, 4)),
            ]),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}

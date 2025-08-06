<?php

namespace Database\Factories;

use App\Models\BonusType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BonusType>
 */
class BonusTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['free_spins', 'bonus_coins', 'multiplier', 'no_deposit', 'deposit_match', 'cashback'];

        return [
            'name' => fake()->words(2, true) . ' Bonus',
            'code' => strtoupper(fake()->lexify('???_???')),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement($types),
            'config' => [
                'is_claimable' => fake()->boolean(70),
                'cooldown_hours' => fake()->randomElement([12, 24, 48, 72]),
                'max_claims' => fake()->optional(0.7, null)->randomElement([1, 3, 5, 10]),
                'amount' => fake()->numberBetween(10, 100),
                'percentage' => fake()->randomElement([10, 25, 50, 100, 200]),
                'expiry_days' => fake()->randomElement([1, 3, 7, 14, 30]),
            ],
            'is_active' => fake()->boolean(80),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (BonusType $bonusType) {
            // Additional setup after making the model if needed
        })->afterCreating(function (BonusType $bonusType) {
            // Additional setup after creating the model if needed
        });
    }

    /**
     * Indicate that the bonus type is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the bonus type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the bonus type is claimable.
     */
    public function claimable(): static
    {
        return $this->state(function (array $attributes) {
            $config = $attributes['config'] ?? [];
            $config['is_claimable'] = true;

            return [
                'config' => $config,
            ];
        });
    }
}

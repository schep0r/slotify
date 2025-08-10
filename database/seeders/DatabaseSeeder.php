<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\BonusTypeSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
        ]);

        // Run the seeders
        $this->call([
            GameSeeder::class,
            GameConfigurationSeeder::class,
            BonusTypeSeeder::class,
        ]);
    }
}

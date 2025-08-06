<?php

namespace Database\Seeders;

use App\Models\BonusType;
use Illuminate\Database\Seeder;

class BonusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create predefined bonus types
        $this->createDepositBonus();
        $this->createFreeSpinsBonus();
        $this->createCashbackBonus();
        $this->createWelcomeBonus();
        $this->createReferralBonus();
        $this->createLoyaltyBonus();

        // Create 5 random bonus types
        BonusType::factory(5)->create();
    }

    /**
     * Create a deposit bonus type
     */
    private function createDepositBonus(): void
    {
        BonusType::factory()->create([
            'name' => 'First Deposit Bonus',
            'code' => 'DEPOSIT_100',
            'description' => 'Get 100% bonus on your first deposit up to $100',
            'type' => 'deposit_match',
            'config' => [
                'is_claimable' => true,
                'cooldown_hours' => 24,
                'max_claims' => 1,
                'percentage' => 100,
                'max_amount' => 100,
                'min_deposit' => 10,
                'wagering_requirement' => 20,
                'expiry_days' => 7,
            ],
            'is_active' => true,
        ]);
    }

    /**
     * Create a free spins bonus type
     */
    private function createFreeSpinsBonus(): void
    {
        BonusType::factory()->create([
            'name' => 'Daily Free Spins',
            'code' => 'FREE_SPINS_10',
            'description' => 'Claim 10 free spins every day',
            'type' => 'free_spins',
            'config' => [
                'is_claimable' => true,
                'cooldown_hours' => 24,
                'max_claims' => null,
                'spins_count' => 10,
                'game_ids' => [1, 2, 3], // Assuming these are valid game IDs
                'expiry_hours' => 24,
                'max_win' => 50,
            ],
            'is_active' => true,
        ]);
    }

    /**
     * Create a cashback bonus type
     */
    private function createCashbackBonus(): void
    {
        BonusType::factory()->create([
            'name' => 'Weekly Cashback',
            'code' => 'CASHBACK_10',
            'description' => 'Get 10% cashback on your losses every week',
            'type' => 'cashback',
            'config' => [
                'is_claimable' => true,
                'cooldown_hours' => 168, // 7 days
                'max_claims' => null,
                'percentage' => 10,
                'min_loss' => 50,
                'max_cashback' => 200,
                'wagering_requirement' => 1,
                'expiry_days' => 3,
            ],
            'is_active' => true,
        ]);
    }

    /**
     * Create a welcome bonus type
     */
    private function createWelcomeBonus(): void
    {
        BonusType::factory()->create([
            'name' => 'Welcome Package',
            'code' => 'WELCOME_PACK',
            'description' => 'Get bonuses on your first 3 deposits and 50 free spins',
            'type' => 'no_deposit',
            'config' => [
                'is_claimable' => false, // Automatically assigned
                'cooldown_hours' => 0,
                'max_claims' => 1,
                'deposit_bonuses' => [
                    ['deposit' => 1, 'percentage' => 100, 'max_amount' => 100],
                    ['deposit' => 2, 'percentage' => 50, 'max_amount' => 100],
                    ['deposit' => 3, 'percentage' => 25, 'max_amount' => 100],
                ],
                'free_spins' => 50,
                'wagering_requirement' => 30,
                'expiry_days' => 14,
            ],
            'is_active' => true,
        ]);
    }

    /**
     * Create a referral bonus type
     */
    private function createReferralBonus(): void
    {
        BonusType::factory()->create([
            'name' => 'Refer a Friend',
            'code' => 'REFER_FRIEND',
            'description' => 'Get $25 for each friend you refer who makes a deposit',
            'type' => 'bonus_coins',
            'config' => [
                'is_claimable' => false, // Automatically assigned
                'cooldown_hours' => 0,
                'max_claims' => null,
                'amount' => 25,
                'min_friend_deposit' => 20,
                'wagering_requirement' => 5,
                'expiry_days' => 30,
            ],
            'is_active' => true,
        ]);
    }

    /**
     * Create a loyalty bonus type
     */
    private function createLoyaltyBonus(): void
    {
        BonusType::factory()->create([
            'name' => 'VIP Loyalty Bonus',
            'code' => 'VIP_LOYALTY',
            'description' => 'Monthly bonus for VIP players based on activity',
            'type' => 'multiplier',
            'config' => [
                'is_claimable' => true,
                'cooldown_hours' => 720, // 30 days
                'max_claims' => null,
                'tiers' => [
                    ['level' => 1, 'amount' => 10, 'wagering_requirement' => 10],
                    ['level' => 2, 'amount' => 25, 'wagering_requirement' => 8],
                    ['level' => 3, 'amount' => 50, 'wagering_requirement' => 5],
                    ['level' => 4, 'amount' => 100, 'wagering_requirement' => 3],
                ],
                'expiry_days' => 7,
            ],
            'is_active' => true,
        ]);
    }
}

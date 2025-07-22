<?php

namespace Tests\Services;

use App\Models\SlotConfiguration;
use App\Services\PayoutCalculator;
use PHPUnit\Framework\TestCase;

class PayoutCalculatorTest extends TestCase
{
    public function testCalculatePayoutWithLineWin()
    {
        $config = new SlotConfiguration([
            'symbols' => [
                ['id' => 'A', 'payouts' => [3 => 10, 4 => 20, 5 => 50]],
                ['id' => 'B', 'payouts' => [3 => 5, 4 => 15, 5 => 25]],
            ],
            'paylines' => 5,
            'scatter_symbols' => [['id' => 'S', 'name' => 'Hole', 'min_count' => 3, 'triggers_free_spins' => true, 'frequency' => 5, 'payouts' => [3 => 100, 4 => 500, 5 => 2500]]],
            'wild_symbols' => [['id' => 'W', 'name' => 'Wild', 'substitutes_all' => true, 'frequency' => 4, 'multiplier' => 1]],
        ]);

        $reelResult = [
            'A', 'A', 'A', 'B', 'B',
            'B', 'A', 'W', 'B', 'B',
            'A', 'B', 'A', 'S', 'B',
        ];
        $betAmount = 10.0;

        $payoutCalculator = new PayoutCalculator($config);
        $result = $payoutCalculator->calculatePayout($reelResult, $betAmount);

        $this->assertEquals(12, count($result['win_details']));
        $this->assertEquals(270.0, $result['total_payout']);
    }
}

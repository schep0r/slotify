<?php

namespace Tests\Unit;

use App\Services\WildResultService;
use PHPUnit\Framework\TestCase;

class WildResultServiceTest extends TestCase
{
    private WildResultService $wildResultService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wildResultService = new WildResultService();
    }

    public function test_calculate_wild_multiplier()
    {
        $visibleSymbols = [
            ['cherry', 'wild', 'lemon'],
            ['wild', 'cherry', 'wild'],
            ['lemon', 'wild', 'cherry']
        ];

        $multiplier = $this->wildResultService->calculateWildMultiplier($visibleSymbols);
        
        // 4 wild symbols should give multiplier of 5 (1 + 4)
        $this->assertEquals(5, $multiplier);
    }

    public function test_find_best_wild_substitute()
    {
        $symbols = ['wild', 'cherry', 'cherry', 'wild', 'lemon'];
        
        $substitute = $this->wildResultService->findBestWildSubstitute($symbols);
        
        // Cherry appears twice, lemon once, so cherry should be chosen
        $this->assertEquals('cherry', $substitute);
    }

    public function test_get_wild_positions()
    {
        $visibleSymbols = [
            ['cherry', 'wild', 'lemon'],
            ['wild', 'cherry', 'wild'],
            ['lemon', 'wild', 'cherry']
        ];

        $positions = $this->wildResultService->getWildPositions($visibleSymbols);
        
        $expectedPositions = [
            ['reel' => 0, 'row' => 1],
            ['reel' => 1, 'row' => 0],
            ['reel' => 1, 'row' => 2],
            ['reel' => 2, 'row' => 1]
        ];
        
        $this->assertEquals($expectedPositions, $positions);
    }
}
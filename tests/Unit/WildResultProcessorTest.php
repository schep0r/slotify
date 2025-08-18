<?php

namespace Tests\Unit;

use App\Processors\WildResultProcessor;
use PHPUnit\Framework\TestCase;

class WildResultProcessorTest extends TestCase
{
    private WildResultProcessor $wildResultProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wildResultProcessor = new WildResultProcessor();
    }

    public function test_calculate_wild_multiplier()
    {
        $visibleSymbols = [
            ['cherry', 'wild', 'lemon'],
            ['wild', 'cherry', 'wild'],
            ['lemon', 'wild', 'cherry']
        ];

        $multiplier = $this->wildResultProcessor->calculateWildMultiplier($visibleSymbols);
        
        // 4 wild symbols should give multiplier of 5 (1 + 4)
        $this->assertEquals(5, $multiplier);
    }

    public function test_find_best_wild_substitute()
    {
        $symbols = ['wild', 'cherry', 'cherry', 'wild', 'lemon'];
        
        $substitute = $this->wildResultProcessor->findBestWildSubstitute($symbols);
        
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

        $positions = $this->wildResultProcessor->getWildPositions($visibleSymbols);
        
        $expectedPositions = [
            ['reel' => 0, 'row' => 1],
            ['reel' => 1, 'row' => 0],
            ['reel' => 1, 'row' => 2],
            ['reel' => 2, 'row' => 1]
        ];
        
        $this->assertEquals($expectedPositions, $positions);
    }
}
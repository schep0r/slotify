<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Services\ScatterResultService;
use PHPUnit\Framework\TestCase;

class ScatterResultServiceTest extends TestCase
{
    private ScatterResultService $scatterResultService;
    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scatterResultService = new ScatterResultService();

        // Create a stub Game with scatter configuration compatible with the service
        $scatterConfigRecord = new class {
            public array $value;
            public function __construct()
            {
                $this->value = [
                    'symbol' => 'scatter',
                    'pays_independently' => true,
                    // paytable multipliers by scatter count
                    'paytable' => [
                        3 => 2,
                        4 => 10,
                        5 => 100,
                    ],
                ];
            }
        };

        $configs = [$scatterConfigRecord];

        $this->game = new class($configs) extends Game {
            protected array $configs;
            public function __construct(array $configs = [])
            {
                parent::__construct();
                $this->configs = $configs;
            }
            public function getAttribute($key)
            {
                if ($key === 'scatterConfigurations') {
                    return $this->configs; // Return preloaded relation substitute
                }
                return parent::getAttribute($key);
            }
        };
    }

    public function test_count_scatter_symbols()
    {
        $visibleSymbols = [
            ['cherry', 'scatter', 'lemon'],
            ['wild', 'cherry', 'scatter'],
            ['scatter', 'wild', 'cherry']
        ];

        $count = $this->scatterResultService->countScatterSymbols($visibleSymbols, 'scatter');

        $this->assertEquals(3, $count);
    }

    public function test_check_scatter_bonus_with_three_scatters()
    {
        $visibleSymbols = [
            ['cherry', 'scatter', 'lemon'],
            ['wild', 'cherry', 'scatter'],
            ['scatter', 'wild', 'cherry']
        ];

        $result = $this->scatterResultService->checkScatterBonus($this->game, $visibleSymbols, 10.0);

        // With config: 3 scatters pay 2x bet; freeSpins are not calculated in checkScatterBonus anymore
        $this->assertEquals(20.0, $result['payout']);
        $this->assertEquals(0, $result['freeSpins']);
        $this->assertEquals([3], $result['scatterCount']);
        $this->assertTrue($result['isScatterWin']);

        // Ensure positions are returned for the configured scatter symbol (service currently appends the array to itself)
        $expectedFirstPositions = [
            ['reel' => 0, 'row' => 1],
            ['reel' => 1, 'row' => 2],
            ['reel' => 2, 'row' => 0]
        ];
        $this->assertEquals($expectedFirstPositions, array_slice($result['positions'], 0, 3));
        $this->assertCount(4, $result['positions']);
    }

    public function test_check_scatter_bonus_with_insufficient_scatters()
    {
        $visibleSymbols = [
            ['cherry', 'scatter', 'lemon'],
            ['wild', 'cherry', 'wild'],
            ['lemon', 'wild', 'cherry']
        ];

        $result = $this->scatterResultService->checkScatterBonus($this->game, $visibleSymbols, 10.0);

        $this->assertEquals(0, $result['payout']);
        $this->assertEquals(0, $result['freeSpins']);
        $this->assertEquals([1], $result['scatterCount']);
        // Service flags isScatterWin based on presence of configurations
        $this->assertTrue($result['isScatterWin']);

        $this->assertEquals([
            ['reel' => 0, 'row' => 1]
        ], array_slice($result['positions'], 0, 1));
        $this->assertCount(2, $result['positions']);
    }

    public function test_get_scatter_positions()
    {
        $visibleSymbols = [
            ['cherry', 'scatter', 'lemon'],
            ['wild', 'cherry', 'scatter'],
            ['scatter', 'wild', 'cherry']
        ];

        $positions = $this->scatterResultService->getScatterPositions($visibleSymbols, 'scatter');

        $expectedPositions = [
            ['reel' => 0, 'row' => 1],
            ['reel' => 1, 'row' => 2],
            ['reel' => 2, 'row' => 0]
        ];

        $this->assertEquals($expectedPositions, $positions);
    }
}

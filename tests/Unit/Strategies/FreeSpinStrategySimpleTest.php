<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Strategies\FreeSpinStrategy;
use PHPUnit\Framework\TestCase;

class FreeSpinStrategySimpleTest extends TestCase
{
    private FreeSpinStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();

        // Create strategy with minimal mocks for basic tests
        $this->strategy = new FreeSpinStrategy(
            $this->createMock(\App\Contracts\ReelGeneratorInterface::class),
            $this->createMock(\App\Contracts\PayoutCalculatorInterface::class),
            $this->createMock(\App\Contracts\GameLoggerInterface::class),
            $this->createMock(\App\Managers\GameSessionManager::class),
            $this->createMock(\App\Managers\FreeSpinManager::class)
        );
    }

    public function testCanHandleFreeSpins(): void
    {
        $gameData = ['useFreeSpins' => true];
        $this->assertTrue($this->strategy->canHandle($gameData));
    }

    public function testCannotHandleBetSpins(): void
    {
        $gameData = ['useFreeSpins' => false];
        $this->assertFalse($this->strategy->canHandle($gameData));

        $gameData = []; // useFreeSpins not set
        $this->assertFalse($this->strategy->canHandle($gameData));
    }

    public function testGetRequiredInputs(): void
    {
        $inputs = $this->strategy->getRequiredInputs();
        
        $this->assertArrayHasKey('useFreeSpins', $inputs);
        $this->assertArrayHasKey('activePaylines', $inputs);
        $this->assertEquals('required|boolean|accepted', $inputs['useFreeSpins']);
        $this->assertEquals('array|nullable', $inputs['activePaylines']);
    }
}
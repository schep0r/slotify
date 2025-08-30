<?php

declare(strict_types=1);

namespace Tests\Unit\Engines;

use App\DTOs\GameResultDto;
use App\Engines\SlotGameEngine;
use App\Models\Game;
use App\Models\User;
use App\Strategies\BetSpinStrategy;
use App\Strategies\FreeSpinStrategy;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class SlotGameEngineTest extends TestCase
{
    private SlotGameEngine $engine;
    private BetSpinStrategy $betSpinStrategy;
    private FreeSpinStrategy $freeSpinStrategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->betSpinStrategy = Mockery::mock(BetSpinStrategy::class);
        $this->freeSpinStrategy = Mockery::mock(FreeSpinStrategy::class);

        $this->engine = new SlotGameEngine(
            $this->betSpinStrategy,
            $this->freeSpinStrategy
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testPlayWithBetSpinStrategy(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $game = Mockery::mock(Game::class);
        $gameData = ['betAmount' => 10.0, 'useFreeSpins' => false];

        // Create a real GameResultDto instead of mocking it
        $expectedResult = new GameResultDto(
            betAmount: 10.0,
            winAmount: 50.0,
            newBalance: 1040.0,
            gameData: new \App\DTOs\SlotGameDataDto(
                betAmount: 10.0,
                winAmount: 50.0,
                reelPositions: [0, 1, 2],
                visibleSymbols: [['A', 'B', 'C']],
                winningLines: []
            )
        );

        $this->betSpinStrategy->shouldReceive('canHandle')
            ->once()
            ->with($gameData)
            ->andReturn(true);

        $this->betSpinStrategy->shouldReceive('execute')
            ->once()
            ->with($user, $game, $gameData)
            ->andReturn($expectedResult);

        // Act
        $result = $this->engine->play($user, $game, $gameData);

        // Assert
        $this->assertSame($expectedResult, $result);
    }

    public function testPlayWithFreeSpinStrategy(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $game = Mockery::mock(Game::class);
        $gameData = ['useFreeSpins' => true];

        // Create a real GameResultDto instead of mocking it
        $expectedResult = new GameResultDto(
            betAmount: 0,
            winAmount: 25.0,
            newBalance: 1025.0,
            gameData: new \App\DTOs\SlotGameDataDto(
                betAmount: 5.0,
                winAmount: 25.0,
                reelPositions: [0, 1, 2],
                visibleSymbols: [['A', 'A', 'A']],
                winningLines: []
            )
        );

        $this->betSpinStrategy->shouldReceive('canHandle')
            ->once()
            ->with($gameData)
            ->andReturn(false);

        $this->freeSpinStrategy->shouldReceive('canHandle')
            ->once()
            ->with($gameData)
            ->andReturn(true);

        $this->freeSpinStrategy->shouldReceive('execute')
            ->once()
            ->with($user, $game, $gameData)
            ->andReturn($expectedResult);

        // Act
        $result = $this->engine->play($user, $game, $gameData);

        // Assert
        $this->assertSame($expectedResult, $result);
    }

    public function testPlayThrowsExceptionWhenNoStrategyCanHandle(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $game = Mockery::mock(Game::class);
        $gameData = ['invalidData' => true];

        $this->betSpinStrategy->shouldReceive('canHandle')
            ->once()
            ->with($gameData)
            ->andReturn(false);

        $this->freeSpinStrategy->shouldReceive('canHandle')
            ->once()
            ->with($gameData)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No suitable strategy found for the given game data');

        $this->engine->play($user, $game, $gameData);
    }

    public function testValidateInputWithValidPaylines(): void
    {
        // Arrange
        $game = Mockery::mock(Game::class);
        $game->shouldIgnoreMissing();
        $user = Mockery::mock(User::class);

        // Mock the paylinesConfiguration property access
        $paylinesConfig = (object) ['value' => [
            [0, 1, 2],
            [0, 0, 0],
            [1, 1, 1],
            [2, 2, 2]
        ]];
        $game->paylinesConfiguration = $paylinesConfig;

        $gameData = [
            'betAmount' => 10.0,
            'activePaylines' => [0, 1, 2, 3] // Valid paylines (indices 0-3 for 4 paylines)
        ];

        // Act & Assert - Should not throw exception
        $this->engine->validateInput($gameData, $game, $user);
        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function testValidateInputWithInvalidPaylines(): void
    {
        // Arrange
        $game = Mockery::mock(Game::class);
        $game->shouldIgnoreMissing();
        $user = Mockery::mock(User::class);

        // Mock the paylinesConfiguration property access
        $paylinesConfig = (object) ['value' => [
            [0, 1, 2],
            [0, 0, 0]
        ]];
        $game->paylinesConfiguration = $paylinesConfig;

        $gameData = [
            'betAmount' => 10.0,
            'activePaylines' => [0, 1, 5] // Payline 5 doesn't exist (only 0,1 available)
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid payline: 5');

        $this->engine->validateInput($gameData, $game, $user);
    }

    public function testValidateInputWithDefaultPaylines(): void
    {
        // Arrange
        $game = Mockery::mock(Game::class);
        $game->shouldIgnoreMissing();
        $user = Mockery::mock(User::class);

        // Mock the paylinesConfiguration property access
        $paylinesConfig = (object) ['value' => [[0, 1, 2]]];
        $game->paylinesConfiguration = $paylinesConfig;

        $gameData = [
            'betAmount' => 10.0
            // No activePaylines specified - should default to [0]
        ];

        // Act & Assert - Should not throw exception
        $this->engine->validateInput($gameData, $game, $user);
        $this->assertTrue(true);
    }

    public function testGetRequiredInputsMergesAllStrategies(): void
    {
        // Arrange
        $this->betSpinStrategy->shouldReceive('getRequiredInputs')
            ->once()
            ->andReturn([
                'betAmount' => 'required|numeric|min:0.01'
            ]);

        $this->freeSpinStrategy->shouldReceive('getRequiredInputs')
            ->once()
            ->andReturn([
                'useFreeSpins' => 'required|boolean|accepted'
            ]);

        // Act
        $inputs = $this->engine->getRequiredInputs();

        // Assert
        $this->assertArrayHasKey('betAmount', $inputs);
        $this->assertArrayHasKey('useFreeSpins', $inputs);
        $this->assertArrayHasKey('activePaylines', $inputs);
        $this->assertEquals('required|numeric|min:0.01', $inputs['betAmount']);
        $this->assertEquals('boolean|nullable', $inputs['useFreeSpins']);
        $this->assertEquals('array|nullable', $inputs['activePaylines']);
    }

    public function testGetConfigurationRequirements(): void
    {
        $requirements = $this->engine->getConfigurationRequirements();

        $expectedRequirements = [
            'reels' => 'required|array',
            'rows' => 'required|integer|min:1',
            'paylines' => 'required|array',
            'paytable' => 'required|array',
            'rtp' => 'required|numeric|between:80,99',
        ];

        $this->assertEquals($expectedRequirements, $requirements);
    }
}

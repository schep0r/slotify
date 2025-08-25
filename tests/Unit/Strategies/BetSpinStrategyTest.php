<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Contracts\BetValidatorInterface;
use App\Contracts\GameLoggerInterface;
use App\Contracts\PayoutCalculatorInterface;
use App\Contracts\ReelGeneratorInterface;
use App\Contracts\TransactionManagerInterface;
use App\DTOs\GameResultDto;
use App\Managers\GameSessionManager;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use App\Strategies\BetSpinStrategy;
use Mockery;
use PHPUnit\Framework\TestCase;

class BetSpinStrategyTest extends TestCase
{
    private BetSpinStrategy $strategy;
    private BetValidatorInterface $betValidator;
    private ReelGeneratorInterface $reelGenerator;
    private PayoutCalculatorInterface $payoutCalculator;
    private TransactionManagerInterface $transactionManager;
    private GameLoggerInterface $gameLogger;
    private GameSessionManager $gameSessionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->betValidator = Mockery::mock(BetValidatorInterface::class);
        $this->reelGenerator = Mockery::mock(ReelGeneratorInterface::class);
        $this->payoutCalculator = Mockery::mock(PayoutCalculatorInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->gameLogger = Mockery::mock(GameLoggerInterface::class);
        $this->gameSessionManager = Mockery::mock(GameSessionManager::class);

        $this->strategy = new BetSpinStrategy(
            $this->betValidator,
            $this->reelGenerator,
            $this->payoutCalculator,
            $this->transactionManager,
            $this->gameLogger,
            $this->gameSessionManager
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCanHandleBetSpins(): void
    {
        $gameData = ['betAmount' => 10.0, 'useFreeSpins' => false];
        $this->assertTrue($this->strategy->canHandle($gameData));

        $gameData = ['betAmount' => 10.0]; // useFreeSpins not set
        $this->assertTrue($this->strategy->canHandle($gameData));
    }

    public function testCannotHandleFreeSpins(): void
    {
        $gameData = ['betAmount' => 10.0, 'useFreeSpins' => true];
        $this->assertFalse($this->strategy->canHandle($gameData));
    }

    public function testCannotHandleWithoutBetAmount(): void
    {
        $gameData = ['useFreeSpins' => false];
        $this->assertFalse($this->strategy->canHandle($gameData));
    }

    public function testGetRequiredInputs(): void
    {
        $inputs = $this->strategy->getRequiredInputs();
        
        $this->assertArrayHasKey('betAmount', $inputs);
        $this->assertArrayHasKey('activePaylines', $inputs);
        $this->assertEquals('required|numeric|min:0.01', $inputs['betAmount']);
        $this->assertEquals('array|nullable', $inputs['activePaylines']);
    }

    public function testExecuteSuccessfulSpin(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $game = Mockery::mock(Game::class);
        $gameSession = Mockery::mock(GameSession::class);
        
        $gameData = [
            'betAmount' => 10.0,
            'activePaylines' => [0, 1, 2]
        ];

        $spinResult = new \App\DTOs\ReelVisibilityDto(
            positions: [0, 1, 2],
            symbols: [['A', 'B', 'C'], ['A', 'A', 'A'], ['B', 'C', 'A']]
        );

        $payoutResult = [
            'betAmount' => 10.0,
            'totalPayout' => 50.0,
            'winningLines' => [['line' => 1, 'payout' => 50.0]],
            'isJackpot' => false,
            'multiplier' => 1.0,
            'freeSpinsAwarded' => 0,
            'scatterResult' => [],
            'wildPositions' => []
        ];

        // Mock expectations
        $this->betValidator->shouldReceive('validate')
            ->once()
            ->with($game, $user, 10.0);

        $this->gameSessionManager->shouldReceive('getOrCreateUserSession')
            ->once()
            ->with($user, $game)
            ->andReturn($gameSession);

        $this->reelGenerator->shouldReceive('getVisibleSymbols')
            ->once()
            ->with($game)
            ->andReturn($spinResult);

        $this->payoutCalculator->shouldReceive('calculatePayout')
            ->once()
            ->with($game, $spinResult->symbols, 10.0, [0, 1, 2])
            ->andReturn($payoutResult);

        $this->transactionManager->shouldReceive('processGameTransaction')
            ->once()
            ->with($user, $gameSession, 10.0, 50.0)
            ->andReturn(1040.0); // New balance

        $this->gameLogger->shouldReceive('logGameRound')
            ->once()
            ->with($gameSession, $payoutResult, 10.0, $spinResult->symbols);

        // Act
        $result = $this->strategy->execute($user, $game, $gameData);

        // Assert
        $this->assertInstanceOf(GameResultDto::class, $result);
        $this->assertEquals('slot', $result->gameType);
        $this->assertEquals(10.0, $result->betAmount);
        $this->assertEquals(50.0, $result->winAmount);
        $this->assertEquals(1040.0, $result->newBalance);
        
        $gameData = $result->gameData;
        $this->assertEquals(10.0, $gameData->betAmount);
        $this->assertEquals(50.0, $gameData->winAmount);
        $this->assertEquals($spinResult->positions, $gameData->reelPositions);
        $this->assertEquals($spinResult->symbols, $gameData->visibleSymbols);
        $this->assertEquals($payoutResult['winningLines'], $gameData->winningLines);
    }

    public function testExecuteWithDefaultPaylines(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $game = Mockery::mock(Game::class);
        $gameSession = Mockery::mock(GameSession::class);
        
        $gameData = ['betAmount' => 5.0]; // No activePaylines specified

        $spinResult = new \App\DTOs\ReelVisibilityDto(
            positions: [0],
            symbols: [['A', 'B', 'C']]
        );

        $payoutResult = [
            'betAmount' => 5.0,
            'totalPayout' => 0.0,
            'winningLines' => [],
            'isJackpot' => false,
            'multiplier' => 1.0,
            'freeSpinsAwarded' => 0,
            'scatterResult' => [],
            'wildPositions' => []
        ];

        // Mock expectations
        $this->betValidator->shouldReceive('validate')->once();
        $this->gameSessionManager->shouldReceive('getOrCreateUserSession')->once()->andReturn($gameSession);
        $this->reelGenerator->shouldReceive('getVisibleSymbols')->once()->andReturn($spinResult);
        
        $this->payoutCalculator->shouldReceive('calculatePayout')
            ->once()
            ->with($game, $spinResult->symbols, 5.0, [0]) // Default paylines
            ->andReturn($payoutResult);
            
        $this->transactionManager->shouldReceive('processGameTransaction')->once()->andReturn(995.0);
        $this->gameLogger->shouldReceive('logGameRound')->once();

        // Act
        $result = $this->strategy->execute($user, $game, $gameData);

        // Assert
        $this->assertInstanceOf(GameResultDto::class, $result);
        $this->assertEquals(5.0, $result->betAmount);
        $this->assertEquals(0.0, $result->winAmount);
    }
}

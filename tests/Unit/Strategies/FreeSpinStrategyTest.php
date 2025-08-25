<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Contracts\GameLoggerInterface;
use App\Contracts\PayoutCalculatorInterface;
use App\Contracts\ReelGeneratorInterface;
use App\DTOs\GameResultDto;
use App\Exceptions\InsufficientFreeSpinsException;
use App\Managers\FreeSpinManager;
use App\Managers\GameSessionManager;
use App\Models\FreeSpin;
use App\Models\FreeSpinTransaction;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use App\Strategies\FreeSpinStrategy;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class FreeSpinStrategyTest extends TestCase
{
    private FreeSpinStrategy $strategy;
    private ReelGeneratorInterface $reelGenerator;
    private PayoutCalculatorInterface $payoutCalculator;
    private GameLoggerInterface $gameLogger;
    private GameSessionManager $gameSessionManager;
    private FreeSpinManager $freeSpinManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reelGenerator = Mockery::mock(ReelGeneratorInterface::class);
        $this->payoutCalculator = Mockery::mock(PayoutCalculatorInterface::class);
        $this->gameLogger = Mockery::mock(GameLoggerInterface::class);
        $this->gameSessionManager = Mockery::mock(GameSessionManager::class);
        $this->freeSpinManager = Mockery::mock(FreeSpinManager::class);

        $this->strategy = new FreeSpinStrategy(
            $this->reelGenerator,
            $this->payoutCalculator,
            $this->gameLogger,
            $this->gameSessionManager,
            $this->freeSpinManager
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

    public function testExecuteThrowsExceptionWhenNoFreeSpinsAvailable(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $game = Mockery::mock(Game::class);
        $game->shouldIgnoreMissing();
        $game->id = 'game-123';
        
        $gameData = ['useFreeSpins' => true];

        $this->freeSpinManager->shouldReceive('getAvailableFreeSpins')
            ->once()
            ->with($user, Mockery::any())
            ->andReturn(0);

        // Act & Assert
        $this->expectException(InsufficientFreeSpinsException::class);
        $this->expectExceptionMessage('No free spins available for this game');
        
        $this->strategy->execute($user, $game, $gameData);
    }

    public function testExecuteSuccessfulFreeSpin(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldIgnoreMissing();
        $user->balance = 1000.0;
        $user->shouldReceive('refresh')->once();
        
        $game = Mockery::mock(Game::class);
        $game->shouldIgnoreMissing();
        $game->shouldReceive('getAttribute')->with('id')->andReturn('game-123');
        $game->shouldReceive('getAttribute')->with('min_bet')->andReturn(1.0);
        $game->id = 'game-123';
        $game->min_bet = 1.0;
        
        $gameSession = Mockery::mock(GameSession::class);
        
        $freeSpin = Mockery::mock(FreeSpin::class);
        $freeSpin->shouldIgnoreMissing();
        $freeSpin->bet_value = 5.0;
        
        $freeSpinTransaction = Mockery::mock(FreeSpinTransaction::class);
        
        $gameData = [
            'useFreeSpins' => true,
            'activePaylines' => [0, 1]
        ];

        $spinResult = new \App\DTOs\ReelVisibilityDto(
            positions: [0, 1, 2],
            symbols: [['A', 'A', 'A'], ['B', 'B', 'B'], ['C', 'C', 'C']]
        );

        $payoutResult = [
            'betAmount' => 5.0,
            'totalPayout' => 25.0,
            'winningLines' => [['line' => 0, 'payout' => 25.0]],
            'isJackpot' => false,
            'multiplier' => 1.0,
            'freeSpinsAwarded' => 2,
            'scatterResult' => [],
            'wildPositions' => []
        ];

        // Mock expectations
        $this->freeSpinManager->shouldReceive('getAvailableFreeSpins')
            ->once()
            ->with($user, Mockery::any())
            ->andReturn(3);

        $this->gameSessionManager->shouldReceive('getOrCreateUserSession')
            ->once()
            ->with($user, $game)
            ->andReturn($gameSession);

        $this->reelGenerator->shouldReceive('getVisibleSymbols')
            ->once()
            ->with($game)
            ->andReturn($spinResult);

        $this->freeSpinManager->shouldReceive('getUserFreeSpins')
            ->once()
            ->with($user, Mockery::any())
            ->andReturn(new Collection([$freeSpin]));

        $this->payoutCalculator->shouldReceive('calculatePayout')
            ->once()
            ->with($game, $spinResult->symbols, 5.0, [0, 1])
            ->andReturn($payoutResult);

        $this->freeSpinManager->shouldReceive('useFreeSpin')
            ->once()
            ->with(
                $user,
                Mockery::any(),
                [
                    'reelPositions' => $spinResult->positions,
                    'visibleSymbols' => $spinResult->symbols,
                    'winningLines' => $payoutResult['winningLines'],
                ],
                25.0
            )
            ->andReturn($freeSpinTransaction);

        $this->gameLogger->shouldReceive('logGameRound')
            ->once()
            ->with($gameSession, $payoutResult, 5.0, $spinResult->symbols);

        // Act
        $result = $this->strategy->execute($user, $game, $gameData);

        // Assert
        $this->assertInstanceOf(GameResultDto::class, $result);
        $this->assertEquals('slot', $result->gameType);
        $this->assertEquals(0, $result->betAmount); // No bet amount for free spins
        $this->assertEquals(25.0, $result->winAmount);
        $this->assertEquals(1000.0, $result->newBalance);
        
        $gameData = $result->gameData;
        $this->assertEquals(5.0, $gameData->betAmount); // Bet amount from free spin record
        $this->assertEquals(25.0, $gameData->winAmount);
        $this->assertEquals($spinResult->positions, $gameData->reelPositions);
        $this->assertEquals($spinResult->symbols, $gameData->visibleSymbols);
        $this->assertEquals(2, $gameData->freeSpinsAwarded);
    }

    public function testExecuteWithDefaultBetValueWhenFreeSpinHasNone(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldIgnoreMissing();
        $user->balance = 500.0;
        $user->shouldReceive('refresh')->once();
        
        $game = Mockery::mock(Game::class);
        $game->shouldIgnoreMissing();
        $game->shouldReceive('getAttribute')->with('id')->andReturn('game-456');
        $game->shouldReceive('getAttribute')->with('min_bet')->andReturn(2.0);
        $game->id = 'game-456';
        $game->min_bet = 2.0;
        
        $gameSession = Mockery::mock(GameSession::class);
        
        $freeSpin = Mockery::mock(FreeSpin::class);
        $freeSpin->shouldIgnoreMissing();
        $freeSpin->bet_value = null; // No bet value set
        
        $freeSpinTransaction = Mockery::mock(FreeSpinTransaction::class);
        
        $gameData = ['useFreeSpins' => true];

        $spinResult = new \App\DTOs\ReelVisibilityDto(
            positions: [0],
            symbols: [['A', 'B', 'C']]
        );

        $payoutResult = [
            'betAmount' => 2.0, // Should use game's min_bet
            'totalPayout' => 0.0,
            'winningLines' => [],
            'isJackpot' => false,
            'multiplier' => 1.0,
            'freeSpinsAwarded' => 0,
            'scatterResult' => [],
            'wildPositions' => []
        ];

        // Mock expectations
        $this->freeSpinManager->shouldReceive('getAvailableFreeSpins')->once()->with($user, Mockery::any())->andReturn(1);
        $this->gameSessionManager->shouldReceive('getOrCreateUserSession')->once()->andReturn($gameSession);
        $this->reelGenerator->shouldReceive('getVisibleSymbols')->once()->andReturn($spinResult);
        $this->freeSpinManager->shouldReceive('getUserFreeSpins')->once()->with($user, Mockery::any())->andReturn(new Collection([$freeSpin]));
        
        $this->payoutCalculator->shouldReceive('calculatePayout')
            ->once()
            ->with($game, $spinResult->symbols, 2.0, [0]) // Should use min_bet
            ->andReturn($payoutResult);
            
        $this->freeSpinManager->shouldReceive('useFreeSpin')->once()->with($user, Mockery::any(), Mockery::any(), 0.0)->andReturn($freeSpinTransaction);
        $this->gameLogger->shouldReceive('logGameRound')->once();

        // Act
        $result = $this->strategy->execute($user, $game, $gameData);

        // Assert
        $this->assertEquals(0, $result->betAmount); // No bet for free spins
        $this->assertEquals(2.0, $result->gameData->betAmount); // But game data shows the actual bet value used
    }
}
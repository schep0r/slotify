<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Engines\SlotGameEngine;
use App\Exceptions\InsufficientFreeSpinsException;
use App\Managers\FreeSpinManager;
use App\Models\FreeSpin;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotGameEngineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private SlotGameEngine $engine;
    private FreeSpinManager $freeSpinManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->engine = app(SlotGameEngine::class);
        $this->freeSpinManager = app(FreeSpinManager::class);
    }

    public function testBetSpinFlow(): void
    {
        // Arrange
        $user = User::factory()->create(['balance' => 1000.0]);
        $game = Game::factory()->create([
            'min_bet' => 1.0,
            'max_bet' => 100.0,
            'is_active' => true
        ]);

        $gameData = [
            'betAmount' => 10.0,
            'activePaylines' => [0],
            'useFreeSpins' => false
        ];

        // Act
        $result = $this->engine->play($user, $game, $gameData);

        // Assert
        $this->assertEquals('slot', $result->gameType);
        $this->assertEquals(10.0, $result->betAmount);
        $this->assertGreaterThanOrEqual(0, $result->winAmount);
        $this->assertLessThan(1000.0, $result->newBalance); // Balance should be reduced by bet
        
        // Verify user balance was updated
        $user->refresh();
        $this->assertEquals($result->newBalance, $user->balance);
        
        // Verify game data structure
        $gameData = $result->gameData;
        $this->assertIsArray($gameData->reelPositions);
        $this->assertIsArray($gameData->visibleSymbols);
        $this->assertIsArray($gameData->winningLines);
        $this->assertIsBool($gameData->isJackpot);
        $this->assertIsFloat($gameData->multiplier);
        $this->assertIsInt($gameData->freeSpinsAwarded);
    }

    public function testFreeSpinFlowWithAvailableSpins(): void
    {
        // Arrange
        $user = User::factory()->create(['balance' => 500.0]);
        $game = Game::factory()->create([
            'min_bet' => 1.0,
            'max_bet' => 100.0,
            'is_active' => true
        ]);

        // Award free spins to user
        $this->freeSpinManager->awardFreeSpins(
            $user,
            5, // amount
            'bonus',
            5.0, // bet value
            $game->id // game restriction
        );

        $gameData = [
            'useFreeSpins' => true,
            'activePaylines' => [0]
        ];

        // Act
        $result = $this->engine->play($user, $game, $gameData);

        // Assert
        $this->assertEquals('slot', $result->gameType);
        $this->assertEquals(0, $result->betAmount); // No bet amount for free spins
        $this->assertGreaterThanOrEqual(0, $result->winAmount);
        $this->assertGreaterThanOrEqual(500.0, $result->newBalance); // Balance should not decrease
        
        // Verify free spin was consumed
        $remainingSpins = $this->freeSpinManager->getAvailableFreeSpins($user, $game->id);
        $this->assertEquals(4, $remainingSpins);
        
        // Verify game data shows the free spin bet value
        $gameData = $result->gameData;
        $this->assertEquals(5.0, $gameData->betAmount); // Should use free spin bet value
    }

    public function testFreeSpinFlowWithoutAvailableSpins(): void
    {
        // Arrange
        $user = User::factory()->create(['balance' => 500.0]);
        $game = Game::factory()->create([
            'min_bet' => 1.0,
            'max_bet' => 100.0,
            'is_active' => true
        ]);

        $gameData = [
            'useFreeSpins' => true,
            'activePaylines' => [0]
        ];

        // Act & Assert
        $this->expectException(InsufficientFreeSpinsException::class);
        $this->expectExceptionMessage('No free spins available for this game');
        
        $this->engine->play($user, $game, $gameData);
    }

    public function testFreeSpinWithWinnings(): void
    {
        // Arrange
        $user = User::factory()->create(['balance' => 100.0]);
        $game = Game::factory()->create([
            'min_bet' => 1.0,
            'max_bet' => 100.0,
            'is_active' => true
        ]);

        // Award free spins
        $this->freeSpinManager->awardFreeSpins(
            $user,
            1,
            'bonus',
            10.0,
            $game->id
        );

        $initialBalance = $user->balance;
        
        $gameData = [
            'useFreeSpins' => true,
            'activePaylines' => [0]
        ];

        // Act
        $result = $this->engine->play($user, $game, $gameData);

        // Assert
        $user->refresh();
        
        // If there were winnings, balance should increase
        if ($result->winAmount > 0) {
            $this->assertEquals($initialBalance + $result->winAmount, $user->balance);
        } else {
            // If no winnings, balance should remain the same
            $this->assertEquals($initialBalance, $user->balance);
        }
        
        // Free spin should be consumed regardless of outcome
        $remainingSpins = $this->freeSpinManager->getAvailableFreeSpins($user, $game->id);
        $this->assertEquals(0, $remainingSpins);
    }

    public function testStrategySelectionBasedOnGameData(): void
    {
        // Arrange
        $user = User::factory()->create(['balance' => 1000.0]);
        $game = Game::factory()->create();

        // Test bet spin strategy selection
        $betGameData = ['betAmount' => 5.0];
        $betResult = $this->engine->play($user, $game, $betGameData);
        $this->assertEquals(5.0, $betResult->betAmount);

        // Award free spins for free spin test
        $this->freeSpinManager->awardFreeSpins($user, 1, 'bonus', 3.0, $game->id);

        // Test free spin strategy selection
        $freeSpinGameData = ['useFreeSpins' => true];
        $freeSpinResult = $this->engine->play($user, $game, $freeSpinGameData);
        $this->assertEquals(0, $freeSpinResult->betAmount); // No bet for free spins
        $this->assertEquals(3.0, $freeSpinResult->gameData->betAmount); // But shows free spin bet value
    }

    public function testMultipleFreeSpinsConsumption(): void
    {
        // Arrange
        $user = User::factory()->create(['balance' => 200.0]);
        $game = Game::factory()->create();

        // Award multiple free spins
        $this->freeSpinManager->awardFreeSpins($user, 3, 'bonus', 2.0, $game->id);
        
        $gameData = ['useFreeSpins' => true];

        // Act - Use all free spins
        $this->engine->play($user, $game, $gameData);
        $this->assertEquals(2, $this->freeSpinManager->getAvailableFreeSpins($user, $game->id));

        $this->engine->play($user, $game, $gameData);
        $this->assertEquals(1, $this->freeSpinManager->getAvailableFreeSpins($user, $game->id));

        $this->engine->play($user, $game, $gameData);
        $this->assertEquals(0, $this->freeSpinManager->getAvailableFreeSpins($user, $game->id));

        // Assert - Next attempt should fail
        $this->expectException(InsufficientFreeSpinsException::class);
        $this->engine->play($user, $game, $gameData);
    }
}
<?php

namespace Tests\Feature;

use App\DTOs\GameResultDto;
use App\DTOs\RouletteGameDataDto;
use App\Enums\GameType;
use App\Factories\GameEngineFactory;
use App\Models\Game;
use App\Models\RouletteConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalGameEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GameEngineFactory $gameEngineFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['balance' => 1000.00]);
        $this->gameEngineFactory = app(GameEngineFactory::class);
    }

    public function test_can_create_slot_game_engine()
    {
        $game = Game::factory()->create(['type' => GameType::SLOT->value]);

        $engine = $this->gameEngineFactory->createForGame($game);

        $this->assertInstanceOf(\App\Engines\SlotGameEngine::class, $engine);
        $this->assertEquals(GameType::SLOT->value, $engine->getGameType());
    }

    public function test_can_create_roulette_game_engine()
    {
        $game = Game::factory()->create(['type' => GameType::ROULETTE->value]);

        $engine = $this->gameEngineFactory->createForGame($game);

        $this->assertInstanceOf(\App\Engines\RouletteGameEngine::class, $engine);
        $this->assertEquals(GameType::ROULETTE->value, $engine->getGameType());
    }

    public function test_can_play_roulette_game()
    {
        $game = Game::factory()->create([
            'type' => GameType::ROULETTE->value,
            'min_bet' => 1.00,
            'max_bet' => 1000.00,
        ]);

        RouletteConfiguration::factory()->create([
            'game_id' => $game->id,
            'wheel_type' => 'european',
            'table_limits' => RouletteConfiguration::getDefaultTableLimits(),
        ]);

        $engine = $this->gameEngineFactory->createForGame($game);

        $gameData = [
            'bets' => [
                [
                    'type' => 'red',
                    'amount' => 10.00,
                ],
                [
                    'type' => 'straight',
                    'amount' => 5.00,
                    'numbers' => [7],
                ]
            ]
        ];

        $result = $engine->play($this->user, $game, $gameData);

        $this->assertInstanceOf(\App\DTOs\GameResultDto::class, $result);
        $this->assertEquals(GameType::ROULETTE->value, $result->gameType);
        $this->assertEquals(15.00, $result->betAmount); // 10 + 5
        $this->assertInstanceOf(\App\DTOs\RouletteGameDataDto::class, $result->gameData);
        $this->assertIsInt($result->gameData->winningNumber);
        $this->assertIsArray($result->gameData->bets);
    }

    public function test_roulette_game_validates_bet_limits()
    {
        $game = Game::factory()->create([
            'type' => GameType::ROULETTE->value,
            'min_bet' => 1.00,
            'max_bet' => 100.00,
        ]);

        RouletteConfiguration::factory()->create([
            'game_id' => $game->id,
            'table_limits' => [
                'red' => ['min' => 1.00, 'max' => 50.00],
            ],
        ]);

        $engine = $this->gameEngineFactory->createForGame($game);

        $gameData = [
            'bets' => [
                [
                    'type' => 'red',
                    'amount' => 100.00, // Exceeds table limit
                ]
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        $engine->play($this->user, $game, $gameData);
    }

    public function test_can_get_available_game_types()
    {
        $engines = $this->gameEngineFactory->getAvailableEngines();

        $this->assertArrayHasKey(GameType::SLOT->value, $engines);
        $this->assertArrayHasKey(GameType::ROULETTE->value, $engines);
    }

    public function test_game_type_support_check()
    {
        $this->assertTrue($this->gameEngineFactory->isGameTypeSupported(GameType::SLOT));
        $this->assertTrue($this->gameEngineFactory->isGameTypeSupported(GameType::ROULETTE));
    }
}

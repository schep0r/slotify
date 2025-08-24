<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\GameEngineInterface;
use App\Enums\GameType;
use App\Models\Game;
use Illuminate\Container\Container;
use InvalidArgumentException;

class GameEngineFactory
{
    public function __construct(private Container $container)
    {
    }

    /**
     * Create game engine for specific game
     */
    public function createForGame(Game $game): GameEngineInterface
    {
        return $this->createForGameType(GameType::from($game->type->value));
    }

    /**
     * Create game engine for specific game type
     */
    public function createForGameType(GameType $gameType): GameEngineInterface
    {
        $engineClass = $gameType->getEngineClass();

        if (!class_exists($engineClass)) {
            throw new InvalidArgumentException("Game engine class {$engineClass} does not exist");
        }

        $engine = $this->container->make($engineClass);

        if (!$engine instanceof GameEngineInterface) {
            throw new InvalidArgumentException("Class {$engineClass} must implement GameEngineInterface");
        }

        return $engine;
    }

    /**
     * Get all available game engines
     */
    public function getAvailableEngines(): array
    {
        $engines = [];

        foreach (GameType::cases() as $gameType) {
            $engineClass = $gameType->getEngineClass();

            if (class_exists($engineClass)) {
                $engines[$gameType->value] = $this->container->make($engineClass);
            }
        }

        return $engines;
    }

    /**
     * Check if game type is supported
     */
    public function isGameTypeSupported(GameType $gameType): bool
    {
        $engineClass = $gameType->getEngineClass();
        return class_exists($engineClass);
    }
}

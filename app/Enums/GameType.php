<?php

declare(strict_types=1);

namespace App\Enums;

use App\Engines\RouletteGameEngine;
use App\Engines\SlotGameEngine;
use App\Models\RouletteConfiguration;
use App\Models\SlotConfiguration;

enum GameType: string
{
    case SLOT = 'slot';
    case ROULETTE = 'roulette';
    case BLACKJACK = 'blackjack';
    case POKER = 'poker';
    case BACCARAT = 'baccarat';

    public function getDisplayName(): string
    {
        return match($this) {
            self::SLOT => 'Slot Machine',
            self::ROULETTE => 'Roulette',
            self::BLACKJACK => 'Blackjack',
            self::POKER => 'Poker',
            self::BACCARAT => 'Baccarat',
        };
    }

    public function getEngineClass(): string
    {
        return match($this) {
            self::SLOT => SlotGameEngine::class,
            self::ROULETTE => RouletteGameEngine::class,
            default => throw new \InvalidArgumentException("Game engine not implemented for type: {$this->value}"),
        };
    }

    public function getConfigurationModel(): string
    {
        return match($this) {
            self::SLOT => SlotConfiguration::class,
            self::ROULETTE => RouletteConfiguration::class,
            default => throw new \InvalidArgumentException("Configuration model not implemented for type: {$this->value}"),
        };
    }

    public static function getAvailableTypes(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

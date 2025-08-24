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
//            self::BLACKJACK => \App\Services\Games\BlackjackGameEngine::class,
//            self::POKER => \App\Services\Games\PokerGameEngine::class,
//            self::BACCARAT => \App\Services\Games\BaccaratGameEngine::class,
        };
    }

    public function getConfigurationModel(): string
    {
        return match($this) {
            self::SLOT => SlotConfiguration::class,
            self::ROULETTE => RouletteConfiguration::class,
//            self::BLACKJACK => \App\Models\BlackjackConfiguration::class,
//            self::POKER => \App\Models\PokerConfiguration::class,
//            self::BACCARAT => \App\Models\BaccaratConfiguration::class,
        };
    }

    public static function getAvailableTypes(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

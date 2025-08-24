<?php

declare(strict_types=1);

namespace App\Services\Games\Roulette;

use App\Contracts\RandomNumberGeneratorInterface;
use App\Models\Game;

class RouletteWheelGenerator
{
    private const EUROPEAN_WHEEL = [
        0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5,
        24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26
    ];

    private const AMERICAN_WHEEL = [
        0, 28, 9, 26, 30, 11, 7, 20, 32, 17, 5, 22, 34, 15, 3, 24, 36, 13, 1, 00,
        27, 10, 25, 29, 12, 8, 19, 31, 18, 6, 21, 33, 16, 4, 23, 35, 14, 2
    ];

    public function __construct(private RandomNumberGeneratorInterface $rng)
    {
    }

    public function spin(Game $game): int
    {
        $config = $game->getGameConfiguration();
        $wheelType = $config->wheel_type ?? 'european';

        $wheel = $wheelType === 'american' ? self::AMERICAN_WHEEL : self::EUROPEAN_WHEEL;
        $position = $this->rng->generateInt(0, count($wheel) - 1);

        return $wheel[$position];
    }

    public function getWheelNumbers(string $wheelType = 'european'): array
    {
        return $wheelType === 'american' ? self::AMERICAN_WHEEL : self::EUROPEAN_WHEEL;
    }

    public function getMaxNumber(string $wheelType = 'european'): int
    {
        return $wheelType === 'american' ? 37 : 36; // 00 counts as 37 in American roulette
    }
}
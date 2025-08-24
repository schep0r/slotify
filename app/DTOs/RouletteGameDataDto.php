<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Roulette-specific game data DTO containing all roulette result information.
 */
readonly class RouletteGameDataDto
{
    /**
     * @param int $winningNumber
     * @param array<int, RouletteBetResultDto> $bets
     * @param string $wheelType
     */
    public function __construct(
        public int $winningNumber,
        public array $bets,
        public string $wheelType,
    ) {
    }

    /**
     * Convert the DTO to an array
     */
    public function toArray(): array
    {
        return [
            'winningNumber' => $this->winningNumber,
            'bets' => array_map(fn(RouletteBetResultDto $bet) => $bet->toArray(), $this->bets),
            'wheelType' => $this->wheelType,
        ];
    }
}
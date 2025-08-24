<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Individual roulette bet result DTO.
 */
readonly class RouletteBetResultDto
{
    /**
     * @param string $type
     * @param float $amount
     * @param int[] $numbers
     * @param float $payout
     * @param bool $won
     */
    public function __construct(
        public string $type,
        public float $amount,
        public array $numbers,
        public float $payout,
        public bool $won,
    ) {
    }

    /**
     * Convert the DTO to an array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'numbers' => $this->numbers,
            'payout' => $this->payout,
            'won' => $this->won,
        ];
    }
}
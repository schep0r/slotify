<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\GameResultDto;
use App\Models\Game;
use App\Models\User;

interface GameEngineInterface
{
    /**
     * Play a game round
     */
    public function play(User $user, Game $game, array $gameData): GameResultDto;

    /**
     * Validate game input
     */
    public function validateInput(array $gameData, Game $game, User $user): void;

    /**
     * Get game type this engine handles
     */
    public function getGameType(): string;

    /**
     * Get required input parameters for this game type
     */
    public function getRequiredInputs(): array;

    /**
     * Get game-specific configuration requirements
     */
    public function getConfigurationRequirements(): array;
}

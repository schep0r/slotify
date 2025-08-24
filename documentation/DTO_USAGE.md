# DTO Usage Guide

This document explains how to use the new Data Transfer Objects (DTOs) that replace array returns in the game engine system.

## Overview

The game engines now return structured DTOs instead of arrays, providing better type safety and clearer data contracts.

## Main DTOs

### GameResultDto

The main response DTO returned by all game engines' `play()` method:

```php
use App\DTOs\GameResultDto;

// Example usage in a controller
public function play(PlayGameRequest $request, Game $game)
{
    $user = auth()->user();
    $result = $this->gameProcessor->process($game, $user, $request);
    
    // $result is now an array (converted from DTO in GameProcessor)
    return response()->json($result);
}
```

### SlotGameDataDto

Contains slot-specific game data:

```php
use App\DTOs\SlotGameDataDto;

$slotData = new SlotGameDataDto(
    betAmount: 10.00,
    winAmount: 25.50,
    reelPositions: [0, 5, 10, 15, 20],
    visibleSymbols: [
        ['A', 'K', 'Q'],
        ['K', 'Q', 'J'],
        ['Q', 'J', '10'],
        ['J', '10', '9'],
        ['10', '9', 'A']
    ],
    winningLines: [
        [
            'payline' => 0,
            'symbols' => ['A', 'A', 'A'],
            'count' => 3,
            'payout' => 25.50,
            'symbol' => 'A'
        ]
    ],
    isJackpot: false,
    multiplier: 1.0,
    freeSpinsAwarded: 0,
    scatterResult: [],
    wildPositions: []
);
```

### RouletteGameDataDto

Contains roulette-specific game data:

```php
use App\DTOs\RouletteGameDataDto;
use App\DTOs\RouletteBetResultDto;

$betResults = [
    new RouletteBetResultDto(
        type: 'red',
        amount: 10.00,
        numbers: [],
        payout: 20.00,
        won: true
    ),
    new RouletteBetResultDto(
        type: 'straight',
        amount: 5.00,
        numbers: [7],
        payout: 0.00,
        won: false
    )
];

$rouletteData = new RouletteGameDataDto(
    winningNumber: 18,
    bets: $betResults,
    wheelType: 'european'
);
```

## Game Engine Implementation

### Before (Array Return)
```php
public function play(User $user, Game $game, array $gameData): array
{
    // ... game logic ...
    
    return [
        'gameType' => 'slot',
        'betAmount' => 10.00,
        'winAmount' => 25.50,
        'newBalance' => 1015.50,
        'gameData' => [
            'reelPositions' => [0, 5, 10, 15, 20],
            'visibleSymbols' => [...],
            // ... more data
        ]
    ];
}
```

### After (DTO Return)
```php
public function play(User $user, Game $game, array $gameData): GameResultDto
{
    // ... game logic ...
    
    $slotGameData = new SlotGameDataDto(
        betAmount: $betAmount,
        winAmount: $totalPayout,
        reelPositions: $reelPositions,
        visibleSymbols: $visibleSymbols,
        winningLines: $winningLines,
        // ... other parameters
    );

    return new GameResultDto(
        gameType: $this->getGameType(),
        betAmount: $betAmount,
        winAmount: $totalPayout,
        newBalance: $newBalance,
        gameData: $slotGameData
    );
}
```

## Benefits

1. **Type Safety**: IDEs can provide better autocomplete and type checking
2. **Clear Contracts**: DTOs make it explicit what data is expected
3. **Immutability**: DTOs are readonly, preventing accidental modifications
4. **Consistency**: All game engines return the same structure
5. **Documentation**: DTOs serve as living documentation of the data structure

## API Response

The DTOs are automatically converted to arrays for JSON responses:

```json
{
    "gameType": "slot",
    "betAmount": 10.00,
    "winAmount": 25.50,
    "newBalance": 1015.50,
    "gameData": {
        "betAmount": 10.00,
        "winAmount": 25.50,
        "gameData": {
            "reelPositions": [0, 5, 10, 15, 20],
            "visibleSymbols": [...],
            "winningLines": [...],
            "isJackpot": false,
            "multiplier": 1.0,
            "freeSpinsAwarded": 0,
            "scatterResult": [],
            "wildPositions": []
        }
    }
}
```

## Testing

When testing game engines, you can now assert on DTO properties:

```php
$result = $engine->play($user, $game, $gameData);

$this->assertInstanceOf(GameResultDto::class, $result);
$this->assertEquals('roulette', $result->gameType);
$this->assertEquals(15.00, $result->betAmount);
$this->assertInstanceOf(RouletteGameDataDto::class, $result->gameData);
```
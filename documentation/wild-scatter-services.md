# Wild and Scatter Result Services

## Overview

The game engine has been refactored to use dedicated services for handling wild and scatter symbol logic. This separation of concerns improves maintainability, testability, and allows for more sophisticated wild and scatter mechanics.

## New Services

### WildResultService

Handles all wild symbol-related logic:

- **calculateWildMultiplier()**: Calculates multiplier based on wild symbol count
- **findBestWildSubstitute()**: Determines the best symbol for wild substitution
- **processWildSubstitutions()**: Processes wild substitutions in paylines
- **getWildPositions()**: Returns positions of all wild symbols
- **calculateWildContribution()**: Calculates wild contribution to specific paylines

### ScatterResultService

Handles all scatter symbol-related logic:

- **checkScatterBonus()**: Main method for scatter bonus calculation
- **countScatterSymbols()**: Counts scatter symbols across all reels
- **getScatterPositions()**: Returns positions of all scatter symbols
- **calculateScatterPayout()**: Calculates payout based on scatter count
- **calculateFreeSpins()**: Determines free spins awarded
- **checkSpecialScatterFeatures()**: Checks for special scatter-triggered features

## Updated Workflow

### 1. Game Engine (GameEngine.php)
- Remains the main orchestrator
- Calls PayoutCalculator with enhanced result handling
- Returns additional wild and scatter information

### 2. Payout Calculator (PayoutCalculator.php)
- Now uses WildResultService and ScatterResultService
- Provides more detailed results including:
  - Wild positions
  - Scatter result details
  - Enhanced wild multiplier calculations

### 3. Service Integration
- Services are registered in AppServiceProvider
- Dependency injection ensures proper service availability
- Services can be easily mocked for testing

## Enhanced Result Structure

The game engine now returns additional information:

```php
[
    'reelPositions' => [...],
    'visibleSymbols' => [...],
    'winningLines' => [...],
    'totalPayout' => 0.00,
    'newBalance' => 0.00,
    'isJackpot' => false,
    'multiplier' => 1,
    'freeSpinsAwarded' => 0,
    'scatterResult' => [
        'payout' => 0.00,
        'freeSpins' => 0,
        'scatterCount' => 0,
        'positions' => [...],
        'isScatterWin' => false
    ],
    'wildPositions' => [
        ['reel' => 0, 'row' => 1],
        // ...
    ]
]
```

## Benefits

1. **Separation of Concerns**: Wild and scatter logic is isolated
2. **Testability**: Each service can be unit tested independently
3. **Extensibility**: Easy to add new wild/scatter mechanics
4. **Maintainability**: Changes to wild/scatter logic don't affect other components
5. **Reusability**: Services can be used by other game components

## Usage Examples

### Wild Service Usage
```php
$wildService = app(WildResultService::class);
$multiplier = $wildService->calculateWildMultiplier($visibleSymbols);
$positions = $wildService->getWildPositions($visibleSymbols);
```

### Scatter Service Usage
```php
$scatterService = app(ScatterResultService::class);
$result = $scatterService->checkScatterBonus($visibleSymbols, $betAmount);
$features = $scatterService->checkSpecialScatterFeatures($visibleSymbols);
```

## Testing

Unit tests are provided for both services:
- `tests/Unit/WildResultServiceTest.php`
- `tests/Unit/ScatterResultServiceTest.php`

Run tests with:
```bash
php artisan test tests/Unit/WildResultServiceTest.php
php artisan test tests/Unit/ScatterResultServiceTest.php
```

## Future Enhancements

The modular design allows for easy addition of:
- Progressive wild multipliers
- Expanding wilds
- Sticky wilds
- Complex scatter patterns
- Multi-level scatter bonuses
- Wild-scatter interactions
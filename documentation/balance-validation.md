# Balance Validation Architecture

## Overview

User balance validation has been centralized into a single middleware (`BalanceCheckMiddleware`) to eliminate code duplication and ensure consistent behavior across the application.

## Previous Architecture Issues

Before this refactoring, balance validation was scattered across multiple layers:

1. **BalanceCheckMiddleware** - Basic middleware check
2. **BetValidator** - Service layer validation
3. **PlayGameRequest** - Form request validation

This created:
- Code duplication
- Inconsistent error messages
- Multiple points of failure
- Maintenance overhead

## New Centralized Architecture

### BalanceCheckMiddleware

**Location**: `app/Http/Middleware/BalanceCheckMiddleware.php`

**Responsibilities**:
- Validates user balance before any game action that requires betting
- Supports multiple bet amount field names (`betAmount`, `bet_amount`, `amount`, `totalBet`)
- Handles array of bets (sums total amount)
- Provides consistent error responses
- Only applies to game-related endpoints

**Supported Route Patterns**:
- `api/*/game/*/play`
- `api/*/game/spin`
- `api/*/slot/*/spin`
- `api/games/*/play`

### Route Configuration

The middleware is applied selectively to routes that require balance validation:

```php
// Game actions that require balance check
Route::middleware(['balance.check', 'spin.rate.limit'])->group(function () {
    Route::post('/spin', [GameController::class, 'spin']);
    Route::post('/{game}/play', [UniversalGameController::class, 'play']);
});
```

### Error Response Format

```json
{
    "error": "Insufficient balance",
    "code": "INSUFFICIENT_BALANCE",
    "required": 10.00,
    "available": 5.00
}
```

## Migration Notes

### BetValidator Changes

The `BetValidator` class still exists but:
- No longer performs balance validation in the main `validate()` method
- The `validateBalance()` method is marked as deprecated
- Focus is now purely on bet amount validation against game limits

### Form Request Changes

The `PlayGameRequest` class:
- No longer includes balance validation in `withValidator()`
- Focuses on input validation and game-specific rules
- Relies on middleware for balance checking

## Benefits

1. **Single Source of Truth**: Balance validation logic exists in one place
2. **Consistent Behavior**: All endpoints use the same validation logic
3. **Better Error Handling**: Standardized error responses
4. **Easier Maintenance**: Changes only need to be made in one location
5. **Performance**: Validation happens early in the request lifecycle
6. **Flexibility**: Easy to add new route patterns or bet amount field names

## Testing

Comprehensive tests are available in `tests/Feature/BalanceCheckMiddlewareTest.php` covering:
- Sufficient balance scenarios
- Insufficient balance scenarios
- Non-game route bypassing
- Multiple bet amount field names
- Array of bets handling

## Future Considerations

1. **Remove Deprecated Methods**: Consider removing `validateBalance()` from `BetValidator` in future versions
2. **Enhanced Logging**: Add detailed logging for balance validation failures
3. **Rate Limiting Integration**: Consider combining with rate limiting for additional security
4. **Cache Balance**: For high-traffic scenarios, consider caching user balance with appropriate invalidation
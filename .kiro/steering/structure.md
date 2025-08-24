# Project Structure

## Laravel Casino Application Structure

### Core Application (`app/`)
- **Console/Commands/**: Artisan commands for casino game maintenance and cleanup
- **Contracts/**: Interfaces for game engines, RNG components, and casino services
- **DTOs/**: Data Transfer Objects for structured game responses
- **Engines/**: Game engine implementations for different casino games
- **Enums/**: Game type and configuration enumerations
- **Events/**: Game result events for real-time updates
- **Exceptions/**: Custom exceptions (InsufficientBalance, InvalidBet, RNG)
- **Http/**: Controllers, middleware, and request validation
  - **Controllers/**: API and web controllers (Auth, Game, User, Transaction, Universal)
  - **Middleware/**: Casino-specific middleware (BalanceCheck, GameSession, SpinRateLimit)
  - **Requests/**: Form request validation classes
- **Models/**: Eloquent models for all casino entities
- **Services/**: Core casino business logic services

### Game Engines (`app/Engines/`)
- **SlotGameEngine**: Slot machine game logic and spin processing
- **RouletteGameEngine**: Roulette game logic and betting system
- **Future engines**: Blackjack, Poker, Baccarat (planned)

### Managers (`app/Managers/`)
- **GameSessionManager**: Manages player casino game sessions
- **GameRoundManager**: Handles individual game rounds across all casino games
- **BonusManager**: Manages bonus mechanics and promotional features
- **FreeSpinManager**: Free spin allocation and usage for slot games
- **TransactionManager**: Handles all casino financial transactions

### Processors (`app/Processors/`)
- **GameProcessor**: Universal game processor for all casino games
- **PayoutProcessor**: Calculates winnings based on game-specific rules
- **ScatterResultProcessor**: Handles scatter symbol logic for slot games
- **WildResultProcessor**: Handles wild symbol logic for slot games
- **JackpotProcessor**: Manages progressive and fixed jackpot calculations

### Validators (`app/Validators/`)
- **BetValidator**: Validates betting amounts and user balance across all games

### Generators (`app/Generators/`)
- **RandomNumberGenerator**: RNG implementation for fair casino gameplay
- **ReelGenerator**: Generates reel positions and visible symbols for slots
- **RouletteWheelGenerator**: Generates roulette wheel results

### Loggers (`app/Loggers/`)
- **GameLogger**: Logs all casino game rounds and activities

### Admin Interface (`app/Filament/`)
- **Resources/**: Filament admin resources for casino management
  - GameResource, UserResource, TransactionResource, BonusTypeResource
  - SlotConfigurationResource, RouletteConfigurationResource
- **Pages/**: Custom admin pages for casino operations and analytics

### Database (`database/`)
- **migrations/**: Database schema definitions
- **seeders/**: Sample data for development
- **factories/**: Model factories for testing

### Frontend (`resources/`)
- **js/**: Vue.js casino application
  - **components/**: Reusable Vue components
    - **Games/**: Casino game-specific components
      - **Slots/**: Slot machine UI components
      - **Roulette/**: Roulette game UI components
      - **Common/**: Shared game components
    - **Casino/**: Casino lobby and navigation components
    - **UI/**: Generic UI components
  - **composables/**: Vue composition functions for casino features
  - **stores/**: Pinia state management for casino data
  - **views/**: Page-level Vue components for casino sections
  - **utils/**: Utility functions and casino API client
- **css/**: Tailwind CSS styles for casino theme
- **views/**: Blade templates (minimal, mostly SPA)

### Configuration (`config/`)
- **casino.php**: Casino-wide configuration (game limits, RTP settings, security)
- **game.php**: Game-specific configuration (timeouts, limits, logging)
- Standard Laravel config files (app, auth, database, etc.)

## Naming Conventions

### PHP Classes
- **Models**: Singular PascalCase (`Game`, `User`, `GameSession`, `RouletteConfiguration`)
- **Engines**: Game type with `Engine` suffix (`SlotGameEngine`, `RouletteGameEngine`)
- **DTOs**: Data structure with `Dto` suffix (`GameResultDto`, `SlotGameDataDto`)
- **Services**: Descriptive names ending in `Service` (`PayoutCalculator`, `TransactionManager`)
- **Controllers**: Resource-based with `Controller` suffix (`GameController`, `UniversalGameController`)
- **Exceptions**: Descriptive with `Exception` suffix (`InsufficientBalanceException`)

### Database
- **Tables**: Plural snake_case (`games`, `game_sessions`, `transactions`)
- **Columns**: snake_case (`min_bet`, `max_bet`, `created_at`)
- **Foreign Keys**: `{model}_id` format (`user_id`, `game_id`)

### Frontend
- **Components**: PascalCase (`SlotMachine.vue`, `GameCard.vue`)
- **Composables**: camelCase with `use` prefix (`useSlotMachine.js`)
- **Stores**: camelCase with `Store` suffix (`gameStore.js`)

## Key Architectural Patterns

### Service Layer Pattern
Business logic is encapsulated in service classes, keeping controllers thin and models focused on data relationships.

### Repository Pattern (Implicit)
Eloquent models serve as repositories with custom query scopes and relationships.

### Event-Driven Architecture
Game results trigger events for real-time updates and audit logging.

### Middleware Pipeline
Request processing uses Laravel middleware for authentication, validation, and rate limiting.

### Component-Based Frontend
Vue.js components are organized by feature with clear separation of concerns.
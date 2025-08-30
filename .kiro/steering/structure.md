# Project Structure

## Laravel Slots Game Application Structure

### Core Application (`app/`)
- **Console/Commands/**: Artisan commands for slot game maintenance and cleanup
- **Contracts/**: Interfaces for slot game engines, RNG components, and slot game services
- **DTOs/**: Data Transfer Objects for structured slot game responses
- **Engines/**: Slot game engine implementations
- **Enums/**: Slot game type and configuration enumerations
- **Events/**: Slot game result events for real-time updates
- **Exceptions/**: Custom exceptions (InsufficientBalance, InvalidBet, RNG)
- **Http/**: Controllers, middleware, and request validation
  - **Controllers/**: API and web controllers (Auth, SlotGame, User, Transaction)
  - **Middleware/**: Slot game-specific middleware (BalanceCheck, GameSession, SpinRateLimit)
  - **Requests/**: Form request validation classes
- **Models/**: Eloquent models for all slot game entities
- **Services/**: Core slot game business logic services

### Game Engines (`app/Engines/`)
- **SlotGameEngine**: Slot machine game logic and spin processing
- **ClassicSlotEngine**: Traditional 3-reel slot game logic
- **VideoSlotEngine**: Modern 5-reel slot game logic with advanced features

### Managers (`app/Managers/`)
- **SlotSessionManager**: Manages player slot game sessions
- **SlotRoundManager**: Handles individual slot game rounds
- **BonusManager**: Manages bonus mechanics and promotional features for slots
- **FreeSpinManager**: Free spin allocation and usage for slot games
- **TransactionManager**: Handles all slot game financial transactions

### Processors (`app/Processors/`)
- **SlotGameProcessor**: Slot game processor for all slot game types
- **PayoutProcessor**: Calculates winnings based on slot game rules
- **ScatterResultProcessor**: Handles scatter symbol logic for slot games
- **WildResultProcessor**: Handles wild symbol logic for slot games
- **JackpotProcessor**: Manages progressive and fixed jackpot calculations for slots

### Validators (`app/Validators/`)
- **BetValidator**: Validates betting amounts and user balance for slot games

### Generators (`app/Generators/`)
- **RandomNumberGenerator**: RNG implementation for fair slot gameplay
- **ReelGenerator**: Generates reel positions and visible symbols for slots
- **SymbolGenerator**: Generates symbol combinations for slot games

### Loggers (`app/Loggers/`)
- **SlotGameLogger**: Logs all slot game rounds and activities

### Admin Interface (`app/Filament/`)
- **Resources/**: Filament admin resources for slot game management
  - SlotGameResource, UserResource, TransactionResource, BonusTypeResource
  - SlotConfigurationResource, SlotThemeResource
- **Pages/**: Custom admin pages for slot game operations and analytics

### Database (`database/`)
- **migrations/**: Database schema definitions
- **seeders/**: Sample data for development
- **factories/**: Model factories for testing

### Frontend (`resources/`)
- **js/**: Vue.js slot game application
  - **components/**: Reusable Vue components
    - **Slots/**: Slot machine UI components
      - **ClassicSlots/**: Traditional 3-reel slot components
      - **VideoSlots/**: Modern 5-reel slot components
      - **Common/**: Shared slot game components
    - **Game/**: Game lobby and navigation components
    - **UI/**: Generic UI components
  - **composables/**: Vue composition functions for slot game features
  - **stores/**: Pinia state management for slot game data
  - **views/**: Page-level Vue components for slot game sections
  - **utils/**: Utility functions and slot game API client
- **css/**: Tailwind CSS styles for slot game theme
- **views/**: Blade templates (minimal, mostly SPA)

### Configuration (`config/`)
- **slots.php**: Slot game configuration (game limits, RTP settings, security)
- **game.php**: Slot game-specific configuration (timeouts, limits, logging)
- Standard Laravel config files (app, auth, database, etc.)

## Naming Conventions

### PHP Classes
- **Models**: Singular PascalCase (`SlotGame`, `User`, `SlotGameSession`, `SlotConfiguration`)
- **Engines**: Game type with `Engine` suffix (`SlotGameEngine`, `ClassicSlotEngine`, `VideoSlotEngine`)
- **DTOs**: Data structure with `Dto` suffix (`SlotGameResultDto`, `SlotGameDataDto`)
- **Services**: Descriptive names ending in `Service` (`SlotPayoutCalculator`, `SlotTransactionManager`)
- **Controllers**: Resource-based with `Controller` suffix (`SlotGameController`, `SlotSessionController`)
- **Exceptions**: Descriptive with `Exception` suffix (`InsufficientBalanceException`)

### Database
- **Tables**: Plural snake_case (`slot_games`, `slot_game_sessions`, `transactions`)
- **Columns**: snake_case (`min_bet`, `max_bet`, `created_at`)
- **Foreign Keys**: `{model}_id` format (`user_id`, `slot_game_id`)

### Frontend
- **Components**: PascalCase (`SlotMachine.vue`, `SlotGameCard.vue`)
- **Composables**: camelCase with `use` prefix (`useSlotMachine.js`, `useSlotGame.js`)
- **Stores**: camelCase with `Store` suffix (`slotGameStore.js`, `slotSessionStore.js`)

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
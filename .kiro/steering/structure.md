# Project Structure

## Laravel Application Structure

### Core Application (`app/`)
- **Console/Commands/**: Artisan commands for game maintenance and cleanup
- **Contracts/**: Interfaces for game engine and RNG components
- **Enums/**: Game configuration type enumerations
- **Events/**: Game result events for real-time updates
- **Exceptions/**: Custom exceptions (InsufficientBalance, InvalidBet, RNG)
- **Http/**: Controllers, middleware, and request validation
  - **Controllers/**: API and web controllers (Auth, Game, User, Transaction)
  - **Middleware/**: Game-specific middleware (BalanceCheck, GameSession, SpinRateLimit)
  - **Requests/**: Form request validation classes
- **Models/**: Eloquent models for all entities
- **Services/**: Core business logic services

### Key Services (`app/Services/`)
- **GameEngine**: Main slot machine logic and spin processing
- **PayoutCalculator**: Calculates winnings based on game rules
- **RandomNumberGenerator**: RNG implementation for fair gameplay
- **GameSessionService**: Manages player game sessions
- **GameRoundService**: Handles individual spin rounds
- **BonusService**: Manages bonus mechanics and free spins
- **FreeSpinService**: Free spin allocation and usage

### Admin Interface (`app/Filament/`)
- **Resources/**: Filament admin resources for CRUD operations
  - BonusTypeResource, GameResource, TransactionResource, UserResource
- **Pages/**: Custom admin pages for each resource

### Database (`database/`)
- **migrations/**: Database schema definitions
- **seeders/**: Sample data for development
- **factories/**: Model factories for testing

### Frontend (`resources/`)
- **js/**: Vue.js application
  - **components/**: Reusable Vue components
    - **Game/**: Game-specific components
    - **SlotMachine/**: Slot machine UI components
    - **UI/**: Generic UI components
  - **composables/**: Vue composition functions
  - **stores/**: Pinia state management
  - **views/**: Page-level Vue components
  - **utils/**: Utility functions and API client
- **css/**: Tailwind CSS styles
- **views/**: Blade templates (minimal, mostly SPA)

### Configuration (`config/`)
- **game.php**: Game-specific configuration (timeouts, limits, logging)
- Standard Laravel config files (app, auth, database, etc.)

## Naming Conventions

### PHP Classes
- **Models**: Singular PascalCase (`Game`, `User`, `GameSession`)
- **Services**: Descriptive names ending in `Service` (`GameEngine`, `PayoutCalculator`)
- **Controllers**: Resource-based with `Controller` suffix (`GameController`)
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
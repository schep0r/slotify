# Technology Stack

## Backend
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (development), supports MySQL/PostgreSQL
- **Admin Panel**: Filament 3.3
- **Authentication**: Laravel Sanctum
- **Queue System**: Laravel Queue (Redis/Database)
- **Testing**: PHPUnit 11.5+

## Frontend
- **Framework**: Vue.js 3.5+ with Composition API
- **State Management**: Pinia 3.0+
- **Routing**: Vue Router 4.5+
- **Styling**: Tailwind CSS 4.0
- **Build Tool**: Vite 6.2+
- **HTTP Client**: Axios

## Development Tools
- **Code Style**: Laravel Pint (PHP CS Fixer)
- **Package Manager**: Composer (PHP), npm (Node.js)
- **Process Management**: Laravel Sail (Docker)
- **Logging**: Laravel Pail

## Common Commands

### Development Setup
```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

# Start development servers
composer run dev  # Starts all services (server, queue, logs, vite)
# OR individually:
php artisan serve
npm run dev
```

### Testing
```bash
composer run test
php artisan test
```

### Code Quality
```bash
./vendor/bin/pint  # Format PHP code
```

### Database Operations
```bash
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
```

### Production Build
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
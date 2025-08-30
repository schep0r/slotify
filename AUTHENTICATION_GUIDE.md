# Authentication System Guide

## Overview

The Slotify application uses Laravel Sanctum for API authentication with a Vue.js frontend. This guide covers the authentication system implementation.

## Backend Implementation

### AuthController

The `AuthController` handles user authentication with the following endpoints:

- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/logout` - User logout (requires authentication)
- `GET /api/v1/auth/me` - Get current user data (requires authentication)

### Features

- **Token-based authentication** using Laravel Sanctum
- **Secure password validation** with bcrypt hashing
- **Structured JSON responses** with consistent error handling
- **Request validation** using Laravel Form Requests
- **Comprehensive test coverage** with PHPUnit

### Login Request Validation

The `LoginRequest` class validates:
- Email: required, valid email format
- Password: required, minimum 6 characters

## Frontend Implementation

### Vue Components

1. **LoginPage.vue** - Complete login form with:
   - Email and password fields
   - Password visibility toggle
   - Form validation
   - Loading states
   - Error handling
   - Responsive design with Tailwind CSS

2. **UserMenu.vue** - User dropdown menu with:
   - User profile display
   - Balance information
   - Navigation links
   - Logout functionality

### State Management

**AuthStore (Pinia)** manages:
- User authentication state
- Token storage (localStorage)
- API request headers
- Login/logout operations
- User profile data
- Balance tracking

### API Client

**auth.js** provides:
- Login API calls
- Logout API calls
- User profile fetching
- Consistent error handling

### Router Guards

Navigation guards protect routes:
- `requiresAuth`: Redirects to login if not authenticated
- `requiresGuest`: Redirects to games if already authenticated

## Usage Examples

### Backend API Usage

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'

# Get user profile (with token)
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Frontend Usage

```javascript
// In a Vue component
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()

// Login
await authStore.login({
  email: 'user@example.com',
  password: 'password123'
})

// Check authentication status
if (authStore.isAuthenticated) {
  console.log('User is logged in:', authStore.user)
}

// Logout
await authStore.logout()
```

## Security Features

- **CSRF Protection** via Laravel Sanctum
- **Password Hashing** using bcrypt
- **Token Expiration** managed by Sanctum
- **Rate Limiting** on authentication endpoints
- **Input Validation** on all requests
- **Secure Headers** for API responses

## Testing

Run authentication tests:

```bash
php artisan test --filter=AuthControllerTest
```

The test suite covers:
- Valid login scenarios
- Invalid credential handling
- Required field validation
- Token-based logout
- Profile data retrieval
- Unauthorized access protection

## Configuration

### Laravel Sanctum Setup

Ensure Sanctum is configured in `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

### User Model

The User model includes the `HasApiTokens` trait:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

## Development Notes

- Tokens are stored in localStorage for persistence
- API client automatically includes Authorization headers
- Router guards handle authentication redirects
- Error messages are user-friendly and consistent
- Loading states provide good UX during API calls

## Next Steps

Consider implementing:
- Password reset functionality
- Email verification
- Two-factor authentication
- Session management
- Remember me functionality
# URL Shortener - Laravel API

A URL shortening service with the ability to create custom aliases, set link expiration dates, and track click statistics. 
Business logic is separated into services for reduced code coupling and easier modernization.

## Key Features

- Create short links from long URLs
- Custom aliases for short links
- Set link expiration dates
- Track click counts
- REST API with Swagger documentation
- Automatic cleanup of expired links

## Technical Requirements

- PHP ^8.2
- Laravel ^12.0
- Database (PostgreSQL/SQLite/MySQL)
- Composer

# Installation

## Clone and Install Dependencies

```bash
composer install
```
### 1. Start production server with Docker Compose
```bash
docker compose up -d
```
> Note: Minimal optimization for production. On first run, please wait up to 1 minute for migrations and application startup in container

###  2. Classic local development

### Local Database Configuration

Edit the `.env` file and specify database connection parameters:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=url_shortener_laravel
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### Run Migrations

```bash
php artisan migrate
```

### Generate API Documentation

```bash
php artisan l5-swagger:generate
```

### Start Development Server

```bash
php artisan serve
```

The application will be available at: http://localhost:8000

## Main Commands

### Migrations

```bash
# Run all migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Recreate all tables
php artisan migrate:fresh

# Run migrations with test data seeding
php artisan migrate --seed
```

### Cache Clearing

```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

### Testing

```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage
```

## API Endpoints

### Create Short Link

```http
POST /api/shorten
Content-Type: application/json

{
    "url": "https://example.com",
    "expires_at": "2025-12-31T23:59:59Z",
    "custom_alias": "my-link"
}
```

### Get Link Statistics

```http
GET /api/urls/{id}/stats
```

### Redirect to Original URL

```http
GET /{shortCode}
```

## API Documentation

After generating Swagger documentation, it will be available at:
http://localhost:8000/api/documentation

## Project Structure

- `app/Models/Url.php` - URL model with expiration handling methods
- `app/Services/UrlShortenerService.php` - Service for creating short links
- `app/Services/UrlValidationService.php` - URL data validation service
- `app/Services/UrlCleanupService.php` - Service for cleaning expired links
- `app/Http/Controllers/Api/UrlController.php` - API controller for URL operations
- `routes/api.php` - API routes
- `routes/web.php` - Web routes for redirects

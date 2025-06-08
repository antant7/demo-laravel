#!/bin/sh

# Wait for database to be ready
echo "Waiting for database..."
while ! nc -z laravel_postgres 5432; do
  sleep 1
done
echo "Database is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
while ! nc -z laravel_redis 6379; do
  sleep 1
done
echo "Redis is ready!"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear and cache everything for production
echo "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Start php-fpm
echo "Starting PHP-FPM..."
exec php-fpm

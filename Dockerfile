FROM php:8.3-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl \
    icu-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    zlib-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    autoconf \
    build-base \
    linux-headers \
    libstdc++ \
    openssl \
    shadow \
    brotli-dev \
    postgresql-dev \
    netcat-openbsd \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    pdo_pgsql \
    mbstring \
    intl \
    zip \
    xml \
    pcntl \
    opcache

# Install Swoole for Octane
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Create .env from .env.example if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Octane
RUN php artisan octane:install --server=swoole

# Set permissions
RUN addgroup -g 1000 www \
    && adduser -u 1000 -G www -s /bin/sh -D www \
    && chown -R www:www /var/www

USER www

# Expose port for Octane
EXPOSE 8000

# Use entrypoint script
ENTRYPOINT ["./docker-entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]

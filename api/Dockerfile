# Base image with PHP and Composer
FROM php:8.2-fpm

# Install required PHP extensions and system packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory inside container
WORKDIR /var/www

# Copy composer files first (for better layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies without scripts to avoid config issues
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy Laravel application files
COPY . .

# Clear any existing config cache and regenerate autoloader
RUN php artisan config:clear || true && \
    composer dump-autoload --optimize

# Set permissions for storage and cache folders
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM (será sobrescrito por docker-compose)
CMD ["php-fpm"]

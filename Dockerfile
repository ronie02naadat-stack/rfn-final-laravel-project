FROM php:8.4-fpm

# Install system dependencies and Node.js (for Vite)
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip libpng-dev \
    nodejs npm \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Install npm dependencies and build Vite assets
RUN npm install && npm run build

# Create .env and generate key
RUN cp .env.example .env
RUN php artisan key:generate

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
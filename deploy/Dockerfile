# Базовый образ PHP 8.3 + FPM
FROM php:8.3-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    ffmpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www

# Копируем Laravel-проект
COPY . .

# Устанавливаем зависимости Laravel
RUN composer install --optimize-autoloader --no-dev

# Права на запись
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Запуск artisan serve (по умолчанию)
CMD php artisan serve --host=0.0.0.0 --port=8000

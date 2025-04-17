Инструкция по развертке через докер:

1) клонировать репозиторий
2) в корне проекта запустить команду: docker-compose up -d --build
3) перейти на url "http://localhost:8000/upload-video" и начать работу

возможные проблемы:
  если есть проблемы с базой данных, то запустить миграции:
    1) Провалить в контейнер приложения "docker exec -it хеш_контейнера bash"
    2) Выполнить команду "php artisan migrate"
    3) И вычистить кеш "php artisan optimize"



Инструкции к развертке на ubuntu:

1) Установка ffmpeg последней версии
2) Установка php и соответствующих зависимостей:
apt-get update && apt-get install -y \
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
3) Установка зависимостей с помощью "composer install"
4) Запуск базы данных Postgresql (Удобнее будет вынести ее сборку из docker-compose.yml, но понадобится docker)
5) Запуск миграций - php artisan migrate
6) Запуск веб сервера - php artisan serve 
7) запустить три очереди:
  php artisan queue:work --queue=video-processing --timeout=0
  php artisan queue:work --queue=move-to-s3-worker --timeout=0
  php artisan queue:work --queue=video-splicing --timeout=0
8) установка php 8.1+
9) перейти на url "http://localhost:8000/upload-video" и начать работу



При развертке через докер очень важно чтобы DB_HOST в .env файле имел название контейнера,
а при обычной развертке достаточно явно указать хост

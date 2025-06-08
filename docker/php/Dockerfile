FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev libssl-dev pkg-config libssl-dev \
    supervisor \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo pdo_mysql zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

WORKDIR /var/www/html

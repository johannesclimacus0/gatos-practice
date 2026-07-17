FROM php:8.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_mysql
RUN pecl install xdebug \
    &&  docker-php-ext-enable xdebug
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .
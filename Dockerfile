FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install mbstring curl

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY src src
COPY Tests Tests
COPY Samples Samples
COPY composer.json composer.json
COPY phpunit.xml.dist phpunit.xml.dist
COPY .php-cs-fixer.php .php-cs-fixer.php
COPY php.ini /usr/local/etc/php/php.ini

RUN composer install --no-interaction

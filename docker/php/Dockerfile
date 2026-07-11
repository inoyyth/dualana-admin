FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    mysqli \
    pdo_mysql \
    zip \
    gd

RUN a2enmod rewrite

COPY docker/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html
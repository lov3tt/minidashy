#local PHP-FPM image
FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    unzip \
  && docker-php-ext-install pdo pdo_mysql mysqli zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
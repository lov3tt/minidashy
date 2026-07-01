#local PHP-FPM image
#The local PHP container only. It installs the PHP extensions your API needs: pdo_mysql for database queries via PDO,
#mysqli as a secondary driver, and zip which some Composer packages need. Note this is not the production image —
#that's Dockerfile.render (Step 21), which adds Nginx, supervisord, and the Angular build stage.
#
FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    unzip \
  && docker-php-ext-install pdo pdo_mysql mysqli zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
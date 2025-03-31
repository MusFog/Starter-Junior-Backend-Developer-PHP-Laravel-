FROM php:8.2

WORKDIR /var/www/laravel

RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql exif pcntl gd \
    && apt-get clean

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data /var/www

EXPOSE 8010
ENV PORT=8010

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8010"]


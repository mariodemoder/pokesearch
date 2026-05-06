FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash curl git icu-dev libzip-dev oniguruma-dev sqlite-dev

RUN docker-php-ext-install intl mbstring pdo pdo_mysql pdo_sqlite zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

COPY . .

RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
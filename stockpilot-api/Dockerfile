FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction
FROM php:8.4-fpm-alpine
RUN docker-php-ext-install pdo_mysql bcmath
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY . /var/www/html
WORKDIR /var/www/html
RUN chown -R www-data:www-data storage bootstrap/cache
CMD ["php-fpm"]

# syntax=docker/dockerfile:1.7
# Production image for AKH Solutions — php-fpm + nginx + supervisor.
# Frontend assets (public/build) are compiled in CI and copied in via the build context.

# --- Composer dependencies (production only) ---
FROM composer:2.9 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# --- Runtime ---
FROM php:8.4-fpm-alpine AS runtime

RUN apk add --no-cache nginx supervisor bash icu-libs libzip libpng oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev oniguruma-dev linux-headers \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip gd opcache pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

# App + production vendor (includes the CI-built public/build via the build context).
COPY --from=vendor /app /var/www/html

COPY docker/production/php.ini /usr/local/etc/php/conf.d/zz-akh.ini
COPY docker/production/php-fpm.conf /usr/local/etc/php-fpm.d/zz-akh.conf
COPY docker/production/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/supervisord.conf /etc/supervisord.conf
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]

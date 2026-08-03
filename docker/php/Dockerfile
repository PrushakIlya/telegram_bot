FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git\
    openssl \
    ca-certificates\
    $PHPIZE_DEPS

RUN pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_pgsql zip

RUN apk del $PHPIZE_DEPS

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-custom-error-reporting.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/telegram_bot

COPY . .

RUN chown -R www-data:www-data /var/www/telegram_bot/storage /var/www/telegram_bot/bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

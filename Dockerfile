FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    ffmpeg \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && pecl install redis-6.3.0 \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

RUN a2enmod rewrite headers

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -i 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . /var/www/html/

RUN composer dump-autoload --optimize --no-dev

RUN mkdir -p /var/www/html/storage/cache /var/www/html/storage/rate_limits \
    && chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage \
    && chmod -R 755 /var/www/html/public/uploads /var/www/html/storage

COPY docker/php.ini /usr/local/etc/php/conf.d/tubeyou.ini

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/health.php || exit 1

EXPOSE 80

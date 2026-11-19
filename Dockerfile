FROM php:8.4-apache

# Install system dependencies & PHP extensions required by Laravel, DomPDF & SQLite/MySQL/PgSQL
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libicu-dev libsqlite3-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql pdo_pgsql gd zip bcmath intl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set Apache DocumentRoot to Laravel public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chmod +x docker/start.sh \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 80
CMD ["./docker/start.sh"]

FROM php:8.3-apache

# 1. Install PostgreSQL and other required extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# 2. Enable Apache mod_rewrite (required for Laravel routing)
RUN a2enmod rewrite

# 3. Change Apache's DocumentRoot to Laravel's public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Set working directory and copy files
WORKDIR /var/www/html
COPY . .

# 5. Install Composer and dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# 6. Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Environment Variables (DB_PASSWORD is set in Render dashboard)
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV DB_CONNECTION=pgsql
ENV DB_HOST=pg-13fb0596-barakakanawila-d763.f.aivencloud.com
ENV DB_PORT=21405
ENV DB_DATABASE=defaultdb
ENV DB_USERNAME=avnadmin
ENV DB_SSLMODE=require

EXPOSE 80

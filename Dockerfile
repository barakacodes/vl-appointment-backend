FROM richarvey/nginx-php-fpm:latest

# Install PostgreSQL extension
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /var/www/html

# Copy the application files
COPY . .

# Install composer dependencies (This is the missing step!)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Set correct permissions for Laravel storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Environment variables
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV APP_NAME LaravelAPI

# Database configuration (Password must be set in Render Dashboard)
ENV DB_CONNECTION pgsql
ENV DB_HOST pg-13fb0596-barakakanawila-d763.f.aivencloud.com
ENV DB_PORT 21405
ENV DB_DATABASE defaultdb
ENV DB_USERNAME avnadmin
ENV DB_SSLMODE require

ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["/start.sh"]

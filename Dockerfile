FROM richarvey/nginx-php-fpm:latest

COPY . .

# Ensure PostgreSQL extensions are enabled
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# CRITICAL: Allow composer to install dependencies
ENV SKIP_COMPOSER 0

ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

ENV APP_ENV production
ENV APP_DEBUG false
ENV APP_NAME LaravelAPI

# Aiven PostgreSQL Configuration
ENV DB_CONNECTION pgsql
ENV DB_HOST pg-13fb0596-barakakanawila-d763.f.aivencloud.com
ENV DB_PORT 21405
ENV DB_DATABASE defaultdb
ENV DB_USERNAME avnadmin
ENV DB_SSLMODE require

ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["/start.sh"]

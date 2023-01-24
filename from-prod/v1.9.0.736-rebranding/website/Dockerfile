FROM composer as builder
WORKDIR /app/
COPY composer.* ./
RUN composer install
...
FROM php:7.1-fpm-alpine
...
COPY --from=builder /app/vendor /var/www/vendor
FROM php:8.3-cli-alpine

RUN apk add unzip libpq-dev git icu-dev \
    && docker-php-ext-install bcmath intl \
    && docker-php-ext-enable bcmath intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

ENV COMPOSER_ALLOW_SUPERUSER 1

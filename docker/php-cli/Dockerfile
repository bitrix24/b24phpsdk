#
#  This file is part of the bitrix24-php-sdk package.
#
#  © Maksim Mesilov <mesilov.maxim@gmail.com>
#
#  For the full copyright and license information, please view the MIT-LICENSE.txt
#  file that was distributed with this source code.
#

# Grab the PHP extension installer script from mlocati's image
# https://github.com/mlocati/docker-php-extension-installer#readme
FROM mlocati/php-extension-installer:2.4 AS php-extension-installer

# Separate Composer stage to install PHP dependencies efficiently
# https://hub.docker.com/_/composer
FROM composer:2.8 AS composer

# Using official PHP 8.3 CLI image based on Debian Bookworm for development purposes
FROM php:8.4-cli-bookworm AS dev-php

# Copy Composer binary from the official Composer image
COPY --from=composer /usr/bin/composer /usr/bin/composer
# Copy PHP extension installer script from mlocati's image
COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/bin/install-php-extensions

# Link to the source repository for this image (OCI standard label)
LABEL "org.opencontainers.image.source"="https://github.com/bitrix24/b24phpsdk"

# ── expensive layers first (always cached) ────────────────────────────────────
RUN <<EOF
  apt-get update
  apt-get install --no-install-recommends --no-install-suggests -q -y \
          unzip
  apt-get clean
  rm -rf /var/lib/apt/lists/*
EOF

RUN install-php-extensions bcmath excimer intl pcntl opcache yaml zip

WORKDIR /var/www/html

ENV COMPOSER_CACHE_DIR=/tmp/composer/cache

# ── cheap UID/GID layers last (only these rebuild on UID change) ──────────────
ARG UID=10001
ARG GID=10001

RUN groupmod --gid=${GID} www-data && usermod --uid=${UID} --gid=${GID} www-data

RUN chown www-data:www-data /var/www/html

USER www-data

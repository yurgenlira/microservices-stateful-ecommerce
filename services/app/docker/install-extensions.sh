#!/bin/sh
set -e

# Compiler toolchain + headers — removed after build via apk del .build-deps
apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    postgresql-dev \
    oniguruma-dev

# Runtime libraries kept after build — required by compiled extensions to load
# libpng, libjpeg-turbo, libwebp, freetype: gd
# libzip: zip
# icu-libs: intl
# libpq: pdo_pgsql + pgsql
# oniguruma: mbstring
apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    libzip \
    icu-libs \
    libpq \
    oniguruma

# Required before install — enables JPEG, WebP and FreeType support in gd
docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# pdo_pgsql + pgsql: PostgreSQL drivers for Eloquent and raw queries
# intl: locale-aware formatting (dates, currencies)
# pcntl: process signals for queue workers (SIGTERM handling)
# zip: archive support for uploads and Composer
# bcmath: arbitrary-precision math for monetary calculations
# gd: image processing for product photos
# mbstring: UTF-8 string operations (required by Laravel core)
# opcache: statically compiled into PHP 8.5 — configured via opcache.ini only
docker-php-ext-install -j"$(nproc)" \
    pdo_pgsql \
    pgsql \
    intl \
    pcntl \
    zip \
    bcmath \
    gd \
    mbstring

# C extension for Redis — faster than pure-PHP Predis
# Used as driver for cache store (DB 0) and session store (DB 1)
pecl install redis-6.3.0
docker-php-ext-enable redis

apk del .build-deps

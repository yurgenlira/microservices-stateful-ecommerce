# ==========================================
# Stage: base
# ==========================================
FROM php:8.5-fpm-alpine AS base

COPY docker/install-extensions.sh /tmp/install-extensions.sh
RUN chmod +x /tmp/install-extensions.sh \
    && /tmp/install-extensions.sh \
    && rm /tmp/install-extensions.sh

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini     /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www

# ==========================================
# Stage: composer-deps
# ==========================================
FROM base AS composer-deps

COPY --from=composer:2.9.7 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts

# ==========================================
# Stage: development
# ==========================================
FROM base AS development

RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

COPY --from=composer:2.9.7 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache git wget procps nginx

RUN apk add --no-cache --virtual .xdebug-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug-3.5.1 \
    && docker-php-ext-enable xdebug \
    && apk del .xdebug-deps

COPY docker/php/xdebug.ini      /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/php/opcache-dev.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini         /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf        /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /run/nginx \
    && rm -f /etc/nginx/http.d/default.conf

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh      /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]

# ==========================================
# Stage: production
# ==========================================
FROM base AS production

ARG APP_VERSION=unknown
ENV APP_VERSION=${APP_VERSION}

RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

COPY --from=composer-deps --chown=www:www /var/www /var/www

RUN apk add --no-cache nginx \
    && mkdir -p /run/nginx /run/php-fpm \
        /var/lib/nginx/logs /var/lib/nginx/tmp \
    && chown -R www:www /run/nginx /run/php-fpm \
        /var/lib/nginx /var/log/nginx

COPY --chown=www:www docker/php/www.conf        /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www:www docker/nginx/default.conf  /etc/nginx/http.d/default.conf
COPY --chown=www:www docker/entrypoint.sh       /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/testing \
    && chown -R www:www storage/

USER www

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -qO- http://localhost:8080/up || exit 1

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]

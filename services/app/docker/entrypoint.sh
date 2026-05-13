#!/bin/sh
set -e

: "${DB_HOST:?DB_HOST is required}"
: "${REDIS_HOST:?REDIS_HOST is required}"

if [ "${APP_ENV:-local}" != "local" ] && [ -z "${APP_KEY}" ]; then
    echo "APP_KEY is required in non-local environments" >&2
    exit 1
fi

if [ -f vendor/autoload.php ]; then
    php artisan optimize
fi

if [ -n "${PHP_FPM_MAX_CHILDREN}" ]; then
    sed -i "s/^pm.max_children = .*/pm.max_children = ${PHP_FPM_MAX_CHILDREN}/" \
        /usr/local/etc/php-fpm.d/www.conf
fi

php-fpm -D

exec nginx -g "daemon off;"

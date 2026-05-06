#!/bin/sh
set -e
cd /var/www
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
php artisan config:cache
php artisan route:cache
php artisan migrate --force
php-fpm -D
nginx -g 'daemon off;'

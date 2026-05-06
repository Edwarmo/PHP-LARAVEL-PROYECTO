#!/bin/sh
set -e

cd /var/www

echo "DB_USERNAME=$DB_USERNAME"
echo "DB_HOST=$DB_HOST"

# Primero cachear config, LUEGO limpiar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones
php artisan migrate --force

# Arranca nginx + php-fpm
php-fpm &
nginx -g 'daemon off;'

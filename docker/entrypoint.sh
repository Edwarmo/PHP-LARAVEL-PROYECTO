#!/bin/sh
set -e

cd /var/www

echo "DB_USERNAME=$DB_USERNAME"
echo "DB_HOST=$DB_HOST"

# Cachear config y rutas (view:cache falla sin config/view.php)
php artisan config:cache
php artisan route:cache

# Migraciones
php artisan migrate --force

# Arranca nginx + php-fpm
php-fpm &
nginx -g 'daemon off;'

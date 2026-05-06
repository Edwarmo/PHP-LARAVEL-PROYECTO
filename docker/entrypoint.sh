#!/bin/sh
set -e

cd /var/www

# Debug — verificar que las vars llegan
echo "DB_USERNAME=$DB_USERNAME"
echo "DB_HOST=$DB_HOST"

# Limpiar cachés anteriores
php artisan config:clear
php artisan cache:clear

# Cachear para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones
php artisan migrate --force

# Arranca nginx + php-fpm
php-fpm &
nginx -g 'daemon off;'

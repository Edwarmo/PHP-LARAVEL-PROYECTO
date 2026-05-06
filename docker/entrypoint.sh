#!/bin/sh
set -e

# Crear directorios necesarios
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/logs
chown -R www-data:www-data /var/www/storage

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations
php artisan migrate --force

# Inyectar $PORT en nginx
envsubst '$PORT' < /etc/nginx/nginx.conf > /tmp/nginx.conf
mv /tmp/nginx.conf /etc/nginx/nginx.conf

# Start PHP-FPM en background
php-fpm -D

# Start nginx en foreground
nginx -g "daemon off;"

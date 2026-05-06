#!/bin/sh
set -e

cd /var/www

# Limpiar cache previo del build
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes*.php

# Cachear usando vars de entorno de Render directamente
php artisan config:cache
php artisan route:cache

# Migraciones
php artisan migrate --force

# Servicios
php-fpm -D
nginx -g 'daemon off;'

#!/bin/sh
set -e

PORT=${PORT:-8080}

# Laravel bootstrap
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

# Inertia SSR (background)
php artisan inertia:start-ssr 2>/dev/null &

# Fix nginx port
sed -i "s/listen 8080/listen $PORT/" /etc/nginx/http.d/default.conf

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
exec nginx -g 'daemon off;'

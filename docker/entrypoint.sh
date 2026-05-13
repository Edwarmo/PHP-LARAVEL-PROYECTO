#!/bin/sh
set -e

PORT=${PORT:-8080}

# Laravel bootstrap
mkdir -p storage/logs storage/framework/{sessions,views,cache/data} bootstrap/cache database
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Use SQLite fallback if no DB_CONNECTION set via env
if [ -z "$DB_CONNECTION" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    touch database/database.sqlite
fi

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

#!/bin/sh
set -e

PORT=${PORT:-8080}

# Laravel bootstrap
mkdir -p storage/framework/views database
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Use SQLite for standalone Docker dev (override .env)
export DB_CONNECTION=sqlite
export DB_DATABASE=/var/www/html/database/database.sqlite
touch database/database.sqlite

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

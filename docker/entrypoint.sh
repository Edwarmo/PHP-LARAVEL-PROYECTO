#!/bin/sh
set -e

cd /var/www

# Crear directorios necesarios
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/logs
chown -R www-data:www-data /var/www/storage

# Crear .env si no existe (usar .env.example como plantilla)
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Sincronizar variables de entorno de Render con .env
if [ -n "$DB_HOST" ]; then
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
fi
if [ -n "$DB_PORT" ]; then
    sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
fi
if [ -n "$DB_DATABASE" ]; then
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
fi
if [ -n "$DB_USERNAME" ]; then
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
fi
if [ -n "$DB_PASSWORD" ]; then
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
fi
if [ -n "$APP_URL" ]; then
    sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
fi
if [ -n "$APP_ENV" ]; then
    sed -i "s/APP_ENV=.*/APP_ENV=$APP_ENV/" .env
fi

# Asegurar APP_KEY
php artisan key:generate --force

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

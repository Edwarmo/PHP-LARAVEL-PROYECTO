#!/bin/bash
set -e

PORT=${PORT:-8080}

# Asegurar que las carpetas de storage existan y tengan permisos de escritura
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Suppress Apache ServerName warning
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Fix Apache port dynamically
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-enabled/000-default.conf

# Laravel bootstrap (Limpiar y cachear config en producción)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ejecutar migraciones en producción
php artisan migrate --force

# Iniciar el servidor SSR de Inertia en segundo plano
php artisan inertia:start-ssr &

# Iniciar el servidor (Apache en foreground)
exec apache2-foreground


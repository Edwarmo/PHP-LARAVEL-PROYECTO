#!/bin/bash
set -e

PORT=${PORT:-8080}

# Suppress Apache ServerName warning
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Fix Apache port dynamically
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-enabled/000-default.conf

# Laravel bootstrap
php artisan config:cache || true
php artisan route:cache || true
php artisan migrate --force || true

# Start Apache in foreground (ONLY ONCE)
exec apache2-foreground
#!/bin/bash
set -e

# Default port
PORT=${PORT:-8080}

# Update Apache ports
sed -i "s/80/$PORT/g" /etc/apache2/ports.conf
sed -i "s/:80/>:$PORT/g" /etc/apache2/sites-available/000-default.conf

# Run Laravel optimizations
cd /var/www/html
php artisan config:cache
php artisan route:cache
php artisan migrate --force

# Start Apache in foreground
apache2-foreground
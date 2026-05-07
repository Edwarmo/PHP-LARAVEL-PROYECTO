#!/bin/bash
set -e

PORT=${PORT:-8080}

# Fix Apache port
sed -i "s/80/$PORT/g" /etc/apache2/ports.conf
sed -i "s/:80>/:$PORT>/g" /etc/apache2/sites-enabled/000-default.conf

# Start Apache in background FIRST
apache2ctl start

# Then run migrations (if DB fails, app still runs)
php artisan config:cache || true
php artisan route:cache || true
php artisan migrate --force || true

# Keep Apache running in foreground
# Note: we stop the background instance first so FOREGROUND doesn't crash
apache2ctl stop
apache2-foreground
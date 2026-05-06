#!/bin/sh
set -e

cd /var/www

echo "DB_USERNAME=$DB_USERNAME"
echo "DB_HOST=$DB_HOST"
echo "DB_DATABASE=$DB_DATABASE"
echo "DB_PASSWORD=$DB_PASSWORD"

# Limpiar caches anteriores
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
php artisan config:clear
php artisan cache:clear

# Crear .env desde cero con valores de Render (o mantener existente y actualizar)
if [ ! -f .env ]; then
    cat > .env << ENVEOF
APP_NAME="Reservas VideoConf"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-base64:YOUR_APP_KEY_HERE}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-https://php-laravel-proyecto.onrender.com}

APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_CO

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-6543}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<tu_mailtrap_username>
MAIL_PASSWORD=<tu_mailtrap_password>
MAIL_FROM_ADDRESS="noreply@videoconfreservas.com"
MAIL_FROM_NAME="\${APP_NAME}"
ENVEOF
else
    # Actualizar .env existente con vars de Render
    [ -n "$DB_HOST" ] && sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    [ -n "$DB_PORT" ] && sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    [ -n "$DB_DATABASE" ] && sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
    [ -n "$DB_USERNAME" ] && sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
    [ -n "$DB_PASSWORD" ] && sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    [ -n "$APP_URL" ] && sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
fi

# Cachear config
php artisan config:cache
php artisan route:cache

# Migraciones
php artisan migrate --force

# Arranca nginx + php-fpm
php-fpm &
nginx -g 'daemon off;'

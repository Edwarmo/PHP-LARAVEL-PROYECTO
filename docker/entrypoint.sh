#!/bin/sh
set -e
cd /var/www

# Forzar IPv4 para conexiones PostgreSQL
export PGHOST="db.qsbeluygtxexjcrxwbme.supabase.co"
export PGPORT="5432"
export PGDATABASE="postgres"
export PGUSER="postgres"
export PGPASSWORD="123edwarM@.."
export PGOPTIONS="-c tcp_keepalives_idle=60 -c tcp_keepalives_interval=60"

# Limpiar cachés previos
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php

# Cachear usando vars de entorno
php artisan config:cache
php artisan route:cache

# Migraciones - usando pg_connect que respeta PGOPTIONS
php artisan migrate --force

# Servicios
php-fpm -D
nginx -g 'daemon off;'

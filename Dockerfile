# ── Stage 1: Node build ──────────────────────────────────────
FROM php:8.3-cli-alpine AS node-build
WORKDIR /app

# Instalar Node + npm
RUN apk add --no-cache nodejs npm

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP deps (para Ziggy)
COPY composer*.json ./
RUN composer install --no-dev --no-scripts --no-interaction --ignore-platform-reqs

# Node deps + build
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ── Stage 2: PHP runtime ─────────────────────────────────────
FROM php:8.3-fpm-alpine

# System deps
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip unzip curl

# PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# PHP deps
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# App files
COPY . .

# Built assets from stage 1
COPY --from=node-build /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]

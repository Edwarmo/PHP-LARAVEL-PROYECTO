# ── Stage 1: Node build ──────────────────────────────────────
FROM php:8.3-cli-alpine AS node-build
WORKDIR /app

# Install Node + npm
RUN apk add --no-cache nodejs npm

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP deps
COPY composer*.json ./
RUN composer install --no-dev --no-scripts --no-interaction --ignore-platform-reqs

# Node deps + build
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ── Stage 2: PHP runtime with Apache ─────────────────────────────
FROM php:8.3-apache

# System deps
RUN apt-get update && apt-get install -y \
    postgresql-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip curl \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP deps
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# App files
COPY . .

# Built assets from stage 1
COPY --from=node-build /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Apache config for Laravel
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

# Start script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

ENTRYPOINT ["/start.sh"]
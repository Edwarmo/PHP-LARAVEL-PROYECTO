# ── Stage 1: Node + Composer deps ───────────────────────────
FROM php:8.3-cli-alpine AS node-build
WORKDIR /app

# Install Node + npm + pnpm
RUN apk add --no-cache nodejs npm && npm install -g pnpm

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP deps (cache layer: only if composer.* changes)
COPY composer.* ./
RUN composer install --no-dev --no-scripts --no-interaction --ignore-platform-reqs

# Node deps (cache layer: only if package.json/pnpm-lock changes)
COPY package.json pnpm-lock.yaml ./
RUN pnpm config set onlyBuiltDependencies "esbuild" && \
    pnpm install --frozen-lockfile

# App source + build
COPY . .
RUN pnpm run build

# ── Stage 2: PHP runtime with Apache ─────────────────────────
FROM php:8.3-apache

# System deps + PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip curl \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP deps (cache layer)
COPY composer.* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# App files
COPY . .

# Built assets from stage 1
COPY --from=node-build /app/public/build ./public/build

# Storage structure + permissions
RUN mkdir -p storage/framework/{sessions,views,cache/data} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Apache config
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

# Entrypoint
COPY docker/start.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]

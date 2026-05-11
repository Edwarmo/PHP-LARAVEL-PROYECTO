# syntax=docker/dockerfile:1
# ==============================================================
# STAGE 1: Node builder (pnpm + Vite)
# ==============================================================
FROM node:22-alpine AS node-builder
WORKDIR /app

RUN corepack enable && corepack prepare pnpm@latest --activate

COPY package.json pnpm-lock.yaml ./
RUN --mount=type=cache,id=pnpm-store,target=/root/.local/share/pnpm/store \
    pnpm install --frozen-lockfile --ignore-scripts

COPY . .
RUN pnpm run build

# ==============================================================
# STAGE 2: PHP base (deps + extensions)
# ==============================================================
FROM php:8.3-fpm-alpine AS php-base

ENV LD_LIBRARY_PATH="/usr/local/lib:${LD_LIBRARY_PATH}"

RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip unzip curl \
    && docker-php-ext-install -j$(nproc) pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# ==============================================================
# STAGE 3: Runtime (PHP-FPM + Nginx)
# ==============================================================
FROM php-base AS runtime

# Nginx + process runner
RUN apk add --no-cache nginx && \
    mkdir -p /var/www/html/storage/framework/{sessions,views,cache/data} \
             /var/www/html/bootstrap/cache \
             /run/nginx && \
    adduser -D -H -s /sbin/nologin www-data && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY . /var/www/html
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]

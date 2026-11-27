# =============================================================================
# SIGA Enterprise — Dockerfile Principal (Multi-stage)
# PHP 8.2-FPM + Nginx + PostgreSQL | Sin root | Alpine
# =============================================================================

# -----------------------------------------------------------------------------
# STAGE 1: Builder de dependencias PHP (Composer)
# -----------------------------------------------------------------------------
FROM composer:2.8 AS composer-builder

WORKDIR /app

# Copiar manifiestos primero para caché de capas
COPY composer.json composer.lock ./

# Instalar dependencias sin scripts de desarrollo
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-plugins \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# Copiar código fuente
COPY . .

# Optimizar autoload
RUN composer dump-autoload --optimize --no-dev

# -----------------------------------------------------------------------------
# STAGE 2: Compilación de assets (Vite)
# -----------------------------------------------------------------------------
FROM node:20-alpine AS vite-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm run build

# -----------------------------------------------------------------------------
# STAGE 3: Imagen final PHP-FPM + Nginx (Alpine, sin root)
# -----------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="SIGA Enterprise"
LABEL description="Sistema de Gestión de Activos, Préstamos y Mantenimiento"
LABEL version="1.0"

# ----- PASO 1: Paquetes del sistema operativo -----
RUN apk update && apk add --no-cache \
        nginx \
        supervisor \
        curl \
        zip \
        unzip \
        sqlite \
        sqlite-dev \
        postgresql-dev \
        libzip-dev

# ----- PASO 2: Extensiones PDO y utilidades de PHP -----
RUN docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install pdo_sqlite \
    && docker-php-ext-install zip opcache pcntl bcmath \
    && rm -rf /tmp/pear /var/cache/apk/*

# ----- PASO 3: Crear usuario no-root 'www' (uid 1000) -----
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S -G www -s /bin/sh www

# ----- PASO 4: Configurar PHP-FPM para usuario no-root 'www' (como root durante build) -----
RUN sed -i 's/user = www-data/user = www/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = www/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/listen = 127.0.0.1:9000/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;pid = run/php-fpm.pid|pid = /tmp/php-fpm.pid|g' /usr/local/etc/php-fpm.conf \
    && mkdir -p /usr/local/var/log /usr/local/var/run \
    && chown -R www:www /usr/local/var /usr/local/etc/php-fpm.d /usr/local/etc/php-fpm.conf

# ----- PASO 5: Copiar configuraciones personalizadas -----
COPY docker/app/php.ini                /usr/local/etc/php/conf.d/siga.ini
COPY docker/app/opcache.ini            /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx/nginx.conf           /etc/nginx/nginx.conf
COPY docker/nginx/default.conf         /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ----- PASO 6: Directorio de la aplicación -----
WORKDIR /var/www/siga

# Copiar dependencias del builder
COPY --from=composer-builder --chown=www:www /app/vendor ./vendor

# Copiar código fuente
COPY --chown=www:www . .

# Copiar assets compilados para que Laravel encuentre el manifiesto de Vite
COPY --from=vite-builder --chown=www:www /app/public/build ./public/build

# ----- PASO 7: Permisos de directorios de Laravel -----
RUN chown www:www /var/www/siga \
    && mkdir -p \
        /var/www/siga/storage/app/public \
        /var/www/siga/storage/framework/cache/data \
        /var/www/siga/storage/framework/sessions \
        /var/www/siga/storage/framework/views \
        /var/www/siga/storage/logs \
        /var/www/siga/bootstrap/cache \
    && chown -R www:www \
        /var/www/siga/storage \
        /var/www/siga/bootstrap/cache \
    && chmod -R 775 \
        /var/www/siga/storage \
        /var/www/siga/bootstrap/cache

# ----- PASO 8: Directorios temporales de Nginx (sin root) -----
RUN mkdir -p \
        /run/nginx \
        /var/log/nginx \
        /var/lib/nginx/tmp \
        /tmp/nginx_client_temp \
        /tmp/nginx_proxy_temp \
        /tmp/nginx_fastcgi_temp \
        /tmp/nginx_uwsgi_temp \
        /tmp/nginx_scgi_temp \
    && chown -R www:www \
        /run/nginx \
        /var/log/nginx \
        /var/lib/nginx \
        /tmp/nginx_client_temp \
        /tmp/nginx_proxy_temp \
        /tmp/nginx_fastcgi_temp \
        /tmp/nginx_uwsgi_temp \
        /tmp/nginx_scgi_temp

# ----- PASO 9: Script de entrada -----
COPY --chown=www:www docker/app/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

USER www

ENTRYPOINT ["/entrypoint.sh"]

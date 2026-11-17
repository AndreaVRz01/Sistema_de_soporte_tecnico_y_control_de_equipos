# =============================================================================
# SIGA Enterprise — Dockerfile Principal (Multi-stage)
# PHP 8.2-FPM + Nginx + PostgreSQL | Sin root | Alpine
#
# FIX aplicado:
#   - Añadido sqlite-dev y postgresql-dev ANTES de docker-php-ext-install
#   - Eliminado -j$(nproc) para evitar condiciones de carrera en el build
#   - Separadas extensiones en grupos para identificar errores fácilmente
#   - Movida config a docker/app/ para mejor organización
# =============================================================================

# -----------------------------------------------------------------------------
# STAGE 1: Builder de dependencias PHP (Composer)
# -----------------------------------------------------------------------------
FROM composer:2.8 AS composer-builder

WORKDIR /app

# Copiar solo manifiestos primero → mejor caché de capas Docker
COPY composer.json composer.lock ./

# Instalar dependencias sin dev ni scripts
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-plugins \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# Copiar código fuente completo
COPY . .

# Regenerar autoload con el código fuente real
RUN composer dump-autoload --optimize --no-dev

# -----------------------------------------------------------------------------
# STAGE 2: Imagen final PHP-FPM + Nginx (Alpine, sin root)
# -----------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="SIGA Enterprise"
LABEL description="Sistema de Gestión de Activos, Préstamos y Mantenimiento"
LABEL version="4.2"

# ----- PASO 1: Paquetes del sistema operativo -----
# IMPORTANTE: sqlite-dev y postgresql-dev deben estar ANTES de docker-php-ext-install
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

# ----- PASO 2: Extensiones PDO (una por una para diagnóstico claro) -----
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pdo_sqlite

# ----- PASO 3: Resto de extensiones PHP -----
RUN docker-php-ext-install zip opcache pcntl bcmath \
    && rm -rf /tmp/pear /var/cache/apk/*

# ----- PASO 4: Configuración PHP y Nginx -----
COPY docker/app/php.ini      /usr/local/etc/php/conf.d/siga.ini
COPY docker/app/opcache.ini  /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx/nginx.conf      /etc/nginx/nginx.conf
COPY docker/nginx/default.conf    /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ----- PASO 5: Usuario no-root 'www' (Alpine nativo, sin paquete shadow) -----
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S -G www -s /bin/sh www

# ----- PASO 6: Directorio de la aplicación -----
WORKDIR /var/www/siga

# Vendor del stage builder
COPY --from=composer-builder --chown=www:www /app/vendor ./vendor

# Código fuente
COPY --chown=www:www . .

# ----- PASO 7: Permisos de directorios de Laravel -----
RUN mkdir -p \
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

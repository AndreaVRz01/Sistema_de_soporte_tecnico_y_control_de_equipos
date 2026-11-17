#!/bin/sh
# =============================================================================
# Entrypoint — SIGA Enterprise Docker
# Se ejecuta como usuario 'www' (uid 1000, no-root) al iniciar el contenedor.
# Inicializa Laravel y lanza Supervisor (PHP-FPM + Nginx)
# =============================================================================
set -e

echo "============================================="
echo "  SIGA Enterprise v4.2 — Iniciando contenedor"
echo "============================================="

APP_DIR="/var/www/siga"

# ----- 1. Verificar que .env existe -----
if [ ! -f "$APP_DIR/.env" ]; then
    echo "[WARN] .env no encontrado. Copiando .env.example..."
    cp "$APP_DIR/.env.example" "$APP_DIR/.env" 2>/dev/null || true
fi

# ----- 2. Generar APP_KEY si no existe -----
APP_KEY_VALUE=$(grep "^APP_KEY=" "$APP_DIR/.env" | cut -d= -f2)
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "" ]; then
    echo "[INFO] Generando APP_KEY..."
    php "$APP_DIR/artisan" key:generate --force
fi

# ----- 3. Crear base de datos SQLite si no existe -----
SQLITE_DB="${DB_DATABASE:-$APP_DIR/database/database.sqlite}"
if [ ! -f "$SQLITE_DB" ]; then
    echo "[INFO] Creando base de datos SQLite en: $SQLITE_DB"
    mkdir -p "$(dirname "$SQLITE_DB")"
    touch "$SQLITE_DB"
fi

# ----- 4. Ejecutar migraciones -----
echo "[INFO] Ejecutando migraciones..."
php "$APP_DIR/artisan" migrate --force --no-interaction || echo "[WARN] Migraciones fallaron o ya están aplicadas."

# ----- 5. Limpiar y optimizar caché de Laravel -----
echo "[INFO] Optimizando Laravel..."
php "$APP_DIR/artisan" config:cache  --no-interaction || true
php "$APP_DIR/artisan" route:cache   --no-interaction || true
php "$APP_DIR/artisan" view:cache    --no-interaction || true

# ----- 6. Configurar PHP-FPM para escuchar en socket Unix -----
# El socket debe ser accesible por www (no-root)
sed -i 's|listen = 127.0.0.1:9000|listen = /tmp/php-fpm.sock|' \
    /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true
sed -i 's|^;listen.owner = .*|listen.owner = www|' \
    /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true
sed -i 's|^;listen.group = .*|listen.group = www|' \
    /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true
sed -i 's|^;listen.mode = .*|listen.mode = 0660|' \
    /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true

# ----- 7. Crear directorios temporales de Nginx -----
mkdir -p /tmp/nginx_client_temp /tmp/nginx_proxy_temp \
         /tmp/nginx_fastcgi_temp /tmp/nginx_uwsgi_temp \
         /tmp/nginx_scgi_temp

echo "[INFO] Contenedor listo. Lanzando Supervisor..."
echo "---------------------------------------------"
echo "  → App disponible en http://localhost:8080"
echo "  → Frontend disponible en http://localhost:8000"
echo "============================================="

# ----- 8. Lanzar Supervisor (PHP-FPM + Nginx + Queue) -----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

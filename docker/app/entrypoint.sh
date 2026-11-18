#!/bin/sh
# =============================================================================
# Entrypoint — SIGA Enterprise Docker (docker/app/entrypoint.sh)
# Se ejecuta como usuario 'www' (uid 1000, no-root)
# =============================================================================
set -e

APP_DIR="/var/www/siga"

echo "============================================="
echo "  SIGA Enterprise v4.2 — Iniciando"
echo "============================================="

# ----- 1. Crear directorios de storage necesarios para Laravel -----
mkdir -p "$APP_DIR/storage/app/public" \
         "$APP_DIR/storage/framework/cache/data" \
         "$APP_DIR/storage/framework/sessions" \
         "$APP_DIR/storage/framework/views" \
         "$APP_DIR/storage/logs" \
         "$APP_DIR/bootstrap/cache"

# ----- 2. Verificar/generar APP_KEY -----
if [ ! -f "$APP_DIR/.env" ]; then
    echo "[INFO] .env no encontrado, inicializando configuración local..."
    if [ -f "$APP_DIR/.env.example" ]; then
        cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    else
        : > "$APP_DIR/.env"
    fi
fi

APP_KEY_VALUE="${APP_KEY:-}"
if [ -z "$APP_KEY_VALUE" ]; then
    APP_KEY_VALUE=$(grep "^APP_KEY=" "$APP_DIR/.env" 2>/dev/null | cut -d= -f2- | tr -d '"') || true
fi
if [ -z "$APP_KEY_VALUE" ]; then
    echo "[INFO] Generando APP_KEY..."
    php "$APP_DIR/artisan" key:generate --force
    APP_KEY_VALUE=$(grep "^APP_KEY=" "$APP_DIR/.env" 2>/dev/null | cut -d= -f2- | tr -d '"') || true
fi
if [ -z "$APP_KEY_VALUE" ]; then
    echo "[ERROR] No fue posible generar APP_KEY." >&2
    exit 1
fi
export APP_KEY="$APP_KEY_VALUE"

# ----- 3. Esperar a PostgreSQL (con credenciales unificadas) -----
DB_H="${DB_HOST:-bd}"
DB_P="${DB_PORT:-5432}"
DB_D="${DB_DATABASE:-siga_db}"
DB_U="${DB_USERNAME:-siga_user}"
DB_W="${DB_PASSWORD:-siga_pass_2024}"

echo "[INFO] Verificando conexión a PostgreSQL ($DB_H:$DB_P/$DB_D)..."
RETRIES=30
while [ $RETRIES -gt 0 ]; do
    if php -r "
        try {
            new PDO('pgsql:host=$DB_H;port=$DB_P;dbname=$DB_D', '$DB_U', '$DB_W', [PDO::ATTR_TIMEOUT => 2]);
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; then
        echo "[OK] PostgreSQL conectado exitosamente."
        break
    fi
    RETRIES=$((RETRIES - 1))
    if [ $RETRIES -eq 0 ]; then
        echo "[WARN] PostgreSQL no respondió tras 30 intentos. Continuando inicio de servicios..."
        break
    fi
    echo "[INFO] Esperando a PostgreSQL ($RETRIES intentos restantes)..."
    sleep 2
done

# ----- 4. Ejecutar migraciones y seeders -----
echo "[INFO] Ejecutando migraciones..."
php "$APP_DIR/artisan" migrate --force --no-interaction || echo "[WARN] Migraciones omitidas o ya aplicadas."

echo "[INFO] Ejecutando seeders..."
php "$APP_DIR/artisan" db:seed --force --no-interaction || echo "[WARN] No fue posible ejecutar los seeders."

# ----- 5. Limpiar cachés para asegurar que Laravel use las variables de entorno actuales -----
echo "[INFO] Limpiando cachés de configuración..."
php "$APP_DIR/artisan" config:clear --no-interaction || true
php "$APP_DIR/artisan" route:clear  --no-interaction || true
php "$APP_DIR/artisan" view:clear   --no-interaction || true

# ----- 6. Directorios temporales de Nginx en /tmp -----
mkdir -p /tmp/nginx_client_temp /tmp/nginx_proxy_temp \
         /tmp/nginx_fastcgi_temp /tmp/nginx_uwsgi_temp /tmp/nginx_scgi_temp

echo "✔ Contenedor listo."
echo "  → App:      http://localhost:8080 (directo)"
echo "  → Frontend: http://localhost:8000 (proxy)"
echo "============================================="

# ----- 7. Iniciar Supervisor (PHP-FPM + Nginx + Queue Worker) -----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

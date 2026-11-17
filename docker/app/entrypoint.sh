#!/bin/sh
# =============================================================================
# Entrypoint — SIGA Enterprise Docker (docker/app/entrypoint.sh)
# Corre como usuario 'www' (uid 1000, no-root)
# Inicializa Laravel con PostgreSQL y lanza Supervisor
# =============================================================================
set -e

APP_DIR="/var/www/siga"

echo "============================================="
echo "  SIGA Enterprise v4.2 — Iniciando"
echo "============================================="

# ----- 1. Verificar .env -----
if [ ! -f "$APP_DIR/.env" ]; then
    echo "[WARN] .env no encontrado, copiando .env.example..."
    cp "$APP_DIR/.env.example" "$APP_DIR/.env" 2>/dev/null || true
fi

# ----- 2. Generar APP_KEY si está vacío -----
APP_KEY_VALUE=$(grep "^APP_KEY=" "$APP_DIR/.env" | cut -d= -f2- | tr -d '"')
if [ -z "$APP_KEY_VALUE" ]; then
    echo "[INFO] Generando APP_KEY..."
    php "$APP_DIR/artisan" key:generate --force
fi

# ----- 3. Esperar a que PostgreSQL esté listo -----
echo "[INFO] Esperando conexión a PostgreSQL (${DB_HOST:-bd}:${DB_PORT:-5432})..."
RETRIES=30
until php -r "
    try {
        \$pdo = new PDO(
            'pgsql:host=${DB_HOST:-bd};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-siga_db}',
            '${DB_USERNAME:-siga_user}',
            '${DB_PASSWORD:-siga_pass}'
        );
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    RETRIES=$((RETRIES - 1))
    if [ $RETRIES -eq 0 ]; then
        echo "[ERROR] PostgreSQL no responde después de 30 intentos. Revisa el servicio 'bd'."
        exit 1
    fi
    echo "[INFO] PostgreSQL no disponible aún, reintentando en 2s... ($RETRIES intentos restantes)"
    sleep 2
done
echo "[OK] PostgreSQL conectado."

# ----- 4. Migraciones -----
echo "[INFO] Ejecutando migraciones..."
php "$APP_DIR/artisan" migrate --force --no-interaction || echo "[WARN] Algunas migraciones fallaron o ya estaban aplicadas."

# ----- 5. Optimización de Laravel -----
echo "[INFO] Optimizando caché de Laravel..."
php "$APP_DIR/artisan" config:cache  --no-interaction || true
php "$APP_DIR/artisan" route:cache   --no-interaction || true
php "$APP_DIR/artisan" view:cache    --no-interaction || true

# ----- 6. Configurar PHP-FPM en socket Unix -----
FPM_CONF="/usr/local/etc/php-fpm.d/www.conf"
if [ -f "$FPM_CONF" ]; then
    sed -i 's|listen = 127.0.0.1:9000|listen = /tmp/php-fpm.sock|' "$FPM_CONF"
    sed -i 's|^;listen.owner|listen.owner|' "$FPM_CONF"
    sed -i 's|^;listen.group|listen.group|' "$FPM_CONF"
    sed -i 's|^;listen.mode|listen.mode|'  "$FPM_CONF"
    sed -i 's|listen.owner = .*|listen.owner = www|' "$FPM_CONF"
    sed -i 's|listen.group = .*|listen.group = www|' "$FPM_CONF"
    sed -i 's|listen.mode = .*|listen.mode = 0660|'  "$FPM_CONF"
fi

# ----- 7. Crear dirs temporales de Nginx -----
mkdir -p /tmp/nginx_client_temp /tmp/nginx_proxy_temp \
         /tmp/nginx_fastcgi_temp /tmp/nginx_uwsgi_temp /tmp/nginx_scgi_temp

echo ""
echo "✔ Contenedor listo."
echo "  → App:      http://localhost:8080  (directo)"
echo "  → Frontend: http://localhost:8000  (proxy)"
echo "============================================="

# ----- 8. Lanzar Supervisor (PHP-FPM + Nginx + Queue) -----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

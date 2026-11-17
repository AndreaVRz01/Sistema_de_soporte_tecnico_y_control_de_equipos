-- =============================================================================
-- PostgreSQL — Script de inicialización SIGA Enterprise
-- docker/bd/init.sql
-- Se ejecuta automáticamente cuando el contenedor 'bd' inicia por primera vez.
-- Variables inyectadas desde .env vía compose.yaml
-- =============================================================================

-- Habilitar extensiones útiles para Laravel + SIGA
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";   -- UUIDs nativos
CREATE EXTENSION IF NOT EXISTS "pg_trgm";     -- Búsqueda trigram (like veloz)
CREATE EXTENSION IF NOT EXISTS "unaccent";    -- Búsqueda sin acentos (español)

-- Configuración de zona horaria para México Central
SET timezone = 'America/Mexico_City';

-- Esquema de auditoría (separado del esquema public)
CREATE SCHEMA IF NOT EXISTS audit;

-- Comentarios descriptivos en la base de datos
COMMENT ON DATABASE siga_db IS
    'Sistema Institucional de Gestión de Activos, Préstamos y Mantenimiento — UPTex 2026';

COMMENT ON SCHEMA public IS
    'Esquema principal de SIGA Enterprise (Laravel migrations)';

COMMENT ON SCHEMA audit IS
    'Esquema de trazabilidad forense y bitácora de auditoría';

-- =============================================================================
-- Privilegios del usuario siga_user (creado por POSTGRES_USER en compose.yaml)
-- =============================================================================
GRANT ALL PRIVILEGES ON DATABASE siga_db TO siga_user;
GRANT ALL PRIVILEGES ON SCHEMA public TO siga_user;
GRANT ALL PRIVILEGES ON SCHEMA audit TO siga_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO siga_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO siga_user;

-- Privilegios futuros (tablas creadas por migraciones de Laravel)
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT ALL PRIVILEGES ON TABLES TO siga_user;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT ALL PRIVILEGES ON SEQUENCES TO siga_user;

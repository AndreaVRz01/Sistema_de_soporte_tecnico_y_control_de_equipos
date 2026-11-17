# =============================================================================
# Vite Build — SIGA Enterprise (docker/vite/Dockerfile)
# Node.js 20 Alpine: compila assets Tailwind/JS para producción
# Corre como usuario node (uid 1000, no-root)
# =============================================================================

FROM node:20-alpine AS vite-builder

# Metadatos
LABEL stage="vite-build"
LABEL description="SIGA Enterprise — Compilación de assets con Vite"

# Usuario node ya existe en la imagen oficial (uid=1000)
WORKDIR /app

# Copiar manifiestos de dependencias primero (caché de capas)
COPY --chown=node:node package.json package-lock.json* ./

# Instalar dependencias Node (solo producción del build)
RUN npm ci --prefer-offline

# Copiar código fuente necesario para el build
COPY --chown=node:node vite.config.js ./
COPY --chown=node:node resources/ ./resources/
COPY --chown=node:node public/ ./public/

# Cambiar a usuario no-root antes del build
USER node

# Compilar assets (output a /app/public/build)
RUN npm run build

# =============================================================================
# STAGE de exportación: solo los assets compilados
# Se usa como COPY --from=vite-builder en el Dockerfile principal
# =============================================================================
FROM scratch AS vite-assets
COPY --from=vite-builder /app/public/build /build

#!/usr/bin/env sh
# Detiene el script ante el primer error.
set -e

# Carpeta base de la aplicacion Laravel.
cd /var/www/html

# Controla si se ejecutan migraciones al inicio.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  # Ruta rapida: usar sqlcmd si esta instalado en la imagen.
  if [ -x "/opt/mssql-tools18/bin/sqlcmd" ]; then
    echo "Waiting for SQL Server..."
    # Reintentos para esperar DB remota (max 30 * 2s = 60s).
    for attempt in $(seq 1 30); do
      if /opt/mssql-tools18/bin/sqlcmd -C -S "${DB_HOST:-db},${DB_PORT:-1433}" -U "${DB_USERNAME:-sa}" -P "${DB_PASSWORD}" -Q "SELECT 1" >/dev/null 2>&1; then
        # Crea la base si no existe y luego aplica migraciones.
        /opt/mssql-tools18/bin/sqlcmd -C -S "${DB_HOST:-db},${DB_PORT:-1433}" -U "${DB_USERNAME:-sa}" -P "${DB_PASSWORD}" -Q "IF DB_ID(N'${DB_DATABASE:-multimar}') IS NULL CREATE DATABASE [${DB_DATABASE:-multimar}]" >/dev/null 2>&1 || true
        php artisan migrate --force
        break
      fi

      if [ "$attempt" -eq 30 ]; then
        echo "SQL Server was not ready in time"
        exit 1
      fi

      sleep 2
    done
  else
    # Fallback: migraciones solo con Laravel (sin sqlcmd).
    echo "mssql-tools18 no instalado: migraciones con reintento"
    for attempt in $(seq 1 30); do
      if php artisan migrate --force; then
        break
      fi

      if [ "$attempt" -eq 30 ]; then
        echo "No se pudo ejecutar migrate despues de varios intentos"
        exit 1
      fi

      sleep 2
    done
  fi
fi

# Ejecuta el comando principal del contenedor (php-fpm -F).
exec "$@"

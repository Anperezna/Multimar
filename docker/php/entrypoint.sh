#!/usr/bin/env sh
set -e

cd /var/www/html

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "Waiting for SQL Server..."
  for attempt in $(seq 1 30); do
    if /opt/mssql-tools18/bin/sqlcmd -C -S "${DB_HOST:-db},${DB_PORT:-1433}" -U "${DB_USERNAME:-sa}" -P "${DB_PASSWORD}" -Q "SELECT 1" >/dev/null 2>&1; then
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
fi

exec "$@"

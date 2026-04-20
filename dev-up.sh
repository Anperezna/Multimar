#!/usr/bin/env sh
set -e

echo "Iniciando aplicacion con Docker..."
docker compose up --build -d

echo ""
echo "App iniciada en: http://localhost:3000"

echo ""
docker compose ps

echo ""
echo "Listo."

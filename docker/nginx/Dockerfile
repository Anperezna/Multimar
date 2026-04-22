# ---------- STAGE 1: Node build ----------
FROM node:20-bookworm-slim AS node_builder

WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build


# ---------- STAGE 2: Nginx ----------
FROM nginx:1.27-bookworm

# Config de reverse proxy para Laravel -> app:9000.
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
# Solo se copia /public (incluye assets ya compilados).
COPY --from=node_builder /app/public /var/www/html/public

# Puerto HTTP del contenedor nginx.
EXPOSE 3000

# ---------- STAGE 1: Node build ----------
FROM node:20-bookworm-slim AS node_builder

WORKDIR /app
# Instala dependencias JS de forma reproducible.
COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY . .
# Genera assets de frontend (public/build) para servirlos con Nginx.
RUN npm run build


# ---------- STAGE 2: PHP base ----------
FROM php:8.2-fpm-bookworm

WORKDIR /var/www/html

# Instalar SOLO lo necesario + limpiar en el mismo layer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        gnupg \
        unzip \
        unixodbc-dev \
        libicu-dev \
        libzip-dev \
        libonig-dev \
    \
    # Microsoft repo
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/microsoft-prod.list \
    \
    && apt-get update \
    && ACCEPT_EULA=Y DEBIAN_FRONTEND=noninteractive \
        apt-get install -y --no-install-recommends \
        msodbcsql18 \
        mssql-tools18 \
    \
    # PHP extensions
    && pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install bcmath intl zip \
    \
    # 🔥 LIMPIEZA CRÍTICA (clave para evitar tu error)
    && apt-get purge -y --auto-remove gnupg \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Composer binario sin instalar PHP Composer globalmente por apt.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar app (sin node_modules)
COPY . .

# Copiar assets ya compilados
COPY --from=node_builder /app/public/build ./public/build

# Instalar dependencias PHP (sin dev)
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Permisos
RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Config
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Solo uso interno para FastCGI; no se publica al host.
EXPOSE 9000

CMD ["php-fpm", "-F"]
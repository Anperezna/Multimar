# ----------- FRONTEND BUILD -----------
FROM node:20-bookworm-slim AS node_builder

WORKDIR /app

# Copiar solo dependencias primero (mejor cache)
COPY package*.json ./

RUN npm ci --omit=optional --no-audit --no-fund

# Copiar resto del proyecto
COPY . .

# Build frontend (Vite, Laravel Mix, etc.)
RUN npm run build


# ----------- PHP APP -----------
FROM php:8.2-cli-bookworm

WORKDIR /var/www/html

# Instalar dependencias del sistema
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gnupg \
        unzip \
        unixodbc-dev \
        libicu-dev \
        libzip-dev \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/microsoft-prod.list \
    && apt-get update \
    && ACCEPT_EULA=Y DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends msodbcsql18 \
    && pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install bcmath intl zip \
    && apt-get purge -y --auto-remove gnupg \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar proyecto
COPY . .

# Instalar PHP deps (sin dev → más ligero)
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader \
    --no-dev

# Copiar SOLO el build del frontend
COPY --from=node_builder /app/public/build ./public/build

# Permisos
RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Config extra
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 3000

ENTRYPOINT ["entrypoint.sh"]
CMD ["sh", "-c", "echo '<---- App disponible en http://localhost:3000 ---->' && php artisan serve --host=0.0.0.0 --port=3000"]
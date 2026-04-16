FROM php:8.2-cli-bookworm AS php-base
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        unixodbc-dev \
        libicu-dev \
        libzip-dev \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc -o /usr/share/keyrings/microsoft.asc \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.asc] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/microsoft-prod.list \
    && apt-get update \
    && ACCEPT_EULA=Y DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends msodbcsql18 mssql-tools18 \
    && pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install \
        bcmath \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM node:20-bookworm-slim AS frontend
WORKDIR /var/www/html

# Force frontend stage to wait for php-base, reducing peak disk usage from parallel builds.
COPY --from=php-base /etc/debian_version /tmp/php-base-marker

COPY package*.json ./
RUN npm ci --include=optional

COPY . .
RUN npm run build

FROM php-base AS backend
WORKDIR /var/www/html

COPY . .
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM backend AS app
RUN rm -rf public/build \
    && mkdir -p public/build \
    && mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY --from=frontend /var/www/html/public/build ./public/build
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 3000

ENTRYPOINT ["entrypoint.sh"]
CMD ["sh", "-c", "echo '<---- App disponible en http://localhost:3000 ---->' && php artisan serve --host=0.0.0.0 --port=3000"]

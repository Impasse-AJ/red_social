# ===== Etapa 1: dependencias PHP con Composer =====
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# ===== Etapa 2: imagen de producción (PHP 8.4 + Apache) =====
# (el composer.lock exige PHP >= 8.4)
FROM php:8.4-apache

# Extensiones que necesita Symfony/Doctrine
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev \
    && docker-php-ext-install pdo_mysql intl opcache \
    && rm -rf /var/lib/apt/lists/*

# Configuración de PHP para producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize = 4M\npost_max_size = 8M\n; PHP debe ver las variables de entorno del contenedor (APP_ENV, DATABASE_URL...)\nvariables_order = EGPCS\n' > "$PHP_INI_DIR/conf.d/symsocial.ini"

# Apache: docroot en public/ y rutas de Symfony vía FallbackResource
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride None\n    Require all granted\n    FallbackResource /index.php\n</Directory>\n' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

WORKDIR /var/www/html

COPY --from=vendor /app ./

# El runtime de Symfony exige que exista .env; la configuración real
# llega por variables de entorno (docker-compose), que siempre ganan
RUN touch .env

# Valores por defecto que no cambian entre entornos
ENV APP_ENV=prod \
    APP_DEBUG=0 \
    LOCK_DSN=flock \
    MESSENGER_TRANSPORT_DSN="doctrine://default?auto_setup=0"

# Compilar los assets (AssetMapper) con variables ficticias: solo hace falta arrancar el kernel
RUN APP_SECRET=build \
    DATABASE_URL="mysql://build:build@db:3306/build?serverVersion=8.0" \
    MAILER_DSN="null://null" \
    MAILER_FROM="build@localhost" \
    php bin/console asset-map:compile

# Directorios escribibles por Apache (caché/logs y fotos de perfil)
RUN mkdir -p var public/uploads \
    && chown -R www-data:www-data var public/uploads

EXPOSE 80

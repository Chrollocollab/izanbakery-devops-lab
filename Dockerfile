# Multi-stage build: compile and install in throwaway stages, ship only PHP-FPM + the app.
# ---- Stage 1: base = PHP-FPM + compiled extensions. Shared by the vendor and final stages. ----
FROM php:8.5-fpm-alpine AS base


# ONE RUN = ONE layer: whatever is installed AND deleted inside it never reaches the image.
# set -eux: -e stop at the first error, -u error on unset variables, -x print each command in the build log.
RUN set -eux \
# Runtime libs: php-fpm loads these at startup, so they STAY in the image.
 && apk add --no-cache libpq libpng libjpeg-turbo freetype libwebp libzip icu-libs \
# Build-only packages, grouped under the fake name .build-deps so one command removes them all.
# libpq-dev (client headers only) replaces postgresql-dev (server headers + clang/LLVM).
 && apk add --no-cache --virtual .build-deps \
      $PHPIZE_DEPS linux-headers \
      libpq-dev libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev libzip-dev icu-dev \
# Tell gd which image formats to support, then compile the core extensions using every CPU core.
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql gd zip intl bcmath exif pcntl \
# redis isn't in PHP's source tree, so build it from PECL and switch it on.
 && pecl install redis \
 && docker-php-ext-enable redis \
# Delete PECL's download and build leftovers.
 && rm -rf /tmp/pear \
# Remove the whole build group (compilers, headers, LLVM). The runtime libs above stay.
 && apk del .build-deps

 # ---- Stage 2: vendor = Composer deps, installed against the SAME PHP + extensions as production. ----
FROM base AS vendor

# App build tools: Composer needs git + unzip, the asset build needs Node + npm (Step 3c removes these).
RUN apk add --no-cache git unzip 

# Copy the Composer binary from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# All following commands run inside this folder
WORKDIR /var/www/html

# Copy app source (.dockerignore keeps vendor/, node_modules/, .env out)
COPY . .

# Production PHP deps: no dev packages, optimized autoloader, no prompts
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ---- Stage 3: assets = compile CSS/JS with Node. Only public/build/ leaves this stage. ----
FROM node:24-alpine AS assets

# Any folder works here; nothing from this path reaches the final image.
WORKDIR /app

# Vite needs package.json, package-lock.json, vite.config.js and resources/ from the app source.
COPY . .

# npm ci = install the exact versions from package-lock.json; npm run build = Vite writes public/build/.
RUN npm ci && npm run build


# ---- Stage 4: final = what ships. Starts from base (PHP-FPM + extensions), with no build tools. ----
FROM base AS final

# Same path as the vendor stage, so Composer's autoloader paths match.
WORKDIR /var/www/html

# App source from the build context (.dockerignore keeps vendor/, node_modules/ and .env out).
COPY . .

# Only Composer's results: the installed packages + the clean no-dev packages.php/services.php.
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=vendor /var/www/html/bootstrap/cache ./bootstrap/cache

# Only Vite's result: the compiled CSS/JS + manifest.json.
COPY --from=assets /app/public/build ./public/build

# Laravel writes logs/cache/sessions here; php-fpm's workers run as www-data.
RUN chown -R www-data:www-data storage bootstrap/cache

# php-fpm listens on port 9000 (CMD inherited from the base image).

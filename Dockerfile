# "Before" image: single-stage, unoptimized on purpose.
FROM php:8.5-fpm-alpine

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

# App build tools: Composer needs git + unzip, the asset build needs Node + npm (Step 3c removes these).
RUN apk add --no-cache git unzip nodejs npm

# Copy the Composer binary from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# All following commands run inside this folder
WORKDIR /var/www/html

# Copy app source (.dockerignore keeps vendor/, node_modules/, .env out)
COPY . .

# Production PHP deps: no dev packages, optimized autoloader, no prompts
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install exact JS deps from package-lock.json, then build frontend assets
RUN npm ci \
 && npm run build

# Laravel needs to write logs/cache here; php-fpm runs as www-data
RUN chown -R www-data:www-data storage bootstrap/cache

# php-fpm listens on port 9000 (CMD inherited from the base image)
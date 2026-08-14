FROM php:7.4-fpm-bullseye

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        xml \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer 1: Laravel 5.5 (< 5.5.49) quebra package:discover com Composer 2
# (PackageManifest: Undefined index: name)
COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer

# Node 14: laravel-mix 1 puxa node-sass@4, que NÃO tem binário para Node 16
# (em Node 16 o npm tenta compilar e falha sem python/g++)
COPY --from=node:14-bullseye /usr/local/bin/node /usr/local/bin/node
COPY --from=node:14-bullseye /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && node -v && npm -v

# Ferramentas nativas (fallback se algum pacote precisar rebuild)
RUN apt-get update && apt-get install -y --no-install-recommends \
        python3 \
        make \
        g++ \
    && ln -sf /usr/bin/python3 /usr/bin/python \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]

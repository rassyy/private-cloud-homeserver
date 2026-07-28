# Stage 1: Build Frontend Assets (Vue + Tailwind v4 + Vite)
FROM node:20-alpine AS frontend-builder
RUN apk add --no-cache php83 php83-phar php83-mbstring php83-xml php83-dom php83-tokenizer php83-simplexml php83-openssl \
    && ln -s /usr/bin/php83 /usr/bin/php
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader --no-scripts --ignore-platform-req=ext-session --ignore-platform-req=ext-fileinfo --ignore-platform-req=ext-iconv
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production PHP 8.3 + Nginx Container
FROM php:8.3-fpm-alpine

# Install system dependencies & PHP extensions required by Laravel & MySQL
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .
# Copy built Vue frontend assets from stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copy Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
# Copy custom PHP upload limits
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
# Override FPM listen to IPv4
RUN sed -i 's/^listen = 9000/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/docker.conf
# Copy Supervisor config to run PHP-FPM and Nginx together
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

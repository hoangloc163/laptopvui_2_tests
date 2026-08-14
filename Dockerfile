# Dockerfile - PHP 8.4 web server for Laptop Vui
# Place this in the banhang/ repo root

FROM php:8.4-cli-alpine

# Install required PHP extensions
RUN apk add --no-cache \
        sqlite-dev \
        libpng-dev \
        libwebp-dev \
        libjpeg-turbo-dev \
        oniguruma-dev \
        curl \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        mbstring \
        fileinfo \
        gd

# Working directory
WORKDIR /app

# Copy application code
COPY . /app

# Ensure upload and data directories are writable
RUN mkdir -p /app/upload /var/data \
    && chmod 755 /app/upload \
    && chmod 755 /var/data

# Render provides $PORT environment variable
ENV PORT=10000
EXPOSE 10000

# Start PHP built-in server using dev-router.php for URL rewriting
# In production, replace with nginx + php-fpm for better performance
CMD php -S 0.0.0.0:${PORT} -t . dev-router.php

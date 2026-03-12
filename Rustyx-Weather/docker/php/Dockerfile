# Imagen base: PHP 8.2 con FPM sobre Alpine (ligera)
FROM php:8.2-fpm-alpine

# Instalamos las dependencias del sistema y las extensiones PHP necesarias
RUN apk add --no-cache \
    curl \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

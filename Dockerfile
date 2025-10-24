FROM php:8.3-fpm-bullseye

# Installation des dépendances système nécessaires pour les extensions PHP
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        zip \
        curl \
        ca-certificates \
        gnupg \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype-dev \
        libicu-dev \
        libxml2-dev \
        acl \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g sass

RUN docker-php-ext-install pdo_mysql intl zip xml dom opcache

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd -j$(nproc) gd

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN echo "upload_max_filesize=8M" > /usr/local/etc/php/conf.d/99-upload.ini \
 && echo "post_max_size=10M" >> /usr/local/etc/php/conf.d/99-upload.ini

# Définition du répertoire de travail
WORKDIR /code

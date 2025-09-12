# On part d'une image PHP basée sur Debian (Bullseye), plus standard et compatible.
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
# On utilise npm pour installer sass de manière globale, le rendant accessible partout
    && npm install -g sass

# Installation des extensions PHP vitales pour Symfony
RUN docker-php-ext-install pdo_mysql intl zip xml dom opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 🔧 Config PHP en ligne (pas de fichier .ini séparé)
RUN echo "upload_max_filesize=8M" > /usr/local/etc/php/conf.d/99-upload.ini \
 && echo "post_max_size=10M" >> /usr/local/etc/php/conf.d/99-upload.ini

# Définition du répertoire de travail
WORKDIR /code

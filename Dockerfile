# On part d'une image PHP basée sur Debian (Bullseye), plus standard et compatible.
FROM php:8.3-fpm-bullseye

# Installation des dépendances système nécessaires pour les extensions PHP
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP vitales pour Symfony
RUN docker-php-ext-install pdo_mysql intl zip xml dom

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Définition du répertoire de travail
WORKDIR /code

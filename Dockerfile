# Utilisation de l'image PHP avec Apache
FROM php:8.2-apache

# Activation des modules Apache nécessaires
RUN a2enmod rewrite


# Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    libz-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql intl

# Installation de l'extension gRPC
RUN pecl install grpc \
    && docker-php-ext-enable grpc

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers du projet
COPY . /var/www/html/

# 📌 Création des répertoires cache et logs AVANT d'appliquer chmod
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var /var/www/html/var/cache /var/www/html/var/log




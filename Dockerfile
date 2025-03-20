# Utilisation de l'image PHP avec Apache
FROM php:8.2-apache

# Activation des modules Apache nécessaires
RUN a2enmod rewrite

# Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y libpq-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www/html

# Ajouter l'utilisateur www-data si il n'existe pas déjà
RUN useradd -m www-data || true

# Copie des fichiers du projet
COPY . /var/www/html/

# Vérifie que les dossiers de cache et logs existent
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

# Installation des dépendances Symfony
RUN composer install --no-interaction --no-progress --optimize-autoloader

# Exposition du port Apache
EXPOSE 80

# Définition du script d’entrée
ENTRYPOINT ["sh", "/var/www/html/entrypoint.sh"]

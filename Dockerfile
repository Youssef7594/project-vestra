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

# Copie des fichiers du projet
COPY . /var/www/html/

# Donne les permissions correctes pour les logs et le cache Symfony
RUN mkdir -p /var/www/html/var && chown -R www-data:www-data /var/www/html/var

# Exposition du port Apache
EXPOSE 80

# Définition du script d’entrée
ENTRYPOINT ["sh", "/var/www/html/entrypoint.sh"]

#!/bin/sh

# Vérifie que les dossiers nécessaires existent
mkdir -p var/cache var/log
chmod -R 777 var

# Vérifie que les dépendances sont installées
if [ ! -d "vendor" ]; then
    composer install --no-interaction --no-progress --optimize-autoloader
fi

# Attendre que la base de données soit prête
echo "🕐 Attente de la base de données..."
sleep 10

# Exécute les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Démarre Apache
apache2-foreground

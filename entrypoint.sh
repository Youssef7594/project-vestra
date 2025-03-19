#!/bin/bash
set -e

# Attendre que la base de données soit prête
echo "🕐 Attente de la base de données..."
sleep 10

# Vérifier si les migrations sont nécessaires
php bin/console doctrine:migrations:migrate --no-interaction || true

# Donner les bonnes permissions
chmod -R 777 var/cache var/log

# Lancer Apache
exec apache2-foreground

#!/bin/bash

# Script de démarrage pour l'application e-commerce Docker

set -e

echo "🚀 Démarrage de l'application e-commerce..."

# Attendre que MySQL soit prêt
echo "⏳ Attente de MySQL..."
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "MySQL n'est pas encore prêt..."
    sleep 2
done
echo "✅ MySQL est prêt!"

# Attendre que Redis soit prêt
echo "⏳ Attente de Redis..."
until redis-cli -h redis ping 2>/dev/null; do
    echo "Redis n'est pas encore prêt..."
    sleep 2
done
echo "✅ Redis est prêt!"

# Générer la clé d'application si elle n'existe pas
if [ -z "$APP_KEY" ]; then
    echo "🔑 Génération de la clé d'application..."
    php artisan key:generate --force
fi

# Exécuter les migrations
echo "📊 Exécution des migrations..."
php artisan migrate --force

# Vider et recréer le cache
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimiser pour la production
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimisation pour la production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Créer le lien symbolique pour le stockage
echo "🔗 Création du lien de stockage..."
php artisan storage:link

echo "✅ Application prête!"

# Démarrer PHP-FPM
exec php-fpm

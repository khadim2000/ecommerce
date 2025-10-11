# Configuration Docker pour l'Application E-commerce

Ce guide explique comment configurer et utiliser Docker pour l'application e-commerce Laravel + Vue.js.

## 🏗️ Architecture

L'application utilise une architecture multi-conteneurs avec les services suivants :

- **app** : Application Laravel (PHP-FPM)
- **nginx** : Serveur web
- **mysql** : Base de données
- **redis** : Cache et sessions
- **worker** : Traitement des tâches en arrière-plan
- **node** : Développement frontend (profil dev uniquement)
- **mailhog** : Test des emails (profil dev uniquement)

## 🚀 Démarrage rapide

### 1. Configuration de l'environnement

Copiez le fichier d'environnement :
```bash
cp .env.docker.example .env
```

Modifiez les variables selon vos besoins dans `.env`.

### 2. Construction et démarrage

```bash
# Construction et démarrage de tous les services
docker-compose up -d --build

# Ou pour le développement (avec hot reload)
docker-compose --profile dev up -d --build
```

### 3. Initialisation de l'application

```bash
# Entrer dans le conteneur de l'application
docker-compose exec app bash

# Installer les dépendances (si nécessaire)
composer install
npm install

# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate

# Seeder la base de données
php artisan db:seed

# Créer le lien de stockage
php artisan storage:link
```

## 🔧 Commandes utiles

### Gestion des conteneurs

```bash
# Voir les logs
docker-compose logs -f

# Redémarrer un service
docker-compose restart app

# Arrêter tous les services
docker-compose down

# Arrêter et supprimer les volumes
docker-compose down -v
```

### Commandes Laravel

```bash
# Entrer dans le conteneur
docker-compose exec app bash

# Exécuter les migrations
docker-compose exec app php artisan migrate

# Vider le cache
docker-compose exec app php artisan cache:clear

# Regénérer l'autoload
docker-compose exec app composer dump-autoload
```

### Développement frontend

```bash
# Mode développement avec hot reload
docker-compose --profile dev up -d

# Build des assets
docker-compose exec app npm run build
```

## 🌐 Accès aux services

- **Application** : http://localhost
- **MySQL** : localhost:3306
- **Redis** : localhost:6379
- **Mailhog** (dev) : http://localhost:8025
- **Vite** (dev) : http://localhost:5173

## 📁 Structure des fichiers Docker

```
docker/
├── nginx/
│   └── default.conf      # Configuration Nginx
├── php/
│   └── local.ini         # Configuration PHP
├── mysql/
│   └── my.cnf            # Configuration MySQL
└── start.sh              # Script de démarrage
```

## 🔒 Sécurité

- L'application s'exécute avec un utilisateur non-root
- Configuration sécurisée de PHP (expose_php = Off)
- Headers de sécurité configurés dans Nginx
- Base de données avec authentification native MySQL

## 🚀 Production

Pour la production :

1. Modifiez `APP_ENV=production` dans `.env`
2. Configurez les variables de paiement
3. Activez SSL dans la configuration Nginx
4. Utilisez des volumes persistants pour les données

## 🐛 Dépannage

### Problèmes courants

1. **Erreur de permissions** :
   ```bash
   sudo chown -R $USER:$USER .
   ```

2. **Port déjà utilisé** :
   Modifiez les ports dans `.env`

3. **Base de données non accessible** :
   Vérifiez que MySQL est démarré avant l'application

### Logs

```bash
# Logs de l'application
docker-compose logs app

# Logs de la base de données
docker-compose logs mysql

# Logs de Nginx
docker-compose logs nginx
```

## 📦 Optimisations

- Build multi-stage pour réduire la taille de l'image
- Cache des layers Docker optimisé
- Configuration OpCache pour PHP
- Compression gzip dans Nginx
- Images Alpine Linux pour réduire la taille

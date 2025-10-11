# Multi-stage Dockerfile pour Laravel + Vue.js

# -------------------------------
# Stage 1: Build frontend assets
# -------------------------------
    FROM node:18-alpine AS frontend-build

    WORKDIR /app
    
    # Copier seulement les fichiers package.json pour le cache des layers
    COPY package*.json ./
    
    # Installer les dépendances Node.js
    RUN npm ci
    
    # Copier les ressources frontend
    COPY resources/ ./resources/
    COPY tailwind.config.js postcss.config.js vite.config.js jsconfig.json ./
    
    # Build des assets
    RUN npm run build
    
    # -------------------------------
    # Stage 2: Base PHP avec extensions
    # -------------------------------
    FROM php:8.2-fpm-alpine AS php-base
    
    # Installer les dépendances système
    RUN apk add --no-cache \
        git curl libpng-dev libxml2-dev zip unzip oniguruma-dev \
        freetype-dev libjpeg-turbo-dev libzip-dev sqlite-dev icu-dev libpq-dev
    
    # Installer les extensions PHP
    RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install \
            pdo pdo_mysql pdo_sqlite pdo_pgsql mbstring exif pcntl bcmath gd zip intl opcache
    
    # Installer Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    
    # Configurer PHP
    RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini \
        && echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini \
        && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini \
        && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/docker-php-execution.ini
    
    # -------------------------------
    # Stage 3: Application finale
    # -------------------------------
    FROM php-base AS app
    
    # Créer un utilisateur non-root
    RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www
    
    # Définir le répertoire de travail
    WORKDIR /var/www/html
    
    # Copier composer.json et composer.lock
    COPY composer.json composer.lock 
    
    # Installer les dépendances Composer
    RUN composer install --no-dev --optimize-autoloader --no-interaction
    
    # Copier tout le code de l'application
    COPY --chown=www:www . .
    
    # Copier les assets buildés depuis le stage frontend
    COPY --from=frontend-build /app/dist ./public/build
    
    # Configurer les permissions
    RUN chown -R www:www /var/www/html \
        && chmod -R 755 /var/www/html/storage \
        && chmod -R 755 /var/www/html/bootstrap/cache
    
    # Créer le lien symbolique pour le stockage
    RUN php artisan storage:link || true
    
    # Optimiser Laravel pour la production
    RUN php artisan config:cache || true \
        && php artisan route:cache || true \
        && php artisan view:cache || true
    
    # Exposer le port
    EXPOSE 9000
    
    # Changer vers l'utilisateur non-root
    USER www
    
    # Démarrer PHP-FPM
    CMD ["php-fpm"]
    
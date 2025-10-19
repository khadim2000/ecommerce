#!/bin/bash
set -e

# Charger nvm
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Lancer MySQL en mode skip-grant-tables
sudo systemctl stop mysql
sudo mysqld_safe --skip-grant-tables &

# Utiliser Node.js
nvm use 18.17.0

# Lancer Laravel
php artisan serve &
LARAVEL_PID=$!

# Lancer Vue.js
npm run dev &
VUE_PID=$!

# Arrêt propre
trap 'kill $LARAVEL_PID $VUE_PID 2>/dev/null; exit 0' INT
wait

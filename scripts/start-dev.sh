#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/.."

if ! command -v docker >/dev/null 2>&1; then
    echo "Erreur : Docker n'est pas disponible. Demarrez Docker Desktop puis reessayez." >&2
    exit 1
fi

echo "Demarrage de MySQL..."
docker compose up -d --build --wait mysql

echo "Nettoyage du cache de configuration Laravel..."
php artisan config:clear

echo "MySQL est pret. Demarrage de l'application..."
exec composer dev

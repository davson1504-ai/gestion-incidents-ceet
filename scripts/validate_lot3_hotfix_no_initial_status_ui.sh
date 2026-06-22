#!/usr/bin/env bash
set -euo pipefail
PROJECT="$(pwd)"
FILE="$PROJECT/resources/views/components/incidents/form.blade.php"

echo "==> Vérification absence du bloc Statut initial"
if grep -n "Statut initial\|AFFECTE si un opérateur est affecté" "$FILE"; then
  echo "ECHEC: le texte Statut initial est encore présent dans $FILE" >&2
  exit 1
fi

echo "OK: aucun texte de statut initial affiché dans le formulaire."

echo "==> php artisan optimize:clear"
php artisan optimize:clear

echo "==> php artisan view:clear"
php artisan view:clear

echo "==> npm run build"
npm run build

echo "==> php artisan test"
php artisan test

echo "Validation hotfix terminée."

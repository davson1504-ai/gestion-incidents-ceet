#!/usr/bin/env bash
set -euo pipefail

PROJECT="${PROJECT:-$(pwd)}"
if [[ "${1:-}" == "--project" && -n "${2:-}" ]]; then
  PROJECT="$2"
fi
if [[ "${1:-}" == --project=* ]]; then
  PROJECT="${1#--project=}"
fi

cd "$PROJECT"

TARGET="tests/Feature/Incidents/IncidentWorkflowTest.php"

echo "==> Vérification test mis à jour"
if grep -q "assertSee('Statut initial')" "$TARGET"; then
  echo "ECHEC: le test attend encore 'Statut initial'."
  exit 1
fi

if ! grep -q "assertDontSee('Statut initial')" "$TARGET"; then
  echo "ECHEC: le test ne vérifie pas encore l'absence de 'Statut initial'."
  exit 1
fi

echo "OK: test aligné avec l'UI demandée."

echo "==> php artisan optimize:clear"
php artisan optimize:clear

echo "==> php artisan view:clear"
php artisan view:clear

echo "==> npm run build"
npm run build

echo "==> php artisan test --filter='incident create form does not allow free initial status choice'"
php artisan test --filter='incident create form does not allow free initial status choice'

echo "==> php artisan test"
php artisan test

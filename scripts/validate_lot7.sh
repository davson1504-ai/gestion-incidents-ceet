#!/usr/bin/env bash
set -euo pipefail
PROJECT="${PWD}"
for arg in "$@"; do
  case "$arg" in
    --project=*) PROJECT="${arg#*=}" ;;
  esac
done
cd "$PROJECT"
echo "==> Vérification fichiers Lot 7"
required=(
  "resources/views/pages/catalogues/index.blade.php"
  "resources/views/pages/catalogues/departements/index.blade.php"
  "resources/views/pages/catalogues/types/index.blade.php"
  "resources/views/pages/catalogues/causes/index.blade.php"
  "resources/views/pages/catalogues/statuts/index.blade.php"
  "resources/views/pages/catalogues/priorites/index.blade.php"
  "resources/views/pages/historique/index.blade.php"
  "resources/views/pages/system/status.blade.php"
  "resources/views/exports/historique-pdf.blade.php"
  "resources/css/pages/catalogues.css"
  "resources/js/pages/catalogues.js"
  "resources/css/pages/historique.css"
  "resources/js/pages/historique.js"
  "resources/css/pages/system-status.css"
  "resources/js/pages/system-status.js"
)
for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then echo "ECHEC: fichier manquant $file" >&2; exit 1; fi
  echo "OK: $file"
done

checks=(
  "app/Http/Controllers/DepartementController.php:view('pages.catalogues.departements.index'"
  "app/Http/Controllers/TypeIncidentController.php:view('pages.catalogues.types.index'"
  "app/Http/Controllers/CauseController.php:view('pages.catalogues.causes.index'"
  "app/Http/Controllers/StatutController.php:view('pages.catalogues.statuts.index'"
  "app/Http/Controllers/PrioriteController.php:view('pages.catalogues.priorites.index'"
  "app/Http/Controllers/HistoriqueController.php:view('pages.historique.index'"
  "app/Http/Controllers/HistoriqueController.php:exports.historique-pdf"
  "app/Http/Controllers/SystemStatusController.php:view('pages.system.status'"
  "routes/web.php:view('pages.catalogues.index'"
)
for item in "${checks[@]}"; do
  file="${item%%:*}"; needle="${item#*:}"
  if ! grep -q "$needle" "$file"; then echo "ECHEC: $file ne contient pas $needle" >&2; exit 1; fi
  echo "OK: $file"
done

if grep -REiq "<!DOCTYPE html>|<html[[:space:]>]|<head[[:space:]>]|<body[[:space:]>]|ceet-.*sidebar|ceet-.*topbar" resources/views/pages/catalogues resources/views/pages/historique resources/views/pages/system; then
  echo "ECHEC: une page Lot 7 contient encore une structure HTML/sidebar/topbar dupliquée" >&2
  exit 1
fi
echo "OK: pages Lot 7 sans duplication html/head/body/sidebar/topbar"

for entry in \
  "resources/css/pages/catalogues.css" "resources/js/pages/catalogues.js" \
  "resources/css/pages/historique.css" "resources/js/pages/historique.js" \
  "resources/css/pages/system-status.css" "resources/js/pages/system-status.js"; do
  if ! grep -q "$entry" vite.config.js; then echo "ECHEC: entrée Vite manquante $entry" >&2; exit 1; fi
done
echo "OK: entrées Vite Lot 7 présentes"

run_step() {
  echo
  echo "==> $*"
  "$@"
  echo "OK: $*"
}
run_step php artisan optimize:clear
run_step php artisan view:clear
run_step npm run build
run_step php artisan test

echo
echo "Validation Lot 7 terminée: OK."

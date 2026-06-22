#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(pwd)"
for arg in "$@"; do
  case "$arg" in
    --project=*) PROJECT_DIR="${arg#--project=}" ;;
  esac
done

cd "$PROJECT_DIR"

echo "==> Vérification fichiers Lot 5"
for file in \
  resources/views/pages/users/index.blade.php \
  resources/views/pages/users/create.blade.php \
  resources/views/pages/users/edit.blade.php \
  resources/views/components/users/form.blade.php \
  resources/css/pages/users.css \
  resources/js/pages/users.js; do
  test -f "$file" || { echo "ECHEC: fichier manquant $file"; exit 1; }
  echo "OK: $file"
done

grep -q "view('pages.users.index'" app/Http/Controllers/UserController.php || { echo "ECHEC: UserController ne pointe pas vers pages.users.index"; exit 1; }
grep -q "view('pages.users.create'" app/Http/Controllers/UserController.php || { echo "ECHEC: UserController ne pointe pas vers pages.users.create"; exit 1; }
grep -q "view('pages.users.edit'" app/Http/Controllers/UserController.php || { echo "ECHEC: UserController ne pointe pas vers pages.users.edit"; exit 1; }
echo "OK: UserController pointe vers pages.users.*"

if grep -Eq "<!DOCTYPE html>|<html|<head|<body|app-sidebar|app-topbar" resources/views/pages/users/*.blade.php; then
  echo "ECHEC: une page users contient encore une structure HTML complète ou une duplication shell."
  exit 1
fi
echo "OK: pages users sans duplication html/head/body/sidebar/topbar"

grep -q "resources/css/pages/users.css" vite.config.js || { echo "ECHEC: users.css absent de vite.config.js"; exit 1; }
grep -q "resources/js/pages/users.js" vite.config.js || { echo "ECHEC: users.js absent de vite.config.js"; exit 1; }
echo "OK: entrées Vite users présentes"

run() {
  echo
  echo "==> $*"
  "$@"
  echo "OK: $*"
}

run php artisan optimize:clear
run php artisan view:clear
run npm run build
run php artisan test

echo
echo "Validation Lot 5 terminée: OK."

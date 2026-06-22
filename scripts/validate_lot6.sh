#!/usr/bin/env bash
set -euo pipefail
PROJECT="${PWD}"
for arg in "$@"; do
  case "$arg" in
    --project=*) PROJECT="${arg#*=}" ;;
  esac
done

cd "$PROJECT"

echo "==> Vérification fichiers Lot 6"
required=(
  "resources/views/pages/profile/edit.blade.php"
  "resources/css/pages/profile.css"
  "resources/js/pages/profile.js"
)
for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "ECHEC: fichier manquant $file" >&2
    exit 1
  fi
  echo "OK: $file"
done

if ! grep -q "view('pages.profile.edit'" app/Http/Controllers/ProfileController.php; then
  echo "ECHEC: ProfileController ne pointe pas vers pages.profile.edit" >&2
  exit 1
fi
echo "OK: ProfileController pointe vers pages.profile.edit"

if grep -REiq "<!DOCTYPE html>|<html([[:space:]>])|<head([[:space:]>])|<body([[:space:]>])|ceet-profile-sidebar|ceet-profile-topbar" resources/views/pages/profile/edit.blade.php; then
  echo "ECHEC: page profil contient encore une structure HTML/sidebar/topbar dupliquée" >&2
  exit 1
fi
echo "OK: page profil sans duplication html/head/body/sidebar/topbar"

if ! grep -q "resources/css/pages/profile.css" vite.config.js || ! grep -q "resources/js/pages/profile.js" vite.config.js; then
  echo "ECHEC: entrées Vite profile manquantes" >&2
  exit 1
fi
echo "OK: entrées Vite profile présentes"

if ! grep -q "route('password.update')" resources/views/pages/profile/edit.blade.php; then
  echo "ECHEC: formulaire mot de passe manquant" >&2
  exit 1
fi
echo "OK: formulaire mot de passe présent"

if ! grep -q "Avatar" resources/views/pages/profile/edit.blade.php; then
  echo "ECHEC: bloc avatar manquant" >&2
  exit 1
fi
echo "OK: bloc avatar présent"

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
echo "Validation Lot 6 terminée: OK."

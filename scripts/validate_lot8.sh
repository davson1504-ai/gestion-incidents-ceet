#!/usr/bin/env bash
set -euo pipefail

PROJECT="$(pwd)"
for arg in "$@"; do
  case "$arg" in
    --project=*) PROJECT="${arg#--project=}" ;;
    *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
  esac
done

cd "$PROJECT"

if [ ! -f artisan ]; then
  echo "ECHEC: ce script doit être lancé à la racine du projet Laravel." >&2
  exit 1
fi

ok_file() {
  local path="$1"
  if [ ! -f "$path" ]; then
    echo "ECHEC: fichier manquant: $path" >&2
    exit 1
  fi
  echo "OK: $path"
}

absent_path() {
  local path="$1"
  if [ -e "$path" ]; then
    echo "ECHEC: legacy encore présent: $path" >&2
    exit 1
  fi
  echo "OK absent: $path"
}

grep_absent() {
  local pattern="$1"
  local label="$2"
  shift 2
  if grep -R -n -E "$pattern" "$@" >/tmp/ceet_lot8_grep.txt 2>/dev/null; then
    echo "ECHEC: $label" >&2
    cat /tmp/ceet_lot8_grep.txt >&2
    exit 1
  fi
  echo "OK: $label"
}

echo "==> Vérification structure finale"
ok_file "resources/views/layouts/app.blade.php"
ok_file "resources/views/components/app-sidebar.blade.php"
ok_file "resources/views/components/app-topbar.blade.php"
ok_file "resources/views/pages/admin/dashboard.blade.php"
ok_file "resources/views/pages/supervisor/dashboard.blade.php"
ok_file "resources/views/pages/operator/dashboard.blade.php"
ok_file "resources/views/pages/incidents/index.blade.php"
ok_file "resources/views/pages/incidents/create.blade.php"
ok_file "resources/views/pages/incidents/edit.blade.php"
ok_file "resources/views/pages/incidents/show.blade.php"
ok_file "resources/views/pages/incidents/mine.blade.php"
ok_file "resources/views/pages/incidents/en-cours.blade.php"
ok_file "resources/views/pages/reports/index.blade.php"
ok_file "resources/views/pages/users/index.blade.php"
ok_file "resources/views/pages/profile/edit.blade.php"
ok_file "resources/views/pages/catalogues/index.blade.php"
ok_file "resources/views/pages/historique/index.blade.php"
ok_file "resources/views/pages/system/status.blade.php"
ok_file "resources/views/exports/reports-incidents-pdf.blade.php"
ok_file "resources/views/exports/reports-incidents-excel.blade.php"
ok_file "resources/views/exports/historique-pdf.blade.php"
ok_file "docs/AUDIT_FINAL_LOT8.md"

echo
echo "==> Vérification fichiers conservés volontairement"
ok_file "resources/views/incidents/vue-console.blade.php"
ok_file "resources/views/exports/incidents-pdf.blade.php"
ok_file "resources/css/pages/admin-dashboard.css"
ok_file "resources/js/pages/admin-dashboard.js"
ok_file "resources/views/partials/ceet-role-nav.blade.php"

echo
echo "==> Vérification suppression legacy"
absent_path "resources/views/dashboard.blade.php"
absent_path "resources/views/dashboard-supervisor.blade.php"
absent_path "resources/views/dashboard-operator.blade.php"
absent_path "resources/views/incidents/_form.blade.php"
absent_path "resources/views/incidents/create.blade.php"
absent_path "resources/views/incidents/edit.blade.php"
absent_path "resources/views/incidents/en-cours.blade.php"
absent_path "resources/views/incidents/index.blade.php"
absent_path "resources/views/incidents/mine.blade.php"
absent_path "resources/views/incidents/show.blade.php"
absent_path "resources/views/reports"
absent_path "resources/views/users"
absent_path "resources/views/profile"
absent_path "resources/views/catalogues"
absent_path "resources/views/historique"
absent_path "resources/views/system"
absent_path "resources/css/pages/supervisor-dashboard.css"
absent_path "resources/js/pages/supervisor-dashboard.js"
absent_path "resources/css/pages/operator-dashboard.css"
absent_path "resources/js/pages/operator-dashboard.js"
absent_path "resources/css/pages/dashboard.css"
absent_path "resources/js/pages/dashboard.js"
absent_path "resources/css/pages/incidents.css"
absent_path "resources/js/pages/incidents.js"
absent_path "resources/js/incident-form.js"

echo
echo "==> Vérification staging supprimé"
for path in files hotfix_files lot2_files lot3_files lot4_files lot5_files lot6_files lot7_files ceet_lot1_scripts_rapides ceet_lot2_scripts_rapides ceet_lot3_scripts_rapides ceet_lot4_scripts_rapides ceet_lot5_scripts_rapides ceet_lot6_scripts_rapides ceet_lot7_scripts_rapides ceet_lot8_scripts_rapides; do
  if [ -e "$path" ]; then
    echo "AVERTISSEMENT: dossier de staging encore présent: $path"
  else
    echo "OK absent: $path"
  fi
done

echo
echo "==> Vérification références Blade côté contrôleurs"
grep_absent "view\(['\"]dashboard|view\(['\"]dashboard-supervisor|view\(['\"]dashboard-operator|view\(['\"]incidents\.(index|create|edit|en-cours|mine|show)|view\(['\"]reports\.|view\(['\"]users\.|view\(['\"]profile\.edit|view\(['\"]catalogues\.|view\(['\"]historique\.index|loadView\(['\"]historique\.export-pdf|view\(['\"]system\.status" "plus aucune référence contrôleur vers les anciennes vues migrées" app routes

echo
echo "==> Vérification pages sans duplication html/head/body/sidebar/topbar"
grep_absent "<html|<head[[:space:]>]|<body|<x-app-sidebar|<x-app-topbar|@include\(['\"]partials\.ceet-role-nav" "pages/* sans structure HTML ou navigation legacy dupliquée" resources/views/pages

echo
echo "==> Vérification vite.config.js"
if grep -q "resources/css/pages/supervisor-dashboard.css\|resources/js/pages/supervisor-dashboard.js\|resources/css/pages/operator-dashboard.css\|resources/js/pages/operator-dashboard.js" vite.config.js; then
  echo "ECHEC: vite.config.js contient encore des entrées legacy supervisor/operator." >&2
  exit 1
fi
for entry in \
  "resources/css/pages/dashboard-admin.css" \
  "resources/js/pages/dashboard-admin.js" \
  "resources/css/pages/dashboard-supervisor.css" \
  "resources/js/pages/dashboard-supervisor.js" \
  "resources/css/pages/dashboard-operator.css" \
  "resources/js/pages/dashboard-operator.js" \
  "resources/css/pages/incidents-index.css" \
  "resources/js/pages/incidents-index.js" \
  "resources/css/pages/reports.css" \
  "resources/js/pages/reports.js" \
  "resources/css/pages/users.css" \
  "resources/js/pages/users.js" \
  "resources/css/pages/profile.css" \
  "resources/js/pages/profile.js" \
  "resources/css/pages/catalogues.css" \
  "resources/js/pages/catalogues.js" \
  "resources/css/pages/historique.css" \
  "resources/js/pages/historique.js" \
  "resources/css/pages/system-status.css" \
  "resources/js/pages/system-status.js"; do
  if ! grep -q "$entry" vite.config.js; then
    echo "ECHEC: entrée Vite manquante: $entry" >&2
    exit 1
  fi
  echo "OK Vite: $entry"
done

echo
echo "==> php artisan optimize:clear"
php artisan optimize:clear
echo "OK: php artisan optimize:clear"

echo
echo "==> php artisan view:clear"
php artisan view:clear
echo "OK: php artisan view:clear"

echo
echo "==> npm run build"
npm run build
echo "OK: npm run build"

echo
echo "==> php artisan test"
php artisan test
echo "OK: php artisan test"

echo
echo "Validation Lot 8 terminée: OK."

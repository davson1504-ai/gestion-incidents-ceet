#!/usr/bin/env bash
set -u -o pipefail

PROJECT_DIR="$(pwd)"
for arg in "$@"; do
  case "$arg" in
    --project=*) PROJECT_DIR="${arg#--project=}" ;;
  esac
done

cd "$PROJECT_DIR" || exit 1

fail=0
run_step() {
  local label="$1"
  shift
  echo ""
  echo "==> $label"
  "$@"
  local code=$?
  if [[ $code -eq 0 ]]; then
    echo "OK: $label"
  else
    echo "ECHEC: $label"
    fail=1
  fi
}

echo "==> Vérification fichiers Lot 4"
for file in \
  resources/views/pages/reports/index.blade.php \
  resources/views/exports/reports-incidents-pdf.blade.php \
  resources/views/exports/reports-incidents-excel.blade.php \
  resources/css/pages/reports.css \
  resources/js/pages/reports.js; do
  if [[ -f "$file" ]]; then
    echo "OK: $file"
  else
    echo "ECHEC: $file manquant"
    fail=1
  fi
done

if grep -q "view('pages.reports.index'" app/Http/Controllers/ReportController.php; then
  echo "OK: ReportController pointe vers pages.reports.index"
else
  echo "ECHEC: ReportController ne pointe pas vers pages.reports.index"
  fail=1
fi

if grep -q "exports.reports-incidents-pdf" app/Http/Controllers/ReportController.php; then
  echo "OK: ReportController utilise le template PDF export dédié"
else
  echo "ECHEC: template PDF export dédié non utilisé"
  fail=1
fi

if grep -R "<!DOCTYPE html>" resources/views/pages/reports/index.blade.php >/dev/null 2>&1; then
  echo "ECHEC: la page reports contient encore un document HTML complet"
  fail=1
else
  echo "OK: page reports sans duplication html/head/body"
fi

run_step "php artisan optimize:clear" php artisan optimize:clear
run_step "php artisan view:clear" php artisan view:clear
run_step "npm run build" npm run build
run_step "php artisan test" php artisan test

echo ""
if [[ "$fail" -eq 0 ]]; then
  echo "Validation Lot 4 terminée: OK."
else
  echo "Validation Lot 4 terminée avec erreurs."
fi
exit "$fail"

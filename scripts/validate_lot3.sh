#!/usr/bin/env bash
set -euo pipefail
PROJECT="$(pwd)"; STRICT=0
for arg in "$@"; do case "$arg" in --project=*) PROJECT="${arg#--project=}" ;; --strict) STRICT=1 ;; *) echo "Argument inconnu: $arg"; exit 1 ;; esac; done
cd "$PROJECT"
run_step(){ local label="$1"; shift; echo; echo "==> $label"; if "$@"; then echo "OK: $label"; else echo "ECHEC: $label" >&2; [[ "$STRICT" -eq 1 ]] && exit 1; fi; }
check_file(){ local file="$1"; if [[ -f "$file" ]]; then echo "OK: $file"; else echo "MANQUANT: $file" >&2; [[ "$STRICT" -eq 1 ]] && exit 1; fi; }
echo "==> Vérification fichiers Lot 3"
check_file resources/views/pages/incidents/index.blade.php
check_file resources/views/pages/incidents/mine.blade.php
check_file resources/views/pages/incidents/en-cours.blade.php
check_file resources/views/pages/incidents/create.blade.php
check_file resources/views/pages/incidents/edit.blade.php
check_file resources/views/pages/incidents/show.blade.php
check_file resources/css/pages/incidents-create.css
check_file resources/js/pages/incidents-show.js
if grep -q "view('incidents\." app/Http/Controllers/IncidentController.php; then echo "AVERTISSEMENT: IncidentController contient encore une vue legacy incidents.*" >&2; grep -n "view('incidents\." app/Http/Controllers/IncidentController.php || true; [[ "$STRICT" -eq 1 ]] && exit 1; else echo "OK: IncidentController pointe vers pages.incidents.*"; fi
run_step "php artisan optimize:clear" php artisan optimize:clear
run_step "php artisan view:clear" php artisan view:clear
run_step "npm run build" npm run build
run_step "php artisan test" php artisan test
echo; echo "Validation Lot 3 terminée. Relis les éventuels avertissements ci-dessus."

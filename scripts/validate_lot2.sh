#!/usr/bin/env bash
set -uo pipefail

PROJECT_ROOT="$(pwd)"
STRICT=0

for arg in "$@"; do
    case "$arg" in
        --strict) STRICT=1 ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
    esac
done

PROJECT_ROOT="$(cd "$PROJECT_ROOT" && pwd)"
cd "$PROJECT_ROOT" || exit 1

if [[ ! -f artisan ]]; then
    echo "Erreur: artisan introuvable. Place-toi à la racine Laravel." >&2
    exit 1
fi

run_step() {
    local label="$1"
    shift
    echo
    echo "==> $label"
    "$@"
    local code=$?
    if [[ $code -ne 0 ]]; then
        echo "ECHEC: $label"
        if [[ $STRICT -eq 1 ]]; then
            exit $code
        fi
    else
        echo "OK: $label"
    fi
}

run_step "php artisan optimize:clear" php artisan optimize:clear
run_step "php artisan view:clear" php artisan view:clear
run_step "npm run build" npm run build
run_step "php artisan test" php artisan test

echo
echo "Validation Lot 2 terminée. Relis les éventuels avertissements ci-dessus."

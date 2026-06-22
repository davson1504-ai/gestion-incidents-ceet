#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$PWD"
STRICT=0

for arg in "$@"; do
    case "$arg" in
        --strict) STRICT=1 ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) if [[ -d "$arg" ]]; then PROJECT_ROOT="$arg"; fi ;;
    esac
done

PROJECT_ROOT="$(cd "$PROJECT_ROOT" && pwd)"

if [[ ! -f "$PROJECT_ROOT/artisan" ]]; then
    echo "Erreur: $PROJECT_ROOT ne semble pas être la racine du projet Laravel."
    exit 1
fi

cd "$PROJECT_ROOT"

run_or_warn() {
    local label="$1"
    shift
    echo
    echo "==> $label"
    if "$@"; then
        echo "OK: $label"
    else
        local code=$?
        echo "ECHEC: $label"
        if [[ "$STRICT" -eq 1 ]]; then
            exit "$code"
        fi
        return 0
    fi
}

if [[ ! -f vendor/autoload.php ]]; then
    echo "Attention: vendor/autoload.php absent. Lance d'abord: composer install"
    if [[ "$STRICT" -eq 1 ]]; then exit 1; fi
else
    run_or_warn "php artisan optimize:clear" php artisan optimize:clear
    run_or_warn "php artisan view:clear" php artisan view:clear
fi

if [[ ! -d node_modules ]]; then
    echo
    echo "Attention: node_modules absent. Lance d'abord: npm install"
    if [[ "$STRICT" -eq 1 ]]; then exit 1; fi
else
    run_or_warn "npm run build" npm run build
fi

if [[ -f vendor/autoload.php ]]; then
    run_or_warn "php artisan test" php artisan test
fi

echo
echo "Validation Lot 1 terminée. Relis les éventuels avertissements ci-dessus."

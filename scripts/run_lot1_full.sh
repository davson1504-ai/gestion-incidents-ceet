#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$PWD"
STRICT=""

for arg in "$@"; do
    case "$arg" in
        --strict) STRICT="--strict" ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) if [[ -d "$arg" ]]; then PROJECT_ROOT="$arg"; fi ;;
    esac
done

bash "$SCRIPT_DIR/apply_lot1.sh" --yes --project="$PROJECT_ROOT"
bash "$SCRIPT_DIR/validate_lot1.sh" --project="$PROJECT_ROOT" $STRICT

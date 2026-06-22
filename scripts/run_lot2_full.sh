#!/usr/bin/env bash
set -uo pipefail

PROJECT_ROOT="$(pwd)"
STRICT_FLAG=""

for arg in "$@"; do
    case "$arg" in
        --strict) STRICT_FLAG="--strict" ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
    esac
done

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

bash "$SCRIPT_DIR/apply_lot2.sh" --project="$PROJECT_ROOT" --yes || exit $?
bash "$SCRIPT_DIR/validate_lot2.sh" --project="$PROJECT_ROOT" $STRICT_FLAG

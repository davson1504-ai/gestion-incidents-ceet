#!/usr/bin/env bash
set -euo pipefail
STRICT=""
for arg in "$@"; do case "$arg" in --strict) STRICT="--strict" ;; *) echo "Argument inconnu: $arg"; exit 1 ;; esac; done
bash scripts/apply_lot3.sh --dry-run
bash scripts/apply_lot3.sh --yes
bash scripts/validate_lot3.sh $STRICT

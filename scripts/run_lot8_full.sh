#!/usr/bin/env bash
set -euo pipefail
bash scripts/apply_lot8.sh --dry-run
bash scripts/apply_lot8.sh --yes
bash scripts/validate_lot8.sh

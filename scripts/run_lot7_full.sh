#!/usr/bin/env bash
set -euo pipefail
bash scripts/apply_lot7.sh --dry-run
bash scripts/apply_lot7.sh --yes
bash scripts/validate_lot7.sh

#!/usr/bin/env bash
set -euo pipefail
bash scripts/apply_lot6.sh --yes
bash scripts/validate_lot6.sh

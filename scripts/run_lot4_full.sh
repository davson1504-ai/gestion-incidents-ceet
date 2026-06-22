#!/usr/bin/env bash
set -euo pipefail
bash scripts/apply_lot4.sh --yes
bash scripts/validate_lot4.sh

#!/usr/bin/env bash
set -euo pipefail
bash scripts/apply_lot5.sh --yes
bash scripts/validate_lot5.sh

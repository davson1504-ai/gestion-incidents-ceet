#!/usr/bin/env bash
set -euo pipefail

PROJECT="$(pwd)"
YES=0
BACKUP=""

for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --backup=*) BACKUP="${arg#--backup=}" ;;
    --project=*) PROJECT="${arg#--project=}" ;;
    *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
  esac
done

cd "$PROJECT"

if [ ! -f artisan ]; then
  echo "ECHEC: ce script doit être lancé à la racine du projet Laravel." >&2
  exit 1
fi

if [ "$YES" -ne 1 ]; then
  echo "Utilisation: bash scripts/rollback_lot8.sh --yes [--backup=backups/lot8-before-YYYYMMDD-HHMMSS.tar.gz]" >&2
  exit 2
fi

if [ -z "$BACKUP" ]; then
  BACKUP="$(ls -1t backups/lot8-before-*.tar.gz 2>/dev/null | head -n 1 || true)"
fi

if [ -z "$BACKUP" ] || [ ! -f "$BACKUP" ]; then
  echo "ECHEC: backup Lot 8 introuvable." >&2
  exit 1
fi

echo "Restauration depuis : $BACKUP"
tar -xzf "$BACKUP" -C .

echo "Rollback Lot 8 terminé. Relance: php artisan test et npm run build."

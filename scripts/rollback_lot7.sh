#!/usr/bin/env bash
set -euo pipefail
PROJECT="${PWD}"
YES=0
BACKUP=""
for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --backup=*) BACKUP="${arg#*=}" ;;
    --project=*) PROJECT="${arg#*=}" ;;
  esac
done
cd "$PROJECT"
if [ "$YES" -ne 1 ]; then echo "Ajoute --yes pour rollback."; exit 1; fi
if [ -z "$BACKUP" ]; then BACKUP="$(ls -1t backups/lot7-before-*.tar.gz 2>/dev/null | head -n 1 || true)"; fi
if [ -z "$BACKUP" ] || [ ! -f "$BACKUP" ]; then echo "ECHEC: backup Lot 7 introuvable." >&2; exit 1; fi
MANIFEST="${BACKUP%.tar.gz}.manifest.txt"
echo "Rollback depuis : $BACKUP"
if [ -f "$MANIFEST" ]; then
  while IFS= read -r file; do
    [ -n "$file" ] && rm -f "$file"
  done < "$MANIFEST"
fi
tar -xzf "$BACKUP"
echo "Rollback Lot 7 terminé."

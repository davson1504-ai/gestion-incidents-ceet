#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(pwd)"
YES=0
for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --project=*) PROJECT_DIR="${arg#--project=}" ;;
    *) echo "Argument inconnu: $arg"; exit 2 ;;
  esac
done

cd "$PROJECT_DIR"
manifest="$(ls -1t backups/lot4-before-*.manifest.txt 2>/dev/null | head -1 || true)"
backup="${manifest%.manifest.txt}.tar.gz"

if [[ -z "$manifest" || ! -f "$manifest" ]]; then
  echo "ECHEC: aucun manifest Lot 4 trouvé."
  exit 1
fi

if [[ "$YES" -ne 1 ]]; then
  echo "Rollback disponible depuis: $backup"
  echo "Relance avec --yes pour restaurer."
  exit 0
fi

echo "Rollback depuis : $backup"
while read -r status file; do
  case "$status" in
    EXISTING)
      if tar -tzf "$backup" "$file" >/dev/null 2>&1; then
        tar -xzf "$backup" "$file"
        echo "RESTAURE $file"
      fi
      ;;
    CREATED)
      rm -f "$file"
      echo "SUPPRIME $file"
      ;;
  esac
done < "$manifest"

echo "Rollback Lot 4 terminé."

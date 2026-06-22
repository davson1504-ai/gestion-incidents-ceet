#!/usr/bin/env bash
set -euo pipefail
PROJECT="${PWD}"
YES=0
for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --project=*) PROJECT="${arg#*=}" ;;
  esac
done

if [ "$YES" -ne 1 ]; then
  echo "Ajoute --yes pour confirmer le rollback Lot 6."
  exit 1
fi

cd "$PROJECT"
LATEST="$(ls -1t backups/lot6-before-*.tar.gz 2>/dev/null | head -n 1 || true)"
if [ -z "$LATEST" ]; then
  echo "ECHEC: aucun backup Lot 6 trouvé."
  exit 1
fi
MANIFEST="${LATEST%.tar.gz}.manifest.txt"
if [ ! -f "$MANIFEST" ]; then
  echo "ECHEC: manifest introuvable: $MANIFEST"
  exit 1
fi

echo "Rollback depuis : $LATEST"

tar -xzf "$LATEST"
while IFS= read -r file; do
  [ -z "$file" ] && continue
  if tar -tzf "$LATEST" | grep -qx "$file"; then
    echo "RESTAURE $file"
  else
    rm -f "$file"
    echo "SUPPRIME $file"
  fi
done < "$MANIFEST"

echo "Rollback Lot 6 terminé."

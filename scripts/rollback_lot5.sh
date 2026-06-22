#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(pwd)"
CONFIRM=0
for arg in "$@"; do
  case "$arg" in
    --yes) CONFIRM=1 ;;
    --project=*) PROJECT_DIR="${arg#--project=}" ;;
  esac
done

cd "$PROJECT_DIR"

if [[ "$CONFIRM" -ne 1 ]]; then
  echo "Ajoute --yes pour confirmer le rollback Lot 5."
  exit 1
fi

LATEST_MANIFEST="$(ls -1t backups/lot5-before-*.manifest.txt 2>/dev/null | head -1 || true)"
if [[ -z "$LATEST_MANIFEST" ]]; then
  echo "ERREUR: aucun manifest de backup Lot 5 trouvé." >&2
  exit 1
fi

BACKUP="${LATEST_MANIFEST%.manifest.txt}.tar.gz"
if [[ ! -f "$BACKUP" ]]; then
  echo "ERREUR: archive backup introuvable: $BACKUP" >&2
  exit 1
fi

echo "Rollback depuis : $BACKUP"

# Supprime d'abord les fichiers créés par le Lot 5 qui n'étaient pas dans le manifest.
while IFS= read -r file; do
  [[ -z "$file" ]] && continue
  if ! grep -qxF "$file" "$LATEST_MANIFEST"; then
    rm -f "$file"
    echo "SUPPRIME $file"
  fi
done < <(cd lot5_files && find . -type f | sed 's#^./##' | sort)

# Restaure les fichiers modifiés.
if [[ -s "$LATEST_MANIFEST" ]]; then
  tar -xzf "$BACKUP"
  while IFS= read -r file; do
    [[ -z "$file" ]] && continue
    echo "RESTAURE $file"
  done < "$LATEST_MANIFEST"
fi

echo "Rollback Lot 5 terminé."

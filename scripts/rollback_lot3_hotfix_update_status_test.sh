#!/usr/bin/env bash
set -euo pipefail

PROJECT="${PROJECT:-$(pwd)}"
YES=0
for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --project=*) PROJECT="${arg#--project=}" ;;
  esac
done

cd "$PROJECT"

if [[ "$YES" != "1" ]]; then
  echo "Ajoute --yes pour restaurer le dernier backup de ce hotfix."
  exit 1
fi

BACKUP="$(ls -t backups/lot3-hotfix-update-status-test-before-*.tar.gz 2>/dev/null | head -n 1 || true)"
if [[ -z "$BACKUP" ]]; then
  echo "Aucun backup trouvé."
  exit 1
fi

tar -xzf "$BACKUP"
echo "Rollback terminé depuis : $BACKUP"

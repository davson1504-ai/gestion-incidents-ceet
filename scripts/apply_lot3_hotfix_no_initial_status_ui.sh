#!/usr/bin/env bash
set -euo pipefail

PROJECT="$(pwd)"
if [[ "${1:-}" == --project=* ]]; then
  PROJECT="${1#--project=}"
fi

SOURCE="$PROJECT/hotfix_files"
TARGET_FILE="resources/views/components/incidents/form.blade.php"
BACKUP_DIR="$PROJECT/backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="$BACKUP_DIR/lot3-hotfix-no-initial-status-ui-before-$STAMP.tar.gz"

if [[ ! -f "$PROJECT/artisan" ]]; then
  echo "ERREUR: lance ce script à la racine du projet Laravel, ou utilise --project=/chemin/projet" >&2
  exit 1
fi

if [[ ! -f "$SOURCE/$TARGET_FILE" ]]; then
  echo "ERREUR: fichier source introuvable: $SOURCE/$TARGET_FILE" >&2
  exit 1
fi

if [[ "${1:-}" == "--dry-run" ]]; then
  echo "Projet cible : $PROJECT"
  echo "MODIFIER  $TARGET_FILE"
  echo "Mode dry-run: aucune modification ne sera faite."
  exit 0
fi

if [[ "${1:-}" != "--yes" && "${2:-}" != "--yes" ]]; then
  echo "Utilisation: bash scripts/apply_lot3_hotfix_no_initial_status_ui.sh --dry-run|--yes"
  exit 1
fi

mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP" -C "$PROJECT" "$TARGET_FILE" 2>/dev/null || true
install -D -m 0644 "$SOURCE/$TARGET_FILE" "$PROJECT/$TARGET_FILE"

echo "Backup créé : $BACKUP"
echo "OK $TARGET_FILE"
echo "Hotfix appliqué: aucun bloc 'Statut initial / OUVERT' dans le formulaire de création."

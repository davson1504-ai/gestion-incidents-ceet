#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(pwd)"
YES=0
DRY_RUN=0

for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --dry-run) DRY_RUN=1 ;;
    --project=*) PROJECT_DIR="${arg#--project=}" ;;
    *) echo "Argument inconnu: $arg"; exit 2 ;;
  esac
done

if [[ ! -f "$PROJECT_DIR/artisan" || ! -f "$PROJECT_DIR/composer.json" ]]; then
  echo "ECHEC: lance ce script depuis la racine Laravel ou utilise --project=/chemin/projet" >&2
  exit 1
fi

SOURCE_DIR="$PROJECT_DIR/lot4_files"
if [[ ! -d "$SOURCE_DIR" ]]; then
  echo "ECHEC: dossier source introuvable: $SOURCE_DIR" >&2
  exit 1
fi

cd "$PROJECT_DIR"

mapfile -t FILES < <(cd "$SOURCE_DIR" && find . -type f | sed 's#^./##' | sort)

cat <<EOF
Projet cible : $PROJECT_DIR
Source Lot 4 : $SOURCE_DIR
Fichiers à appliquer : ${#FILES[@]}
EOF

for file in "${FILES[@]}"; do
  if [[ -e "$file" ]]; then
    echo "MODIFIER  $file"
  else
    echo "CREER     $file"
  fi

done

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "Mode dry-run: aucune modification ne sera faite."
  exit 0
fi

if [[ "$YES" -ne 1 ]]; then
  echo "Ajoute --yes pour appliquer le Lot 4."
  exit 0
fi

BACKUP_DIR="$PROJECT_DIR/backups"
mkdir -p "$BACKUP_DIR"
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_FILE="$BACKUP_DIR/lot4-before-$STAMP.tar.gz"
MANIFEST_FILE="$BACKUP_DIR/lot4-before-$STAMP.manifest.txt"

: > "$MANIFEST_FILE"
for file in "${FILES[@]}"; do
  if [[ -e "$file" ]]; then
    echo "EXISTING $file" >> "$MANIFEST_FILE"
  else
    echo "CREATED $file" >> "$MANIFEST_FILE"
  fi

done

tar -czf "$BACKUP_FILE" -T <(printf '%s\n' "${FILES[@]}" | while read -r file; do [[ -e "$file" ]] && echo "$file"; done) 2>/dev/null || true

echo "Backup créé : $BACKUP_FILE"

for file in "${FILES[@]}"; do
  target_dir="$(dirname "$file")"
  mkdir -p "$target_dir"
  if [[ -e "$file" && ! -w "$file" ]]; then
    echo "ECHEC: fichier non modifiable: $file" >&2
    echo "Corrige avec: sudo chown \$USER:\$USER '$file' && chmod u+w '$file'" >&2
    exit 1
  fi
  cp "$SOURCE_DIR/$file" "$file"
  echo "OK $file"

done

cat <<EOF

Lot 4 appliqué.
Backup manifest : $MANIFEST_FILE
Prochaine étape : bash scripts/validate_lot4.sh --project=$PROJECT_DIR
EOF

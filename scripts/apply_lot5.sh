#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(pwd)"
SOURCE_DIR="$PROJECT_DIR/lot5_files"
DRY_RUN=1

for arg in "$@"; do
  case "$arg" in
    --yes) DRY_RUN=0 ;;
    --dry-run) DRY_RUN=1 ;;
    --project=*) PROJECT_DIR="${arg#--project=}"; SOURCE_DIR="$PROJECT_DIR/lot5_files" ;;
  esac
done

cd "$PROJECT_DIR"

if [[ ! -f artisan || ! -f composer.json ]]; then
  echo "ERREUR: lance ce script à la racine du projet Laravel." >&2
  exit 1
fi

if [[ ! -d "$SOURCE_DIR" ]]; then
  echo "ERREUR: dossier source introuvable: $SOURCE_DIR" >&2
  exit 1
fi

mapfile -t FILES < <(cd "$SOURCE_DIR" && find . -type f | sed 's#^./##' | sort)

echo "Projet cible : $PROJECT_DIR"
echo "Source Lot 5 : $SOURCE_DIR"
echo "Fichiers à appliquer : ${#FILES[@]}"

for file in "${FILES[@]}"; do
  if [[ -f "$PROJECT_DIR/$file" ]]; then
    echo "MODIFIER  $file"
  else
    echo "CREER     $file"
  fi
done

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "Mode dry-run: aucune modification ne sera faite."
  exit 0
fi

mkdir -p backups
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="backups/lot5-before-$STAMP.tar.gz"
MANIFEST="backups/lot5-before-$STAMP.manifest.txt"

{
  for file in "${FILES[@]}"; do
    if [[ -e "$PROJECT_DIR/$file" ]]; then
      echo "$file"
    fi
  done
} > "$MANIFEST"

if [[ -s "$MANIFEST" ]]; then
  tar -czf "$BACKUP" -T "$MANIFEST"
else
  tar -czf "$BACKUP" --files-from /dev/null
fi

echo "Backup créé : $PROJECT_DIR/$BACKUP"

for file in "${FILES[@]}"; do
  mkdir -p "$(dirname "$PROJECT_DIR/$file")"
  cp "$SOURCE_DIR/$file" "$PROJECT_DIR/$file"
  echo "OK $file"
done

echo
echo "Lot 5 appliqué."
echo "Backup manifest : $PROJECT_DIR/$MANIFEST"
echo "Prochaine étape : bash scripts/validate_lot5.sh --project=$PROJECT_DIR"

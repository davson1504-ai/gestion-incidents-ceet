#!/usr/bin/env bash
set -euo pipefail
PROJECT="${PWD}"
DRY_RUN=0
YES=0

for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --yes) YES=1 ;;
    --project=*) PROJECT="${arg#*=}" ;;
  esac
done

if [ ! -f "$PROJECT/artisan" ]; then
  echo "ECHEC: lance ce script à la racine du projet Laravel ou utilise --project=/chemin/projet" >&2
  exit 1
fi

SRC="$PROJECT/lot6_files"
if [ ! -d "$SRC" ]; then
  echo "ECHEC: dossier source Lot 6 introuvable: $SRC" >&2
  exit 1
fi

cd "$PROJECT"
mapfile -t FILES < <(find lot6_files -type f | sed 's#^lot6_files/##' | sort)

echo "Projet cible : $PROJECT"
echo "Source Lot 6 : $SRC"
echo "Fichiers à appliquer : ${#FILES[@]}"

for file in "${FILES[@]}"; do
  if [ -f "$file" ]; then
    echo "MODIFIER  $file"
  else
    echo "CREER     $file"
  fi
done

if [ "$DRY_RUN" -eq 1 ]; then
  echo "Mode dry-run: aucune modification ne sera faite."
  exit 0
fi

if [ "$YES" -ne 1 ]; then
  echo "Ajoute --yes pour appliquer le Lot 6."
  exit 1
fi

mkdir -p backups
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="backups/lot6-before-$STAMP.tar.gz"
MANIFEST="backups/lot6-before-$STAMP.manifest.txt"

printf "%s\n" "${FILES[@]}" > "$MANIFEST"
# Sauvegarde uniquement les fichiers existants.
EXISTING=()
for file in "${FILES[@]}"; do
  [ -e "$file" ] && EXISTING+=("$file")
done
if [ "${#EXISTING[@]}" -gt 0 ]; then
  tar -czf "$BACKUP" "${EXISTING[@]}"
else
  tar -czf "$BACKUP" --files-from /dev/null
fi

echo "Backup créé : $PROJECT/$BACKUP"

for file in "${FILES[@]}"; do
  mkdir -p "$(dirname "$file")"
  cp "lot6_files/$file" "$file"
  echo "OK $file"
done

echo
echo "Lot 6 appliqué."
echo "Backup manifest : $PROJECT/$MANIFEST"
echo "Prochaine étape : bash scripts/validate_lot6.sh --project=$PROJECT"

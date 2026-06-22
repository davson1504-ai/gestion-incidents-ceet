#!/usr/bin/env bash
set -euo pipefail
PROJECT="${PWD}"
for arg in "$@"; do
  case "$arg" in
    --project=*) PROJECT="${arg#*=}" ;;
    --dry-run) DRY_RUN=1 ;;
    --yes) YES=1 ;;
  esac
done
cd "$PROJECT"
FILE="scripts/validate_lot6.sh"
echo "Projet cible : $PROJECT"
echo "Fichier à corriger : $FILE"
if [ ! -f "$FILE" ]; then
  echo "ECHEC: fichier introuvable $FILE" >&2
  exit 1
fi
if grep -q "<!DOCTYPE html>|<html|<head|<body|ceet-profile-sidebar|ceet-profile-topbar" "$FILE"; then
  echo "MODIFIER  $FILE"
else
  echo "OK: le validateur semble déjà corrigé"
fi
if [ "${DRY_RUN:-0}" = "1" ]; then
  echo "Mode dry-run: aucune modification ne sera faite."
  echo "Objectif: éviter que <header> soit détecté à tort comme <head>."
  exit 0
fi
if [ "${YES:-0}" != "1" ]; then
  echo "Ajoute --yes pour appliquer." >&2
  exit 1
fi
mkdir -p backups
STAMP=$(date +%Y%m%d-%H%M%S)
tar -czf "backups/lot6-hotfix-validator-header-before-$STAMP.tar.gz" "$FILE"
echo "Backup créé : backups/lot6-hotfix-validator-header-before-$STAMP.tar.gz"
python3 - <<'PY'
from pathlib import Path
p = Path('scripts/validate_lot6.sh')
s = p.read_text()
old = 'if grep -REiq "<!DOCTYPE html>|<html|<head|<body|ceet-profile-sidebar|ceet-profile-topbar" resources/views/pages/profile/edit.blade.php; then'
new = 'if grep -REiq "<!DOCTYPE html>|<html([[:space:]>])|<head([[:space:]>])|<body([[:space:]>])|ceet-profile-sidebar|ceet-profile-topbar" resources/views/pages/profile/edit.blade.php; then'
if old not in s:
    print('Aucune occurrence exacte trouvée; tentative de correction par remplacement large.')
    s = s.replace('<!DOCTYPE html>|<html|<head|<body|ceet-profile-sidebar|ceet-profile-topbar', '<!DOCTYPE html>|<html([[:space:]>])|<head([[:space:]>])|<body([[:space:]>])|ceet-profile-sidebar|ceet-profile-topbar')
else:
    s = s.replace(old, new)
p.write_text(s)
PY
echo "OK $FILE"
echo "Hotfix appliqué: <header> ne sera plus pris pour <head>."

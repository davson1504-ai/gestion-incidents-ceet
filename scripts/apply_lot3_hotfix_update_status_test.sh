#!/usr/bin/env bash
set -euo pipefail

PROJECT="${PROJECT:-$(pwd)}"
if [[ "${1:-}" == "--project" && -n "${2:-}" ]]; then
  PROJECT="$2"
fi
if [[ "${1:-}" == --project=* ]]; then
  PROJECT="${1#--project=}"
fi

DRY_RUN=0
YES=0
for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --yes) YES=1 ;;
  esac
done

cd "$PROJECT"

TARGET="tests/Feature/Incidents/IncidentWorkflowTest.php"

if [[ ! -f artisan || ! -f "$TARGET" ]]; then
  echo "Erreur: lance ce script à la racine du projet Laravel."
  echo "Fichier attendu: $TARGET"
  exit 1
fi

echo "Projet cible : $PROJECT"
echo "Fichier à corriger : $TARGET"

if [[ "$DRY_RUN" == "1" ]]; then
  echo "Mode dry-run: aucune modification ne sera faite."
  echo "Objectif: remplacer les assertions qui attendent encore le bloc 'Statut initial' par des assertions d'absence."
  grep -n "Statut initial\|AFFECTE si un opérateur est affecté\|name=\"status_id\"" "$TARGET" || true
  exit 0
fi

if [[ "$YES" != "1" ]]; then
  echo "Ajoute --yes pour appliquer."
  exit 1
fi

mkdir -p backups
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="backups/lot3-hotfix-update-status-test-before-${STAMP}.tar.gz"
tar -czf "$BACKUP" "$TARGET"
echo "Backup créé : $BACKUP"

python3 - <<'PY'
from pathlib import Path
path = Path("tests/Feature/Incidents/IncidentWorkflowTest.php")
text = path.read_text(encoding="utf-8")

old = """        $response->assertSee('Statut initial');
        $response->assertSee('OUVERT');
        $response->assertSee('AFFECTE si un opérateur est affecté');
        $response->assertDontSee('name=\"status_id\"', false);
"""

new = """        $response->assertDontSee('Statut initial');
        $response->assertDontSee('AFFECTE si un opérateur est affecté');
        $response->assertDontSee('name=\"status_id\"', false);
"""

if old not in text:
    raise SystemExit("Bloc attendu introuvable. Le test a peut-être déjà été modifié.")

path.write_text(text.replace(old, new), encoding="utf-8")
PY

echo "OK $TARGET"
echo "Test mis à jour: la page création ne doit plus afficher le bloc 'Statut initial'."

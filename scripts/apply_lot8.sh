#!/usr/bin/env bash
set -euo pipefail

PROJECT="$(pwd)"
YES=0
DRY_RUN=0

for arg in "$@"; do
  case "$arg" in
    --yes) YES=1 ;;
    --dry-run) DRY_RUN=1 ;;
    --project=*) PROJECT="${arg#--project=}" ;;
    *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
  esac
done

cd "$PROJECT"

if [ ! -f artisan ]; then
  echo "ECHEC: ce script doit être lancé à la racine du projet Laravel." >&2
  exit 1
fi

if [ "$YES" -ne 1 ] && [ "$DRY_RUN" -ne 1 ]; then
  echo "Utilisation: bash scripts/apply_lot8.sh --dry-run puis bash scripts/apply_lot8.sh --yes" >&2
  exit 2
fi

STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="backups"
BACKUP_FILE="$BACKUP_DIR/lot8-before-$STAMP.tar.gz"
MANIFEST_FILE="$BACKUP_DIR/lot8-before-$STAMP.manifest.txt"
mkdir -p "$BACKUP_DIR"

REQUIRED_NEW=(
  "resources/views/pages/admin/dashboard.blade.php"
  "resources/views/pages/supervisor/dashboard.blade.php"
  "resources/views/pages/operator/dashboard.blade.php"
  "resources/views/pages/incidents/index.blade.php"
  "resources/views/pages/incidents/create.blade.php"
  "resources/views/pages/incidents/edit.blade.php"
  "resources/views/pages/incidents/show.blade.php"
  "resources/views/pages/incidents/mine.blade.php"
  "resources/views/pages/incidents/en-cours.blade.php"
  "resources/views/pages/reports/index.blade.php"
  "resources/views/pages/users/index.blade.php"
  "resources/views/pages/profile/edit.blade.php"
  "resources/views/pages/catalogues/index.blade.php"
  "resources/views/pages/historique/index.blade.php"
  "resources/views/pages/system/status.blade.php"
  "resources/views/incidents/vue-console.blade.php"
  "resources/views/exports/incidents-pdf.blade.php"
  "vite.config.js"
)

for path in "${REQUIRED_NEW[@]}"; do
  if [ ! -e "$path" ]; then
    echo "ECHEC: prérequis manquant avant nettoyage: $path" >&2
    echo "Arrêt pour éviter de supprimer des vues encore nécessaires." >&2
    exit 1
  fi
done

LEGACY_PATHS=(
  "resources/views/dashboard.blade.php"
  "resources/views/dashboard-supervisor.blade.php"
  "resources/views/dashboard-operator.blade.php"
  "resources/views/incidents/_form.blade.php"
  "resources/views/incidents/create.blade.php"
  "resources/views/incidents/edit.blade.php"
  "resources/views/incidents/en-cours.blade.php"
  "resources/views/incidents/index.blade.php"
  "resources/views/incidents/mine.blade.php"
  "resources/views/incidents/show.blade.php"
  "resources/views/reports"
  "resources/views/users"
  "resources/views/profile"
  "resources/views/catalogues"
  "resources/views/historique"
  "resources/views/system"
  "resources/css/pages/dashboard.css"
  "resources/js/pages/dashboard.js"
  "resources/css/pages/incidents.css"
  "resources/js/pages/incidents.js"
  "resources/js/incident-form.js"
  "resources/css/pages/supervisor-dashboard.css"
  "resources/js/pages/supervisor-dashboard.js"
  "resources/css/pages/operator-dashboard.css"
  "resources/js/pages/operator-dashboard.js"
)

STAGING_PATHS=(
  "files"
  "hotfix_files"
  "lot2_files"
  "lot3_files"
  "lot4_files"
  "lot5_files"
  "lot6_files"
  "lot7_files"
  "ceet_lot1_scripts_rapides"
  "ceet_lot1_structure_commune_txt"
  "ceet_lot2_scripts_rapides"
  "ceet_lot2_scripts_rapides_nested"
  "ceet_lot3_scripts_rapides"
  "ceet_lot3_hotfix_no_initial_status_ui"
  "ceet_lot3_hotfix_update_status_test"
  "ceet_lot4_scripts_rapides"
  "ceet_lot5_scripts_rapides"
  "ceet_lot6_scripts_rapides"
  "ceet_lot6_hotfix_validator_header"
  "ceet_lot7_scripts_rapides"
  "ceet_lot8_scripts_rapides"
)

PATCHED_FILES=(
  "vite.config.js"
  "docs/AUDIT_FINAL_LOT8.md"
)

BACKUP_PATHS=()
for path in "${LEGACY_PATHS[@]}" "${STAGING_PATHS[@]}" "${PATCHED_FILES[@]}"; do
  if [ -e "$path" ]; then
    BACKUP_PATHS+=("$path")
  fi
done

printf '%s\n' "${BACKUP_PATHS[@]}" > "$MANIFEST_FILE"

cat <<EOF
Projet cible : $PROJECT
Fichiers/dossiers legacy détectés : ${#BACKUP_PATHS[@]}
EOF

for path in "${LEGACY_PATHS[@]}"; do
  if [ -e "$path" ]; then
    echo "SUPPRIMER  $path"
  fi
done
for path in "${STAGING_PATHS[@]}"; do
  if [ -e "$path" ]; then
    echo "NETTOYER   $path"
  fi
done

echo "PATCHER    vite.config.js"
echo "CREER      docs/AUDIT_FINAL_LOT8.md"

if [ "$DRY_RUN" -eq 1 ]; then
  echo "Mode dry-run: aucune modification ne sera faite."
  exit 0
fi

if [ "${#BACKUP_PATHS[@]}" -gt 0 ]; then
  tar -czf "$BACKUP_FILE" "${BACKUP_PATHS[@]}"
else
  tar -czf "$BACKUP_FILE" --files-from /dev/null
fi

echo "Backup créé : $BACKUP_FILE"
echo "Backup manifest : $MANIFEST_FILE"

for path in "${LEGACY_PATHS[@]}"; do
  if [ -e "$path" ]; then
    rm -rf "$path"
    echo "OK suppression $path"
  fi
done

# Garder resources/views/incidents/vue-console.blade.php et resources/views/exports/incidents-pdf.blade.php.
# Les autres anciennes vues incidents ont été supprimées individuellement.
find resources/views/incidents -type d -empty -delete 2>/dev/null || true

for path in "${STAGING_PATHS[@]}"; do
  if [ -e "$path" ]; then
    rm -rf "$path"
    echo "OK nettoyage $path"
  fi
done

python3 - <<'PY'
from pathlib import Path

path = Path('vite.config.js')
text = path.read_text(encoding='utf-8')
remove_tokens = [
    "'resources/css/pages/supervisor-dashboard.css',",
    "'resources/js/pages/supervisor-dashboard.js',",
    "'resources/css/pages/operator-dashboard.css',",
    "'resources/js/pages/operator-dashboard.js',",
    "'resources/css/pages/dashboard.css',",
    "'resources/js/pages/dashboard.js',",
    "'resources/css/pages/incidents.css',",
    "'resources/js/pages/incidents.js',",
]
lines = text.splitlines()
filtered = []
for line in lines:
    if any(token in line for token in remove_tokens):
        continue
    filtered.append(line)
text = "\n".join(filtered) + "\n"
text = text.replace(
    "// Anciennes entrées conservées pendant la migration progressive.\n                'resources/css/pages/admin-dashboard.css',\n                'resources/js/pages/admin-dashboard.js',\n",
    "// Entrées legacy conservées uniquement pour resources/views/incidents/vue-console.blade.php.\n                'resources/css/pages/admin-dashboard.css',\n                'resources/js/pages/admin-dashboard.js',\n",
)
path.write_text(text, encoding='utf-8')
PY

echo "OK vite.config.js nettoyé"

mkdir -p docs
cat > docs/AUDIT_FINAL_LOT8.md <<'EOF'
# Audit final Lot 8 — Migration front CEET

## Résultat attendu

Les lots 1 à 7 ont migré les écrans principaux vers une structure commune :

- layout applicatif unique `resources/views/layouts/app.blade.php` ;
- sidebar unique `resources/views/components/app-sidebar.blade.php` ;
- topbar unique `resources/views/components/app-topbar.blade.php` ;
- pages métier sous `resources/views/pages/*` ;
- CSS par layout/composant/page ;
- JavaScript noyau et JavaScript par page ;
- validations Laravel conservées.

## Nettoyage Lot 8

Le Lot 8 supprime les anciennes vues remplacées par les pages `resources/views/pages/*` :

- anciens dashboards racine ;
- anciennes vues incidents, sauf `resources/views/incidents/vue-console.blade.php` ;
- anciennes vues rapports ;
- anciennes vues utilisateurs ;
- ancienne vue profil ;
- anciennes vues catalogues ;
- ancienne vue historique ;
- ancienne vue système.

## Fichiers volontairement conservés

- `resources/views/incidents/vue-console.blade.php` : encore utilisée par `VueConsoleController`.
- `resources/views/exports/incidents-pdf.blade.php` : encore utilisée par l’export PDF incidents API.
- `resources/css/pages/admin-dashboard.css` et `resources/js/pages/admin-dashboard.js` : encore utilisés par la vue console legacy.
- `resources/views/partials/ceet-role-nav.blade.php` : encore utilisé par la vue console legacy.

## Validation attendue

Après application :

```bash
php artisan optimize:clear
php artisan view:clear
npm run build
php artisan test
```

Le résultat attendu est : `Tests: 99 passed`.
EOF

echo "OK docs/AUDIT_FINAL_LOT8.md"
echo "Lot 8 appliqué. Prochaine étape : bash scripts/validate_lot8.sh"

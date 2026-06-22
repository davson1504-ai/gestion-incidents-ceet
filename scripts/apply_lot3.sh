#!/usr/bin/env bash
set -euo pipefail

PROJECT="$(pwd)"
DRY_RUN=0
YES=0
for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=1 ;;
        --yes) YES=1 ;;
        --project=*) PROJECT="${arg#--project=}" ;;
        *) echo "Argument inconnu: $arg"; exit 1 ;;
    esac
done
cd "$PROJECT"
if [[ ! -f artisan || ! -d resources || ! -d app ]]; then echo "ERREUR: lance ce script à la racine Laravel." >&2; exit 1; fi
SOURCE="$PROJECT/lot3_files"
if [[ ! -d "$SOURCE" ]]; then echo "ERREUR: dossier source Lot 3 introuvable: $SOURCE" >&2; exit 1; fi
mapfile -t COPY_FILES < <(cd "$SOURCE" && find . -type f | sed 's#^./##' | sort)
PATCH_FILES=("app/Http/Controllers/IncidentController.php" "app/Http/Requests/StoreIncidentRequest.php")
printf 'Projet cible : %s\n' "$PROJECT"
printf 'Source Lot 3 : %s\n' "$SOURCE"
printf 'Fichiers à appliquer : %s\n' "${#COPY_FILES[@]}"
for file in "${COPY_FILES[@]}"; do [[ -f "$file" ]] && printf 'MODIFIER  %s\n' "$file" || printf 'CREER     %s\n' "$file"; done
for file in "${PATCH_FILES[@]}"; do printf 'PATCHER   %s\n' "$file"; done
if [[ "$DRY_RUN" -eq 1 ]]; then echo "Mode dry-run: aucune modification ne sera faite."; exit 0; fi
if [[ "$YES" -ne 1 ]]; then echo "Ajoute --yes pour appliquer réellement le Lot 3." >&2; exit 1; fi
BACKUP_DIR="$PROJECT/backups"; mkdir -p "$BACKUP_DIR"; STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="$BACKUP_DIR/lot3-before-$STAMP.tar.gz"; MANIFEST="$BACKUP_DIR/lot3-before-$STAMP.manifest.txt"
{ for file in "${COPY_FILES[@]}"; do echo "$file"; done; for file in "${PATCH_FILES[@]}"; do echo "$file"; done; } | sort -u > "$MANIFEST"
tar -czf "$BACKUP" -T "$MANIFEST" 2>/dev/null || true
echo "Backup créé : $BACKUP"
for file in "${COPY_FILES[@]}"; do mkdir -p "$(dirname "$file")"; cp "$SOURCE/$file" "$file"; printf 'OK %s\n' "$file"; done
python3 - <<'PYIN'
from pathlib import Path
controller = Path('app/Http/Controllers/IncidentController.php')
text = controller.read_text(encoding='utf-8')
replacements = {
    "return $this->renderIncidentList($request, true, 'incidents.mine');": "return $this->renderIncidentList($request, true, 'pages.incidents.mine');",
    "return view('incidents.en-cours', array_merge([": "return view('pages.incidents.en-cours', array_merge([",
    "return view('incidents.create', $this->incidentCatalogueService->activeFormCatalogues());": "return view('pages.incidents.create', $this->incidentCatalogueService->activeFormCatalogues());",
    "return view('incidents.show', array_merge(": "return view('pages.incidents.show', array_merge(",
    "return view('incidents.edit', array_merge(": "return view('pages.incidents.edit', array_merge(",
    "private function renderIncidentList(Request $request, bool $onlyMine = false, string $view = 'incidents.index'): View": "private function renderIncidentList(Request $request, bool $onlyMine = false, string $view = 'pages.incidents.index'): View",
}
for old, new in replacements.items():
    text = text.replace(old, new)
controller.write_text(text, encoding='utf-8')
store = Path('app/Http/Requests/StoreIncidentRequest.php')
text = store.read_text(encoding='utf-8')
text = text.replace("            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],\n", "")
store.write_text(text, encoding='utf-8')
PYIN
printf 'OK patch IncidentController.php\n'
printf 'OK patch StoreIncidentRequest.php\n'
echo; echo "Lot 3 appliqué."; echo "Backup manifest : $MANIFEST"; echo "Prochaine étape : bash scripts/validate_lot3.sh --project=$PROJECT"

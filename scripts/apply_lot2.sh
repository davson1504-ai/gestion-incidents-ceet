#!/usr/bin/env bash
set -uo pipefail

PROJECT_ROOT="$(pwd)"
DRY_RUN=0
ASSUME_YES=0

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=1 ;;
        --yes|-y) ASSUME_YES=1 ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
    esac
done

PROJECT_ROOT="$(cd "$PROJECT_ROOT" && pwd)"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ ! -f "$PROJECT_ROOT/artisan" || ! -d "$PROJECT_ROOT/resources" ]]; then
    echo "Erreur: $PROJECT_ROOT ne semble pas être la racine d'un projet Laravel." >&2
    exit 1
fi

SOURCE_DIR="$PROJECT_ROOT/lot2_files"
if [[ ! -d "$SOURCE_DIR" ]]; then
    SOURCE_DIR="$(cd "$SCRIPT_DIR/.." && pwd)/lot2_files"
fi

if [[ ! -d "$SOURCE_DIR" ]]; then
    echo "Erreur: dossier source Lot 2 introuvable." >&2
    echo "Attendu: $PROJECT_ROOT/lot2_files ou $(cd "$SCRIPT_DIR/.." && pwd)/lot2_files" >&2
    exit 1
fi

mapfile -t FILES < <(cd "$SOURCE_DIR" && find . -type f ! -name '*:Zone.Identifier' | sed 's#^./##' | sort)

if [[ ${#FILES[@]} -eq 0 ]]; then
    echo "Erreur: aucun fichier à appliquer dans $SOURCE_DIR." >&2
    exit 1
fi

echo "Projet cible : $PROJECT_ROOT"
echo "Source Lot 2 : $SOURCE_DIR"
echo "Fichiers à appliquer : ${#FILES[@]}"

for rel in "${FILES[@]}"; do
    if [[ -e "$PROJECT_ROOT/$rel" ]]; then
        echo "MODIFIER  $rel"
    else
        echo "CREER     $rel"
    fi
done

if [[ $DRY_RUN -eq 1 ]]; then
    echo "Mode dry-run: aucune modification ne sera faite."
    exit 0
fi

if [[ $ASSUME_YES -ne 1 ]]; then
    echo
    read -r -p "Appliquer le Lot 2 ? Tape 'oui' pour confirmer: " confirm
    if [[ "$confirm" != "oui" ]]; then
        echo "Annulé."
        exit 0
    fi
fi

mkdir -p "$PROJECT_ROOT/backups"
TS="$(date +%Y%m%d-%H%M%S)"
BACKUP_ROOT="$PROJECT_ROOT/backups/lot2-before-$TS"
MANIFEST="$PROJECT_ROOT/backups/lot2-before-$TS.manifest.txt"
mkdir -p "$BACKUP_ROOT"
: > "$MANIFEST"

for rel in "${FILES[@]}"; do
    target="$PROJECT_ROOT/$rel"
    if [[ -e "$target" ]]; then
        echo "MODIFIED $rel" >> "$MANIFEST"
        mkdir -p "$BACKUP_ROOT/$(dirname "$rel")"
        cp -a "$target" "$BACKUP_ROOT/$rel"
    else
        echo "CREATED $rel" >> "$MANIFEST"
    fi
done

tar -czf "$PROJECT_ROOT/backups/lot2-before-$TS.tar.gz" -C "$BACKUP_ROOT" .
rm -rf "$BACKUP_ROOT"

echo "Backup créé : $PROJECT_ROOT/backups/lot2-before-$TS.tar.gz"

for rel in "${FILES[@]}"; do
    mkdir -p "$PROJECT_ROOT/$(dirname "$rel")"
    cp -a "$SOURCE_DIR/$rel" "$PROJECT_ROOT/$rel"
    echo "OK $rel"
done

echo
echo "Lot 2 appliqué."
echo "Backup manifest : $MANIFEST"
echo "Prochaine étape : bash scripts/validate_lot2.sh --project=$PROJECT_ROOT"

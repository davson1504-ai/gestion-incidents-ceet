#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUNDLE_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
FILES_DIR="$BUNDLE_ROOT/files"

PROJECT_ROOT="$PWD"
DRY_RUN=0
YES=0

for arg in "$@"; do
    case "$arg" in
        --yes|-y) YES=1 ;;
        --dry-run) DRY_RUN=1 ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *)
            if [[ -d "$arg" ]]; then PROJECT_ROOT="$arg"; fi
            ;;
    esac
done

PROJECT_ROOT="$(cd "$PROJECT_ROOT" && pwd)"

if [[ ! -f "$PROJECT_ROOT/artisan" || ! -d "$PROJECT_ROOT/resources" ]]; then
    echo "Erreur: $PROJECT_ROOT ne semble pas être la racine du projet Laravel."
    echo "Utilisation: bash scripts/apply_lot1.sh --yes --project=/chemin/vers/projet"
    exit 1
fi

if [[ ! -d "$FILES_DIR" ]]; then
    echo "Erreur: dossier fichiers introuvable: $FILES_DIR"
    exit 1
fi

echo "Projet cible : $PROJECT_ROOT"
echo "Source Lot 1 : $FILES_DIR"

mapfile -t FILES < <(cd "$FILES_DIR" && find . -type f | sed 's#^./##' | sort)

if [[ ${#FILES[@]} -eq 0 ]]; then
    echo "Erreur: aucun fichier à appliquer."
    exit 1
fi

echo "Fichiers à appliquer : ${#FILES[@]}"

if [[ "$DRY_RUN" -eq 1 ]]; then
    echo "Mode dry-run: aucune modification ne sera faite."
    for rel in "${FILES[@]}"; do
        if [[ -f "$PROJECT_ROOT/$rel" ]]; then
            echo "MODIFIER  $rel"
        else
            echo "CREER     $rel"
        fi
    done
    exit 0
fi

if [[ "$YES" -ne 1 ]]; then
    echo "Cette opération va créer/remplacer les fichiers du Lot 1."
    read -r -p "Continuer ? [y/N] " answer
    case "$answer" in
        y|Y|yes|YES|oui|OUI) ;;
        *) echo "Annulé."; exit 0 ;;
    esac
fi

mkdir -p "$PROJECT_ROOT/backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="$PROJECT_ROOT/backups/lot1-before-$STAMP.tar.gz"
MANIFEST="$PROJECT_ROOT/backups/lot1-before-$STAMP.manifest.txt"
TMP_LIST="$(mktemp)"
trap 'rm -f "$TMP_LIST"' EXIT

: > "$TMP_LIST"
for rel in "${FILES[@]}"; do
    if [[ -e "$PROJECT_ROOT/$rel" ]]; then
        printf '%s\n' "$rel" >> "$TMP_LIST"
    fi
done

if [[ -s "$TMP_LIST" ]]; then
    (cd "$PROJECT_ROOT" && tar -czf "$BACKUP" -T "$TMP_LIST")
    cp "$TMP_LIST" "$MANIFEST"
    echo "Backup créé : $BACKUP"
else
    echo "Aucun fichier existant à sauvegarder."
    echo "Aucun fichier existant à sauvegarder." > "$MANIFEST"
fi

for rel in "${FILES[@]}"; do
    mkdir -p "$(dirname "$PROJECT_ROOT/$rel")"
    cp "$FILES_DIR/$rel" "$PROJECT_ROOT/$rel"
    echo "OK $rel"
done

echo
echo "Lot 1 appliqué."
echo "Backup manifest : $MANIFEST"
echo "Prochaine étape : bash scripts/validate_lot1.sh --project=$PROJECT_ROOT"

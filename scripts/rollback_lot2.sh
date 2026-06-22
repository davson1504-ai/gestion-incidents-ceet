#!/usr/bin/env bash
set -uo pipefail

PROJECT_ROOT="$(pwd)"
ASSUME_YES=0
BACKUP_TS=""

for arg in "$@"; do
    case "$arg" in
        --yes|-y) ASSUME_YES=1 ;;
        --backup=*) BACKUP_TS="${arg#--backup=}" ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) echo "Argument inconnu: $arg" >&2; exit 2 ;;
    esac
done

PROJECT_ROOT="$(cd "$PROJECT_ROOT" && pwd)"
cd "$PROJECT_ROOT" || exit 1

if [[ -z "$BACKUP_TS" ]]; then
    LATEST_MANIFEST="$(ls -1t backups/lot2-before-*.manifest.txt 2>/dev/null | head -n 1 || true)"
else
    LATEST_MANIFEST="backups/lot2-before-$BACKUP_TS.manifest.txt"
fi

if [[ -z "$LATEST_MANIFEST" || ! -f "$LATEST_MANIFEST" ]]; then
    echo "Erreur: manifest de backup Lot 2 introuvable." >&2
    exit 1
fi

TAR_FILE="${LATEST_MANIFEST%.manifest.txt}.tar.gz"
if [[ ! -f "$TAR_FILE" ]]; then
    echo "Erreur: archive de backup introuvable: $TAR_FILE" >&2
    exit 1
fi

echo "Rollback depuis : $TAR_FILE"

if [[ $ASSUME_YES -ne 1 ]]; then
    read -r -p "Restaurer ce backup Lot 2 ? Tape 'oui' pour confirmer: " confirm
    if [[ "$confirm" != "oui" ]]; then
        echo "Annulé."
        exit 0
    fi
fi

RESTORE_DIR="$(mktemp -d)"
tar -xzf "$TAR_FILE" -C "$RESTORE_DIR"

while read -r action rel; do
    [[ -z "${action:-}" || -z "${rel:-}" ]] && continue

    if [[ "$action" == "CREATED" ]]; then
        rm -f "$PROJECT_ROOT/$rel"
        echo "SUPPRIME $rel"
    elif [[ "$action" == "MODIFIED" ]]; then
        mkdir -p "$PROJECT_ROOT/$(dirname "$rel")"
        cp -a "$RESTORE_DIR/$rel" "$PROJECT_ROOT/$rel"
        echo "RESTAURE $rel"
    fi
done < "$LATEST_MANIFEST"

rm -rf "$RESTORE_DIR"

echo "Rollback Lot 2 terminé."

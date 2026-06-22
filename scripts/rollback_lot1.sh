#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$PWD"
BACKUP_FILE=""
YES=0

for arg in "$@"; do
    case "$arg" in
        --yes|-y) YES=1 ;;
        --backup=*) BACKUP_FILE="${arg#--backup=}" ;;
        --project=*) PROJECT_ROOT="${arg#--project=}" ;;
        *) if [[ -d "$arg" ]]; then PROJECT_ROOT="$arg"; fi ;;
    esac
done

PROJECT_ROOT="$(cd "$PROJECT_ROOT" && pwd)"

if [[ ! -f "$PROJECT_ROOT/artisan" ]]; then
    echo "Erreur: $PROJECT_ROOT ne semble pas être la racine du projet Laravel."
    exit 1
fi

if [[ -z "$BACKUP_FILE" ]]; then
    BACKUP_FILE="$(ls -1t "$PROJECT_ROOT"/backups/lot1-before-*.tar.gz 2>/dev/null | head -1 || true)"
fi

if [[ -z "$BACKUP_FILE" || ! -f "$BACKUP_FILE" ]]; then
    echo "Erreur: aucun backup Lot 1 trouvé."
    echo "Utilisation: bash scripts/rollback_lot1.sh --backup=/chemin/backup.tar.gz"
    exit 1
fi

echo "Projet cible : $PROJECT_ROOT"
echo "Backup utilisé : $BACKUP_FILE"

if [[ "$YES" -ne 1 ]]; then
    read -r -p "Restaurer ce backup ? [y/N] " answer
    case "$answer" in
        y|Y|yes|YES|oui|OUI) ;;
        *) echo "Annulé."; exit 0 ;;
    esac
fi

(cd "$PROJECT_ROOT" && tar -xzf "$BACKUP_FILE")
echo "Rollback Lot 1 terminé."

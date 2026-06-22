#!/usr/bin/env bash
set -euo pipefail
PROJECT="$(pwd)"; YES=0
for arg in "$@"; do case "$arg" in --yes) YES=1 ;; --project=*) PROJECT="${arg#--project=}" ;; *) echo "Argument inconnu: $arg"; exit 1 ;; esac; done
cd "$PROJECT"; [[ "$YES" -ne 1 ]] && { echo "Ajoute --yes pour confirmer le rollback Lot 3." >&2; exit 1; }
LATEST="$(ls -1t backups/lot3-before-*.tar.gz 2>/dev/null | head -n 1 || true)"; MANIFEST="${LATEST%.tar.gz}.manifest.txt"
[[ -z "$LATEST" || ! -f "$LATEST" || ! -f "$MANIFEST" ]] && { echo "Aucun backup Lot 3 trouvé." >&2; exit 1; }
echo "Rollback depuis : $LATEST"; tmpdir="$(mktemp -d)"; tar -xzf "$LATEST" -C "$tmpdir" 2>/dev/null || true
while IFS= read -r file; do if [[ -f "$tmpdir/$file" ]]; then mkdir -p "$(dirname "$file")"; cp "$tmpdir/$file" "$file"; echo "RESTAURE $file"; else rm -f "$file"; echo "SUPPRIME $file"; fi; done < "$MANIFEST"
rm -rf "$tmpdir"; echo "Rollback Lot 3 terminé."

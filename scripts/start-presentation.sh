#!/usr/bin/env bash

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

fail() {
    printf 'ERREUR PRESENTATION: %s\n' "$1" >&2
    exit 1
}

command -v docker >/dev/null 2>&1 \
    || fail "Docker est introuvable. Installez ou demarrez Docker Desktop."

docker info >/dev/null 2>&1 \
    || fail "Docker Desktop n'est pas pret. Demarrez-le puis relancez ce script."

command -v php >/dev/null 2>&1 \
    || fail "PHP est introuvable dans WSL."

[[ -f artisan ]] || fail "Le script doit etre lance depuis le projet Laravel."
[[ -f .env ]] || fail "Le fichier .env local est absent."
[[ -f public/build/manifest.json ]] \
    || fail "Les assets compiles sont absents. Executez d'abord: npm run build"

# Ce fichier généré par Vite force Laravel à contacter le serveur de
# développement. Il doit être absent en présentation hors ligne.
rm -f public/hot

# Un fichier local non versionne peut surcharger .env. Les valeurs de securite
# ci-dessous restent toutefois imposees pour toute presentation.
if [[ -f .env.presentation ]]; then
    set -a
    # shellcheck disable=SC1091
    source .env.presentation
    set +a
fi

export APP_ENV=local
export APP_DEBUG=false
export APP_URL=http://127.0.0.1:8000
export QUEUE_CONNECTION=sync
export BROADCAST_CONNECTION=log
export AUTH_ALLOW_PUBLIC_REGISTRATION=false
export SESSION_SECURE_COOKIE=false

printf '%s\n' "[1/5] Verification de Docker... OK"
printf '%s\n' "[2/5] Demarrage de MySQL et attente de son etat sain..."
docker compose up -d --wait mysql \
    || fail "MySQL n'a pas pu demarrer. Consultez: docker compose logs mysql"

printf '%s\n' "[3/5] Nettoyage des caches Laravel..."
php artisan optimize:clear --no-ansi \
    || fail "Laravel n'a pas pu vider ses caches."

printf '%s\n' "[4/5] Verification des migrations..."
MIGRATION_STATUS="$(php artisan migrate:status --no-ansi)" \
    || fail "La base MySQL n'est pas accessible depuis Laravel."
printf '%s\n' "$MIGRATION_STATUS"

if grep -q 'Pending' <<<"$MIGRATION_STATUS"; then
    fail "Des migrations sont en attente. Aucune migration n'a ete executee automatiquement."
fi

printf '%s\n' "[5/5] Verification des trois roles de demonstration..."
ROLE_COUNTS="$(docker compose exec -T mysql sh -c 'mysql --batch --skip-column-names -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SELECT \"Administrateur\", COUNT(users.id) FROM roles INNER JOIN model_has_roles ON model_has_roles.role_id = roles.id INNER JOIN users ON users.id = model_has_roles.model_id WHERE roles.name IN (\"Administrateur\", \"admin\") UNION ALL SELECT \"Superviseur\", COUNT(users.id) FROM roles INNER JOIN model_has_roles ON model_has_roles.role_id = roles.id INNER JOIN users ON users.id = model_has_roles.model_id WHERE roles.name IN (\"Superviseur\", \"superviseur\") UNION ALL SELECT \"Operateur\", COUNT(users.id) FROM roles INNER JOIN model_has_roles ON model_has_roles.role_id = roles.id INNER JOIN users ON users.id = model_has_roles.model_id WHERE roles.name IN (\"Opérateur\", \"OpÃ©rateur\", \"Operateur\", \"operateur\");"' 2>/dev/null)" \
    || fail "Impossible de verifier les comptes de demonstration."

grep -Eq '^Administrateur[[:space:]]+[1-9][0-9]*$' <<<"$ROLE_COUNTS" \
    || fail "Aucun compte Administrateur n'est disponible."
grep -Eq '^Superviseur[[:space:]]+[1-9][0-9]*$' <<<"$ROLE_COUNTS" \
    || fail "Aucun compte Superviseur n'est disponible."
grep -Eq '^Operateur[[:space:]]+[1-9][0-9]*$' <<<"$ROLE_COUNTS" \
    || fail "Aucun compte Operateur n'est disponible."

printf '%s\n' "Preflight termine: base, migrations et roles sont prets."
printf '%s\n' "Application: http://127.0.0.1:8000"
printf '%s\n' "Arret du serveur: Ctrl+C"

if [[ "${PRESENTATION_PREFLIGHT_ONLY:-0}" == "1" ]]; then
    exit 0
fi

exec php artisan serve --host=127.0.0.1 --port=8000 --no-ansi

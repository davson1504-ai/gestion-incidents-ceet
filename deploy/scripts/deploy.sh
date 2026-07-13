#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/gestion-incidents-ceet/current}"
ENV_FILE="${ENV_FILE:-$APP_DIR/.env}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/gestion-incidents-ceet}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
REVERB_SUPERVISOR_PROGRAM="${REVERB_SUPERVISOR_PROGRAM:-gestion-incidents-ceet-reverb}"
QUEUE_SUPERVISOR_PROGRAM="${QUEUE_SUPERVISOR_PROGRAM:-gestion-incidents-ceet-worker:*}"
APP_HEALTH_URL="${APP_HEALTH_URL:-https://incidents.example.tg/up}"
APP_BASE_URL="${APP_BASE_URL:-https://incidents.example.tg}"

fail() {
    printf 'ERROR: %s\n' "$1" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "Missing required command: $1"
}

read_env_value() {
    local key="$1"
    grep -E "^${key}=" "$ENV_FILE" | tail -n 1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

[ -d "$APP_DIR" ] || fail "Application directory not found: $APP_DIR"
[ -f "$ENV_FILE" ] || fail "Environment file not found: $ENV_FILE"

require_command "$PHP_BIN"
require_command "$COMPOSER_BIN"
require_command "$NPM_BIN"
require_command mysql
require_command mysqldump
require_command curl
require_command supervisorctl
require_command systemctl

cd "$APP_DIR"

"$(dirname "$0")/preflight.sh"

DB_HOST="$(read_env_value DB_HOST)"
DB_PORT="$(read_env_value DB_PORT)"
DB_DATABASE="$(read_env_value DB_DATABASE)"
DB_USERNAME="$(read_env_value DB_USERNAME)"
DB_PASSWORD="$(read_env_value DB_PASSWORD)"

[ -n "$DB_DATABASE" ] || fail "DB_DATABASE is required"
[ -n "$DB_USERNAME" ] || fail "DB_USERNAME is required"

mkdir -p "$BACKUP_DIR"
backup_file="$BACKUP_DIR/${DB_DATABASE}_$(date -u +%Y%m%dT%H%M%SZ).sql"

MAINTENANCE_SECRET="deploy-$(date -u +%Y%m%d%H%M%S)-$RANDOM"
"$PHP_BIN" artisan down --render="errors::503" --retry=60 --secret="$MAINTENANCE_SECRET"

export MYSQL_PWD="$DB_PASSWORD"
mysqldump \
    --host="${DB_HOST:-127.0.0.1}" \
    --port="${DB_PORT:-3306}" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --routines \
    --triggers \
    "$DB_DATABASE" > "$backup_file"
unset MYSQL_PWD

[ -s "$backup_file" ] || fail "Database backup is empty: $backup_file"
printf 'Database backup created and verified: %s\n' "$backup_file"

"$COMPOSER_BIN" install --no-dev --no-interaction --prefer-dist --optimize-autoloader
"$NPM_BIN" ci --no-audit --no-fund
"$NPM_BIN" run build

printf 'Type MIGRATE to run php artisan migrate --force: '
read -r confirmation
[ "$confirmation" = "MIGRATE" ] || fail "Migration confirmation not provided"

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache
"$PHP_BIN" artisan queue:restart

systemctl restart "$PHP_FPM_SERVICE"
supervisorctl reread
supervisorctl update
supervisorctl restart "$REVERB_SUPERVISOR_PROGRAM"
supervisorctl restart "$QUEUE_SUPERVISOR_PROGRAM"

cookie_jar="$(mktemp)"
trap 'rm -f "$cookie_jar"' EXIT
curl --fail --silent --show-error --cookie-jar "$cookie_jar" "$APP_BASE_URL/$MAINTENANCE_SECRET" >/dev/null
curl --fail --silent --show-error --cookie "$cookie_jar" "$APP_HEALTH_URL" >/dev/null

printf 'Health check passed for %s.\n' "$APP_HEALTH_URL"
printf 'Type UP to leave maintenance mode: '
read -r up_confirmation
[ "$up_confirmation" = "UP" ] || fail "Application left in maintenance mode for manual inspection"

"$PHP_BIN" artisan up
printf 'Deployment finished.\n'

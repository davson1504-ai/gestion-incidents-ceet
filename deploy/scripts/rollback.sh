#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="${APP_ROOT:-/var/www/gestion-incidents-ceet}"
CURRENT_LINK="${CURRENT_LINK:-$APP_ROOT/current}"
ROLLBACK_RELEASE="${ROLLBACK_RELEASE:-}"
PHP_BIN="${PHP_BIN:-php}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
REVERB_SUPERVISOR_PROGRAM="${REVERB_SUPERVISOR_PROGRAM:-gestion-incidents-ceet-reverb}"
QUEUE_SUPERVISOR_PROGRAM="${QUEUE_SUPERVISOR_PROGRAM:-gestion-incidents-ceet-worker:*}"

fail() {
    printf 'ERROR: %s\n' "$1" >&2
    exit 1
}

[ -n "$ROLLBACK_RELEASE" ] || fail "Set ROLLBACK_RELEASE to the release directory to restore"
[ -d "$ROLLBACK_RELEASE" ] || fail "Rollback release not found: $ROLLBACK_RELEASE"
[ -L "$CURRENT_LINK" ] || fail "Current path is not a symlink: $CURRENT_LINK"

printf 'This script rolls back code only. It never runs migrate:rollback.\n'
printf 'Restore the database separately from a verified backup only when required.\n'
printf 'Type ROLLBACK-CODE to switch current to %s: ' "$ROLLBACK_RELEASE"
read -r confirmation
[ "$confirmation" = "ROLLBACK-CODE" ] || fail "Rollback confirmation not provided"

cd "$CURRENT_LINK"
"$PHP_BIN" artisan down --render="errors::503" --retry=60 || true

ln -sfn "$ROLLBACK_RELEASE" "$CURRENT_LINK"

cd "$CURRENT_LINK"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan queue:restart

systemctl restart "$PHP_FPM_SERVICE"
supervisorctl restart "$REVERB_SUPERVISOR_PROGRAM"
supervisorctl restart "$QUEUE_SUPERVISOR_PROGRAM"

printf 'Code rollback complete. Run post-rollback checks before php artisan up.\n'

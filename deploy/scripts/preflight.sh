#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/gestion-incidents-ceet/current}"
ENV_FILE="${ENV_FILE:-$APP_DIR/.env}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NODE_BIN="${NODE_BIN:-node}"
NPM_BIN="${NPM_BIN:-npm}"

fail() {
    printf 'ERROR: %s\n' "$1" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "Missing required command: $1"
}

[ -d "$APP_DIR" ] || fail "Application directory not found: $APP_DIR"
[ -f "$ENV_FILE" ] || fail "Environment file not found: $ENV_FILE"

require_command "$PHP_BIN"
require_command "$COMPOSER_BIN"
require_command "$NODE_BIN"
require_command "$NPM_BIN"
require_command mysql
require_command mysqldump

cd "$APP_DIR"

"$PHP_BIN" -v >/dev/null
"$COMPOSER_BIN" validate --strict
"$COMPOSER_BIN" check-platform-reqs
"$NPM_BIN" --version >/dev/null

"$PHP_BIN" artisan about --only=environment
"$PHP_BIN" artisan route:list >/dev/null

[ -w storage ] || fail "storage/ must be writable by the web server user"
[ -w bootstrap/cache ] || fail "bootstrap/cache/ must be writable by the web server user"

printf 'Preflight checks passed.\n'

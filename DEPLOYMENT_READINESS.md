# Deployment readiness

Date: 2026-07-13
Branch: `chore/prepare-production-deployment`

## Verdict

NO-GO for immediate production deployment.

The application code, tests, build, Laravel caches and shell script syntax checks pass locally. The remaining blockers are operational: the production server, production database connectivity, TLS, Supervisor, Nginx, cron and real backup/restore flow have not been verified. Local `php artisan migrate:status` cannot connect because the current local `.env` resolves `DB_HOST=mysql`, which is only valid inside the Docker/Sail network.

## Files changed for production readiness

- `.env.production.example`
- `README.md`
- `config/reverb.php`
- `app/Console/Commands/CreateFirstAdminCommand.php`
- `database/seeders/AdminUserSeeder.php`
- `database/seeders/OperatorUserSeeder.php`
- `database/seeders/IncidentSeeder.php`
- `database/seeders/LogSeeder.php`
- `database/seeders/ProductionSeeder.php`
- `tests/Feature/ProductionDeploymentSecurityTest.php`
- `deploy/nginx/gestion-incidents-ceet.conf`
- `deploy/nginx/reverb-websocket.conf`
- `deploy/supervisor/reverb.conf`
- `deploy/supervisor/queue-worker.conf`
- `deploy/cron/laravel-scheduler`
- `deploy/scripts/preflight.sh`
- `deploy/scripts/deploy.sh`
- `deploy/scripts/rollback.sh`
- `docs/deployment-production.md`

Existing unrelated or pre-existing modified/untracked work remains in the worktree, including incident workflow files, backup `.bak` files, duplicated `resources/css/css/`, duplicated `resources/js/pages/pages/`, `NUL`, and the incident report workflow migration/resource/test files. These were not removed.

## Command results

Initial and final checks run from WSL in `/home/lenovo/projects/gestion-incidents-ceet`.

- `composer validate --strict`: passed.
- `composer install --no-interaction --prefer-dist`: passed.
- `npm ci --no-audit --no-fund`: initially failed with `EACCES` on root-owned `node_modules/.vite/deps/_metadata.json`; fixed local generated dependency ownership; final run passed.
- `php artisan optimize:clear`: passed.
- `php artisan test`: passed, `117 passed`, `447 assertions`.
- `npm run build`: passed with Vite `v6.4.2`; warnings only for existing empty chunks: `supervisor-dashboard`, `operator-dashboard`, `incidents-show`, `incidents-create`.
- `php artisan route:list`: passed, `126` routes shown.
- `php artisan about`: passed. Laravel `12.57.0`, PHP `8.4.22`, local env `local`, debug enabled locally.
- `composer check-platform-reqs`: passed for required PHP extensions.
- Laravel caches after changes: `config:cache`, `route:cache`, `view:cache` passed. Current cache status: config cached, routes cached, views cached.
- Shell syntax: `bash -n deploy/scripts/preflight.sh deploy/scripts/deploy.sh deploy/scripts/rollback.sh` passed.
- `php artisan migrate:status --no-interaction`: not verified locally; failed because `DB_HOST=mysql` cannot resolve outside the Docker/Sail network.

Composer emitted deprecation notices from globally installed Composer dependencies under PHP 8.4, but the commands exited successfully.

## Migrations and risks

Current migration list includes the new/untracked workflow migrations:

- `2026_07_06_100000_add_report_control_workflow_to_incident_reports_table.php`
- `2026_07_06_100100_sync_incident_report_workflow_permissions.php`

Risks:

- `2026_07_06_100000...` adds nullable columns and foreign keys to `incident_reports`, then backfills `operateur_id`, `date_soumission` and `statut_rapport` from existing columns. The `down()` path drops these columns and can discard report workflow data.
- `2026_07_06_100100...` creates/syncs permissions and role assignments. The `down()` path revokes some permissions and may affect authorization if run after production use.
- Because rollback migrations can remove or alter data, `deploy/scripts/rollback.sh` intentionally never runs `migrate:rollback`.

## Server prerequisites

- Linux server with Nginx.
- PHP-FPM 8.4 or compatible PHP 8.2+ runtime.
- Composer.
- Node.js and npm.
- MySQL 8.x server and MySQL client tools: `mysql`, `mysqldump`.
- Supervisor.
- Cron.
- TLS certificates for the app host and Reverb WebSocket host.
- Writable directories limited to `storage/` and `bootstrap/cache/` for the web server user. Do not use `777`.

## Variables still to configure

Use `.env.production.example` as the template and fill real values only on the server:

- `APP_KEY`
- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `REVERB_APP_ID`
- `REVERB_APP_KEY`
- `REVERB_APP_SECRET`
- `REVERB_HOST`
- `REVERB_ALLOWED_ORIGINS`

Production must keep:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `AUTH_ALLOW_PUBLIC_REGISTRATION=false`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`

## Backup procedure

The deployment script creates a MySQL dump before migration:

```bash
BACKUP_DIR=/var/backups/gestion-incidents-ceet deploy/scripts/deploy.sh
```

The dump uses `mysqldump --single-transaction --routines --triggers` and verifies that the backup file is non-empty before continuing.

Before production use, perform a manual restore test on a non-production database to prove the backup can be restored.

## Deployment procedure

1. Prepare the server with Nginx, PHP-FPM, Supervisor, cron, MySQL client tools, Composer, Node.js and npm.
2. Copy `.env.production.example` to `.env` on the server and fill real secrets there only.
3. Install Nginx configs from `deploy/nginx/`.
4. Install Supervisor configs from `deploy/supervisor/`.
5. Install cron from `deploy/cron/laravel-scheduler`.
6. Run preflight:

```bash
APP_DIR=/var/www/gestion-incidents-ceet/current deploy/scripts/preflight.sh
```

7. Run deployment:

```bash
APP_DIR=/var/www/gestion-incidents-ceet/current \
APP_BASE_URL=https://incidents.example.tg \
APP_HEALTH_URL=https://incidents.example.tg/up \
deploy/scripts/deploy.sh
```

The script:

- uses `set -Eeuo pipefail`;
- verifies prerequisites;
- avoids shell tracing and does not print secrets;
- places the app in maintenance mode with a temporary bypass secret;
- creates and verifies a MySQL backup before migration;
- runs `composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader`;
- runs `npm ci --no-audit --no-fund` and `npm run build`;
- asks for `MIGRATE` before `php artisan migrate --force`;
- never runs `--seed` automatically;
- generates Laravel caches;
- restarts PHP-FPM, Reverb and the queue worker;
- tests `/up` through the maintenance bypass;
- asks for `UP` before leaving maintenance mode.

Production reference data is separate and explicit:

```bash
php artisan db:seed --class=ProductionSeeder --force
php artisan app:create-first-admin
```

## Rollback procedure

Code rollback:

```bash
APP_ROOT=/var/www/gestion-incidents-ceet \
ROLLBACK_RELEASE=/var/www/gestion-incidents-ceet/releases/previous-release \
deploy/scripts/rollback.sh
```

The rollback script separates code rollback from database restoration and never runs `migrate:rollback`.

Database restoration must be a separate manual decision using a verified backup. This is required because some migrations can drop columns or revoke permissions during rollback.

## Post-deployment tests

- `/up` returns success over HTTPS.
- Login works for the first administrator created by `app:create-first-admin`.
- Incident list, incident detail and dashboards load.
- Report workflow actions remain authorized for the correct roles.
- Reverb WebSocket connects only from `REVERB_ALLOWED_ORIGINS`.
- Queue worker processes jobs.
- Scheduler runs every minute.
- Application logs contain no exposed secrets.
- `php artisan migrate:status --no-interaction` confirms expected migration state on the production database.

## Not verifiable without server access

- Real production database connectivity.
- Real migration status on the target database.
- MySQL backup restore viability on production-like data.
- Nginx virtual host behavior and TLS certificates.
- Reverb WebSocket behavior through the production reverse proxy.
- Supervisor process restarts on the target server.
- Cron execution on the target server.
- `/up` health check over the real production URL.
- File ownership for the production web server user.

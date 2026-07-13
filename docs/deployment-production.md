# Production deployment guide

This guide prepares a Laravel production deployment without running remote commands from the repository.

## Server prerequisites

- Linux server with Nginx, PHP-FPM 8.4 or a compatible PHP 8.2+ runtime, Composer, Node.js, npm, MySQL client tools, Supervisor and cron.
- MySQL 8.x database created before deployment.
- TLS certificates for the application host and Reverb WebSocket host.
- A server user that owns the release files.
- The web server user must write only to `storage/` and `bootstrap/cache/`. Do not use `777`.

## Environment

Copy `.env.production.example` to `.env` on the server and fill real values there only. Never commit secrets.

Required values include:

- `APP_KEY`
- `APP_URL`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
- `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`
- `REVERB_HOST`, `REVERB_ALLOWED_ORIGINS`

`APP_ENV` must be `production`, `APP_DEBUG` must be `false`, public registration must stay disabled, and `REVERB_ALLOWED_ORIGINS` must list explicit HTTPS origins.

## Initial data

For production catalog data only:

```bash
php artisan db:seed --class=ProductionSeeder --force
```

Create the first administrator interactively:

```bash
php artisan app:create-first-admin
```

Do not run demo seeders in production. Do not run `migrate --seed` in production.

## Nginx and services

Use `deploy/nginx/gestion-incidents-ceet.conf` for the Laravel application. It serves only `public/`.

Use `deploy/nginx/reverb-websocket.conf` for Reverb WebSocket proxying to `127.0.0.1:8080`.

Install Supervisor programs from:

- `deploy/supervisor/reverb.conf`
- `deploy/supervisor/queue-worker.conf`

Install the Laravel scheduler cron from `deploy/cron/laravel-scheduler`.

## Preflight

Run:

```bash
APP_DIR=/var/www/gestion-incidents-ceet/current deploy/scripts/preflight.sh
```

The script checks runtime commands, Composer metadata, platform requirements, Artisan route loading and writable directories.

## Deployment

Run the deployment script on the server from the checked-out release:

```bash
APP_DIR=/var/www/gestion-incidents-ceet/current \
APP_HEALTH_URL=https://incidents.example.tg/up \
deploy/scripts/deploy.sh
```

The script places the application in maintenance mode, creates and verifies a MySQL backup, installs production Composer dependencies, builds assets, asks for explicit confirmation before `migrate --force`, creates Laravel caches, restarts PHP-FPM, Reverb and the queue worker, checks `/up`, then asks before leaving maintenance mode.

The script never runs `--seed` automatically.

## Rollback

Code rollback and database restoration are separate operations.

For code rollback:

```bash
APP_ROOT=/var/www/gestion-incidents-ceet \
ROLLBACK_RELEASE=/var/www/gestion-incidents-ceet/releases/previous-release \
deploy/scripts/rollback.sh
```

The rollback script never runs `migrate:rollback`. Some migrations can delete or truncate data, so database restoration must be a deliberate manual operation from a verified backup.

## Post-deployment checks

- `/up` returns success.
- Login works for the manually created admin.
- Dashboards and incident lists load.
- Reverb WebSocket connections succeed from the configured HTTPS origin.
- Queue worker processes jobs.
- Scheduler runs once per minute.
- Logs contain no secret output.

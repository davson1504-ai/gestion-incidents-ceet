# Application de gestion des incidents du réseau électrique de la CEET

Application Laravel 12 destinée à la déclaration, au suivi, à l’analyse et à la traçabilité des incidents du réseau électrique de la CEET au Togo.

## Stack
- Laravel 12
- PHP 8.2+
- Blade
- TailwindCSS / Bootstrap utilitaire existant
- Spatie `laravel-permission`
- Laravel Reverb
- DomPDF
- Maatwebsite Excel 3.1
- MySQL 8.4
- Docker Sail

## Fonctionnalités principales
- Gestion complète des incidents avec filtres multi-critères
- Dashboard KPI et graphiques Chart.js
- Tableau de bord des incidents en cours
- Catalogues CEET enrichis (départs, types, causes, statuts, priorités)
- Rapports journaliers et mensuels en PDF
- Export incidents en CSV et Excel natif `.xlsx`
- Traçabilité double via `IncidentAction` et `Log`
- Temps réel via Reverb
- Gestion des rôles Administrateur / Superviseur / Opérateur Terrain

## Installation rapide
```bash
git clone https://github.com/davson1504-ai/gestion-incidents-ceet.git
cd gestion-incidents-ceet
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan reverb:start
npm run dev
```

## Docker Sail
```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan reverb:start
./vendor/bin/sail npm run dev
```

## Documentation
- [Guide utilisateur](docs/guide-utilisateur.md)
- [Guide d'installation](docs/guide-installation.md)

## Variables d’environnement critiques
- `APP_URL`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `REVERB_APP_ID`
- `REVERB_APP_KEY`
- `REVERB_APP_SECRET`
- `REVERB_HOST`
- `REVERB_PORT`
- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_FROM_ADDRESS`

## Scripts utiles
```bash
composer dev
composer dev:full
composer dev:lite
npm run build
php artisan optimize
```

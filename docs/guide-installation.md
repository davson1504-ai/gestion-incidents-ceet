# Guide d'installation WSL / Sail

## 1. Position de référence du projet

Le dépôt de référence doit être utilisé depuis WSL avec Laravel Sail. Les commandes PHP, Composer, npm, migrations, tests et build doivent être lancées dans l'environnement Sail afin d'utiliser les mêmes versions et le même réseau Docker que l'application.

Éviter d'exécuter les commandes métier Laravel depuis PowerShell si le fichier `.env` utilise `DB_HOST=mysql`, car ce nom d'hôte est résolu dans le réseau Docker Sail.

## 2. Prérequis

- WSL 2 avec une distribution Linux installée.
- Docker Desktop ou Docker Engine accessible depuis WSL.
- Git.
- Composer disponible pour installer Sail au premier démarrage si `vendor/` n'existe pas.

## 3. Installation initiale

```bash
git clone https://github.com/davson1504-ai/gestion-incidents-ceet.git
cd gestion-incidents-ceet
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

## 4. Démarrage quotidien

```bash
cd gestion-incidents-ceet
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

Dans un autre terminal, démarrer Reverb si le temps réel est nécessaire :

```bash
./vendor/bin/sail artisan reverb:start --host=0.0.0.0 --port=8080
```

## 5. Commandes de validation

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail npm run build
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan migrate:status
```

## 6. Variables d'environnement critiques

- `APP_URL`
- `APP_ENV`
- `APP_DEBUG`
- `DB_CONNECTION`
- `DB_HOST`
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

En Sail, `DB_HOST` doit généralement rester à `mysql`.

## 7. Création du premier compte administrateur

```bash
./vendor/bin/sail artisan db:seed --class=AdminUserSeeder
```

Si les rôles ou permissions doivent être resynchronisés :

```bash
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
```

## 8. Préparation production

```bash
./vendor/bin/sail composer install --no-dev --optimize-autoloader
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache
```

Avant livraison, vérifier que `APP_ENV=production` et `APP_DEBUG=false`.

## 9. Résolution des problèmes courants

### Base de données inaccessible hors Sail

Si une commande lancée depuis PowerShell échoue avec `getaddrinfo for mysql failed`, relancer la commande dans WSL avec Sail :

```bash
./vendor/bin/sail artisan migrate:status
```

### Assets Vite non chargés

Vérifier que les dépendances Node sont installées dans Sail :

```bash
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

### Permissions `storage/` et `bootstrap/cache`

```bash
./vendor/bin/sail artisan optimize:clear
```

### Reverb indisponible

Vérifier les variables `REVERB_*`, puis relancer :

```bash
./vendor/bin/sail artisan reverb:start --host=0.0.0.0 --port=8080
```

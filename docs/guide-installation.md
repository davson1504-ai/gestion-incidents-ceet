# Guide d'installation

## 1. Prérequis système
- PHP 8.2 ou supérieur
- Composer 2.x
- Node.js 20+ et npm
- MySQL 8.4
- Docker Desktop ou Docker Engine + Docker Compose en option

## 2. Installation locale sans Docker
1. Clonez le dépôt :
   ```bash
   git clone https://github.com/davson1504-ai/gestion-incidents-ceet.git
   cd gestion-incidents-ceet
   ```
2. Préparez l’environnement :
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Installez les dépendances :
   ```bash
   composer install
   npm install
   ```
4. Configurez la base de données dans `.env`.
5. Lancez les migrations et les seeders :
   ```bash
   php artisan migrate --seed
   ```
6. Démarrez Reverb :
   ```bash
   php artisan reverb:start
   ```
7. Lancez Vite :
   ```bash
   npm run dev
   ```

## 3. Installation avec Laravel Sail / Docker
1. Préparez l’environnement :
   ```bash
   cp .env.example .env
   composer install
   ```
2. Démarrez les conteneurs :
   ```bash
   ./vendor/bin/sail up -d
   ```
3. Générez la clé d’application puis migrez :
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```
4. Démarrez Reverb :
   ```bash
   ./vendor/bin/sail artisan reverb:start
   ```
5. Démarrez Vite :
   ```bash
   ./vendor/bin/sail npm run dev
   ```

## 4. Variables d’environnement critiques
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, `REVERB_PORT`
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_FROM_ADDRESS`
- `APP_URL` pour le bon chargement des assets Vite

## 5. Créer le premier compte Administrateur
Utilisez le seeder dédié :
```bash
php artisan db:seed --class=AdminUserSeeder
```

Alternative avec Tinker :
```bash
php artisan tinker
```
Puis créez un utilisateur et attribuez-lui le rôle `Administrateur`.

## 6. Lancer en production
```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Résolution des problèmes courants
### Permissions `storage/` et `bootstrap/cache`
Assurez-vous que le serveur web peut écrire dans ces dossiers.

### Clé `.env` absente
Exécutez :
```bash
php artisan key:generate
```

### Port Reverb bloqué
Modifiez `REVERB_PORT` et adaptez votre commande `php artisan reverb:start`.

### Base de données inaccessible
Vérifiez les variables `DB_*`, le service MySQL et le port exposé.

### Assets Vite non chargés
Contrôlez `APP_URL`, relancez `npm run dev` en local ou `npm run build` en production.

# Mode présentation locale

Ce mode démarre Laravel directement dans WSL et uniquement MySQL dans Docker.
Il n'exécute aucune migration et aucun seeder automatiquement. Il impose
`APP_DEBUG=false`, désactive l'inscription publique, utilise une file synchrone
et remplace la diffusion temps réel par les logs.

## Démarrer

1. Démarrer Docker Desktop et attendre qu'il soit prêt.
2. Ouvrir un terminal WSL dans le projet.
3. Lancer :

```bash
bash scripts/start-presentation.sh
```

4. Ouvrir <http://127.0.0.1:8000>.

Le script s'arrête avec un message explicite si Docker, MySQL, les migrations
ou l'un des trois rôles requis ne sont pas prêts. Il ne tente jamais de réparer
la base automatiquement.

## Fonctionnement hors ligne

Les polices de texte utilisent les polices système et les icônes Material
Symbols sont intégrées localement sous licence Apache-2.0. Les pages de
présentation ne chargent plus Google Fonts, Bunny Fonts, CDN ou images Google.

Toujours exécuter `npm run build` avant la répétition. Le lanceur refuse de
démarrer si le manifeste compilé manque et retire automatiquement `public/hot`,
car ce fichier temporaire forcerait Laravel à contacter Vite sur le port 5173.
Il ne faut donc pas laisser `npm run dev` ou `composer dev` actif en parallèle
du mode présentation.

Le fichier `.env.presentation.example` sert de modèle sans secret. Si une
configuration distincte est nécessaire, le copier manuellement vers
`.env.presentation`, remplir uniquement les valeurs locales et ne jamais
versionner ce fichier. Sans ce fichier, le script utilise la connexion définie
dans `.env` et impose quand même les réglages sûrs de présentation.

## Arrêter

Arrêter Laravel avec `Ctrl+C`. MySQL peut rester disponible pour la répétition.
Pour l'arrêter également :

```bash
docker compose stop mysql
```

## Vérifier la base sans la modifier

```bash
docker compose ps mysql
php artisan migrate:status
php artisan db:show --counts
```

MySQL doit être `healthy`, toutes les migrations doivent être `Ran`, et les
tables `users`, `roles` et `incidents` doivent contenir les données attendues.

## Tester les trois comptes

Préparer ou mettre à jour les trois comptes avec la commande interactive :

```bash
php artisan demo:prepare-accounts
```

La commande accepte aussi les six variables locales `DEMO_ADMIN_*`,
`DEMO_SUPERVISOR_*` et `DEMO_OPERATOR_*` décrites dans
`.env.presentation.example`. Elle refuse les mots de passe faibles, ne les
affiche jamais et ne peut pas s'exécuter en production.

Tester successivement, sans inscrire de mot de passe dans ce document :

1. `admin@ceet.tg` : tableau de bord, utilisateurs, catalogues et statut système ;
2. `superviseur.lome@ceet.tg` : création, affectation, validation et clôture ;
3. `operateur.a@ceet.tg` : notification, prise en charge, intervention et rapport.

Les mots de passe doivent rester dans les variables locales ou être saisis via
la commande interactive prévue pour la préparation des comptes. Ils ne doivent
jamais apparaître dans Git, les captures d'écran ou les diapositives.

## Restaurer la sauvegarde de la phase 1

La sauvegarde attendue est :

```text
storage/backups/soutenance-preparation.sql
```

La restauration remplace l'état courant de la base. Avant de la lancer, arrêter
Laravel, vérifier le chemin et créer une nouvelle sauvegarde de l'état courant.
Ne jamais utiliser `migrate:fresh`.

Après validation manuelle de ces précautions :

```bash
docker compose up -d --wait mysql
docker compose exec -T mysql sh -c \
  'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  < storage/backups/soutenance-preparation.sql
php artisan optimize:clear
php artisan migrate:status
```

## Reverb, seulement si nécessaire

Le mode par défaut utilise `BROADCAST_CONNECTION=log` et ne dépend donc pas de
Reverb. Pour démontrer explicitement le temps réel, ouvrir un second terminal :

```bash
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Il faut alors utiliser localement une configuration de diffusion Reverb adaptée.
Si cette démonstration n'est pas indispensable, conserver le mode `log`, plus
fiable et entièrement local.

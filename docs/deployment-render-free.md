# Deploiement de demonstration gratuit sur Render

Ce guide prepare une demonstration a cout 0 EUR depuis le commit `0bc99e9`. Il ne remplace pas le deploiement de production Forge/Ploi : il sert a exposer une version web Laravel simple, sans worker, sans Reverb et sans stockage persistant.

## Limites actuelles de l'offre gratuite Render

Selon la documentation Render consultee le 14 juillet 2026 :

- un web service free se met en veille apres 15 minutes sans trafic entrant ;
- le reveil prend environ une minute au prochain acces ;
- chaque workspace recoit 750 heures d'instance free par mois, non reportables ;
- si les heures sont epuisees, les services free sont suspendus jusqu'au mois suivant ;
- le systeme de fichiers local est ephemere et peut etre perdu au redeploiement, redemarrage ou reveil ;
- les disques persistants ne sont pas disponibles pour les web services free ;
- un workspace ne peut avoir qu'une base Render PostgreSQL free active ;
- la base PostgreSQL free est limitee a 1 Go et expire 30 jours apres creation ;
- les web services free ne prennent pas en charge le scaling multi-instance, les disques persistants ni les jobs ponctuels.

## Architecture retenue

- Un seul service Web Laravel Docker avec PHP 8.4, requis par le `composer.lock` actuel.
- Une base Render PostgreSQL free pour la demonstration.
- `QUEUE_CONNECTION=sync`, donc pas de worker separe.
- `BROADCAST_CONNECTION=log`, donc Reverb et WebSocket desactives.
- `MAIL_MAILER=log`, donc aucun envoi SMTP.
- `LOG_CHANNEL=stderr`, compatible avec les logs Render.
- `/up` expose comme health check Laravel.

Les fichiers locaux sont temporaires. Les exports CSV/PDF peuvent etre telecharges pendant la requete, mais aucun upload important ne doit dependre du disque local.

## Variables Render

Render peut appliquer `render.yaml`. Les variables principales sont :

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=<URL Render ou domaine de demonstration>
APP_KEY=<genere par Render>
LOG_CHANNEL=stderr
DB_CONNECTION=pgsql
DB_HOST=<fourni par la base Render>
DB_PORT=<fourni par la base Render>
DB_DATABASE=<fourni par la base Render>
DB_USERNAME=<fourni par la base Render>
DB_PASSWORD=<fourni par la base Render>
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
MAIL_MAILER=log
FILESYSTEM_DISK=local
REVERB_ENABLED=false
```

Ne collez jamais de secret dans le depot. Utilisez les variables d'environnement Render pour les valeurs sensibles.

## Procedure GitHub

1. Pousser la branche `chore/prepare-free-hosting` vers GitHub.
2. Verifier que le commit contient `Dockerfile.render`, `.dockerignore`, `render.yaml`, `.env.render.example`, `docs/deployment-render-free.md` et `RENDER_FREE_READINESS.md`.
3. Dans Render, connecter le depot GitHub.
4. Creer un Blueprint depuis `render.yaml`, ou creer manuellement un Web Service Docker free en indiquant `Dockerfile.render`.
5. Garder `autoDeploy: false` pour controler les redeploiements.

## Premier deploiement manuel

1. Creer le Web Service free et la base PostgreSQL free depuis Render.
2. Definir `APP_URL` avec l'URL Render ou le domaine de demonstration.
3. Lancer le premier build.
4. Une fois le service demarre, executer les migrations prudemment depuis un shell Render si disponible pour le service :

```bash
php artisan migrate --force
```

Ne pas executer `migrate --seed`. Ne pas executer `migrate:rollback` automatiquement.

Render indique que les one-off jobs ne sont pas inclus dans les web services free. Si le shell n'est pas disponible sur l'offre choisie, utiliser temporairement une execution manuelle controlee depuis un environnement local autorise a atteindre la base, puis revenir au plan gratuit.

## Creation manuelle du premier administrateur

Apres les migrations, creer le premier administrateur avec la commande interactive prevue par l'application :

```bash
php artisan app:create-first-admin
```

Ne stockez pas le mot de passe dans le depot ou dans la documentation. Changez-le immediatement si une valeur temporaire a ete utilisee.

## Fonctionnalites conservees

- Authentification et session base de donnees.
- Dashboard.
- Liste, consultation et creation d'incidents.
- Permissions Spatie.
- Exports essentiels CSV/PDF tant qu'ils sont generes a la demande.
- Health check `/up`.
- Compatibilite PostgreSQL validee par migration depuis une base vide locale.

## Fonctionnalites desactivees ou limitees

- Reverb/WebSocket desactive pour la premiere version gratuite.
- Queue worker desactive, les jobs passent en `sync`.
- Scheduler non critique non active dans Render free. Une solution gratuite externe peut appeler une route dediee future ou un endpoint de maintenance protege, mais aucun endpoint de ce type n'est ajoute ici.
- Stockage local non persistant : pas d'uploads importants sans stockage objet externe.
- PostgreSQL free expire apres 30 jours ; cette base convient a une demonstration, pas a une production durable.

## Evolutions vers une offre payante

Pour reactiver une architecture production :

1. Passer le Web Service sur une instance payante.
2. Ajouter un worker separe et remettre `QUEUE_CONNECTION=database` ou Redis.
3. Ajouter un cron job ou scheduler payant pour `php artisan schedule:run`.
4. Reactiver Reverb avec un service WebSocket dedie ou un port/service adapte.
5. Ajouter un disque persistant ou un stockage objet pour les fichiers.
6. Migrer PostgreSQL vers une offre non expirante avec sauvegardes.

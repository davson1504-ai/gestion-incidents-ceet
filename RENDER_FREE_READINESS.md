# Render Free Readiness

Verdict : `FREE HOSTING READY` pour une demonstration gratuite, avec les limites Render free documentees.

## Cible

- Plateforme : Render ou PaaS equivalent.
- Cout vise : 0 EUR.
- Service applicatif : un seul Web Service Docker Laravel.
- Base : Render PostgreSQL free si les migrations passent depuis une base vide.
- Commit de depart : `0bc99e9`.
- Branche : `chore/prepare-free-hosting`.

## Choix de configuration

| Sujet | Decision |
| --- | --- |
| Runtime | Docker, PHP 8.4 Apache |
| Build frontend | `npm ci` puis `npm run build` |
| Build PHP | `composer install --no-dev --optimize-autoloader` |
| Port | variable `PORT`, fallback `10000` |
| Health check | `/up` |
| Queue | `sync` |
| Broadcasting | `log` |
| Reverb | desactive |
| Scheduler | non critique, non active en free |
| Logs | stderr |
| Filesystem | local temporaire |
| Mail | log |

## Limitations gratuites a accepter

- Mise en veille apres 15 minutes d'inactivite.
- Reveil lent, environ une minute.
- 750 heures free par workspace et par mois.
- Systeme de fichiers ephemere.
- Pas de disque persistant sur web service free.
- Une seule base PostgreSQL free active par workspace.
- PostgreSQL free limite a 1 Go et expire apres 30 jours.

## Points a verifier

- [x] `composer validate --strict`
- [x] `composer install --no-interaction --prefer-dist`
- [x] `npm ci --no-audit --no-fund`
- [x] `php artisan test`
- [x] `npm run build`
- [x] migrations depuis une base vide
- [x] compatibilite PostgreSQL
- [x] connexion
- [x] dashboard
- [x] liste des incidents
- [x] creation d'incident
- [x] permissions
- [x] exports essentiels
- [x] `/up`
- [x] `docker build -f Dockerfile.render .`

## Variables Render attendues

Voir `.env.render.example` et `render.yaml`. Les secrets doivent etre generes ou injectes dans Render, jamais commites.

## Verdict final

Verdict apres validations locales :

```text
FREE HOSTING READY
```

# Plan de nettoyage sans risque

Audit réalisé le 20 juillet 2026 sur la branche
`chore/prepare-soutenance`.

## Méthode et garanties

- Comparaison octet par octet avec `cmp` et contrôle SHA-256 des doublons.
- Recherche de références dans `app`, `bootstrap`, `config`, `database`,
  `resources`, `routes`, `tests` et `vite.config.js`.
- Aucun chemin `resources/css/css/` ou `resources/js/pages/pages/` n'est
  référencé par le code ou par Vite.
- Aucun fichier `.bak-*` n'est référencé par l'application.
- Aucun fichier suivi par Git n'est supprimé dans cette phase.
- Aucune donnée, migration, sauvegarde SQL ou configuration secrète n'est
  modifiée.

## Suppression sûre

### Artefacts de commandes

| Élément | Preuve |
|---|---|
| `NUL` | Non suivi, 257 octets, contient uniquement un message d'erreur CMD relatif au chemin UNC. |
| `[^` | Non suivi, vide, créé par une commande de recherche mal interprétée. |
| `]*` | Non suivi, vide, créé au même instant par la même commande fautive. |

### Copies CSS strictement identiques

Chaque fichier ci-dessous est non suivi, non référencé et strictement
identique au fichier de même chemin sous `resources/css/` :

- `resources/css/css/components/alerts.css`
- `resources/css/css/components/badges.css`
- `resources/css/css/components/buttons.css`
- `resources/css/css/components/cards.css`
- `resources/css/css/components/forms.css`
- `resources/css/css/components/modals.css`
- `resources/css/css/components/tables.css`
- `resources/css/css/layouts/app-shell.css`
- `resources/css/css/layouts/ceet-shell.css`
- `resources/css/css/layouts/sidebar.css`
- `resources/css/css/layouts/topbar.css`
- `resources/css/css/mobile.css`
- `resources/css/css/pages/dashboard.css`
- `resources/css/css/pages/historique.css`
- `resources/css/css/pages/incidents-create.css`
- `resources/css/css/pages/incidents-edit.css`
- `resources/css/css/pages/incidents-en-cours.css`
- `resources/css/css/pages/incidents.css`
- `resources/css/css/pages/operator-dashboard.css`
- `resources/css/css/pages/profile.css`
- `resources/css/css/pages/supervisor-dashboard.css`
- `resources/css/css/pages/system-status.css`
- `resources/css/css/theme/ceet-theme.css`
- `resources/css/css/theme/reset.css`
- `resources/css/css/theme/typography.css`

### Copies JavaScript strictement identiques

Chaque fichier ci-dessous est non suivi, non référencé et strictement
identique au fichier de même nom sous `resources/js/pages/` :

- `resources/js/pages/pages/dashboard.js`
- `resources/js/pages/pages/incidents-create.js`
- `resources/js/pages/pages/incidents-en-cours.js`
- `resources/js/pages/pages/incidents-index.js`
- `resources/js/pages/pages/incidents-mine.js`
- `resources/js/pages/pages/incidents-show.js`
- `resources/js/pages/pages/incidents.js`
- `resources/js/pages/pages/login.js`
- `resources/js/pages/pages/operator-dashboard.js`
- `resources/js/pages/pages/profile.js`
- `resources/js/pages/pages/reports.js`
- `resources/js/pages/pages/supervisor-dashboard.js`
- `resources/js/pages/pages/users.js`

## À conserver

Les deux vues suivantes ont actuellement le même contenu, mais leurs noms sont
des points d'entrée distincts du moteur de pagination Laravel. Elles ne sont
pas des copies accidentelles :

- `resources/views/vendor/pagination/simple-tailwind.blade.php`
- `resources/views/vendor/pagination/tailwind.blade.php`

Tous les fichiers actifs servant de contrepartie aux copies auditées sont
également conservés.

## Décision manuelle requise — ne pas supprimer

### Sauvegardes différentes de l'actif

Les 18 sauvegardes suivantes diffèrent toutes de leur fichier actif. Elles
peuvent contenir un état de travail utile et restent donc intactes :

- `resources/css/app.css.bak-before-rollback-tooltip-20260619_184726`
- `resources/css/app.css.bak-tooltip-global-20260619_184457`
- `resources/css/pages/catalogues.css.bak-catalogue-design-20260619_193826`
- `resources/css/pages/dashboard-admin.css.bak-before-rollback-tooltip-20260619_184726`
- `resources/css/pages/dashboard-admin.css.bak-tooltip-20260619_184149`
- `resources/css/pages/incidents-index.css.bak-design-fix-20260619_195431`
- `resources/css/pages/reports.css.bak-20260619_200655`
- `resources/js/app.js.bak-before-rollback-tooltip-20260619_184726`
- `resources/js/app.js.bak-tooltip-global-20260619_184457`
- `resources/js/pages/admin-dashboard.js.bak-before-rollback-tooltip-20260619_184726`
- `resources/js/pages/admin-dashboard.js.bak-tooltip-20260619_184149`
- `resources/js/pages/catalogues.js.bak-catalogue-design-20260619_193826`
- `resources/views/catalogues/index.blade.php.bak-catalogue-design-20260619_193826`
- `resources/views/incidents/index.blade.php.bak-design-fix-20260619_195431`
- `resources/views/incidents/index.blade.php.bak-page-title-20260619_194457`
- `resources/views/incidents/show.blade.php.bak-code-20260619_185534`
- `resources/views/incidents/show.blade.php.bak-parse-error-20260619_185916`
- `resources/views/reports/index.blade.php.bak-20260619_200655`

### Copies imbriquées différentes de l'actif

Ces fichiers ne sont pas référencés, mais leur contenu diffère. Ils restent
intacts jusqu'à une décision explicite :

- `resources/css/css/app.css`
- `resources/css/css/pages/admin-dashboard.css`
- `resources/css/css/pages/catalogues.css`
- `resources/css/css/pages/dashboard-admin.css`
- `resources/css/css/pages/dashboard-operator.css`
- `resources/css/css/pages/dashboard-supervisor.css`
- `resources/css/css/pages/incidents-index.css`
- `resources/css/css/pages/incidents-mine.css`
- `resources/css/css/pages/incidents-show.css`
- `resources/css/css/pages/login.css`
- `resources/css/css/pages/reports.css`
- `resources/css/css/pages/users.css`
- `resources/css/css/theme/variables.css`
- `resources/js/pages/pages/admin-dashboard.js`
- `resources/js/pages/pages/catalogues.js`

### Dossiers vides

Ils ne produisent aucun fichier dans Git, mais leur intention structurelle est
inconnue. Ils ne sont pas supprimés automatiquement :

- `app/Http/Middleware`
- `public/css`
- `resources/views/components/users`
- `resources/views/components/incidents`
- `resources/views/pages/profile`
- `resources/views/pages/system`
- `resources/views/pages/users`
- `resources/views/pages/catalogues/priorites`
- `resources/views/pages/catalogues/causes`
- `resources/views/pages/catalogues/types`
- `resources/views/pages/catalogues/departements`
- `resources/views/pages/catalogues/statuts`
- `resources/views/pages/reports`
- `resources/views/pages/incidents`
- `resources/views/pages/historique`
- `resources/views/auth/passwords`

## Vérification après suppression

Les commandes obligatoires sont :

```bash
git diff --check
php artisan test
npm run build
```

## Résultat d'exécution

Le nettoyage sûr a été exécuté le 20 juillet 2026 :

- les 25 copies CSS strictement identiques listées ci-dessus ont été
  supprimées ;
- les 13 copies JavaScript strictement identiques listées ci-dessus ont été
  supprimées ;
- les artefacts non suivis `NUL`, `[^` et `]*` ont été supprimés ;
- les 18 sauvegardes, les 15 copies imbriquées divergentes et tous les
  dossiers vides sont restés intacts.

Contrôles après nettoyage :

- `git diff --check` : réussi (uniquement des avertissements informatifs de
  normalisation CRLF vers LF) ;
- `php artisan test` : 125 tests réussis, 583 assertions ;
- `npm run build` : réussi avec Vite 6.4.2 (36 modules transformés).

Vite signale encore quatre chunks JavaScript vides. Le build reste valide et
ces avertissements ne résultent pas du nettoyage de cette phase.

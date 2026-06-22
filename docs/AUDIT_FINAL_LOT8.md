# Audit final Lot 8 — Migration front CEET

## Résultat attendu

Les lots 1 à 7 ont migré les écrans principaux vers une structure commune :

- layout applicatif unique `resources/views/layouts/app.blade.php` ;
- sidebar unique `resources/views/components/app-sidebar.blade.php` ;
- topbar unique `resources/views/components/app-topbar.blade.php` ;
- pages métier sous `resources/views/pages/*` ;
- CSS par layout/composant/page ;
- JavaScript noyau et JavaScript par page ;
- validations Laravel conservées.

## Nettoyage Lot 8

Le Lot 8 supprime les anciennes vues remplacées par les pages `resources/views/pages/*` :

- anciens dashboards racine ;
- anciennes vues incidents, sauf `resources/views/incidents/vue-console.blade.php` ;
- anciennes vues rapports ;
- anciennes vues utilisateurs ;
- ancienne vue profil ;
- anciennes vues catalogues ;
- ancienne vue historique ;
- ancienne vue système.

## Fichiers volontairement conservés

- `resources/views/incidents/vue-console.blade.php` : encore utilisée par `VueConsoleController`.
- `resources/views/exports/incidents-pdf.blade.php` : encore utilisée par l’export PDF incidents API.
- `resources/css/pages/admin-dashboard.css` et `resources/js/pages/admin-dashboard.js` : encore utilisés par la vue console legacy.
- `resources/views/partials/ceet-role-nav.blade.php` : encore utilisé par la vue console legacy.

## Validation attendue

Après application :

```bash
php artisan optimize:clear
php artisan view:clear
npm run build
php artisan test
```

Le résultat attendu est : `Tests: 99 passed`.

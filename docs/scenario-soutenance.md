# Scénario de soutenance — démonstration de bout en bout

Durée cible : **14 min 20 s**, avec une marge de 40 secondes pour rester entre
12 et 15 minutes. Toute la démonstration suit un seul incident, de sa création
à sa clôture.

## Prérequis avant la démonstration

- Ordinateur branché au secteur, chargeur accessible et souris fonctionnelle.
- Docker Desktop lancé et WSL opérationnel.
- Dans un terminal WSL ouvert à la racine du projet, lancer :

  ```bash
  bash scripts/start-presentation.sh
  ```

- Ouvrir l'application à l'adresse <http://127.0.0.1:8000>.
- Vérifier MySQL avec `docker compose ps mysql` : son état doit être `healthy`.
- Vérifier que les comptes Administrateur, Superviseur et Opérateur préparés
  localement permettent chacun une connexion. Ne conserver leurs identifiants
  que dans le support local prévu à cet effet, hors de ce document.
- Faire un essai hors ligne en coupant le Wi-Fi avant le passage, puis garder
  Internet coupé si aucune autre ressource n'en dépend.
- Désactiver les notifications système, les messageries et les mises à jour.
- Fermer tous les onglets et toutes les applications inutiles.
- Identifier dans la liste un incident de secours déjà affecté à l'Opérateur,
  puis noter uniquement son code sur papier.

## Jeu de données conseillé

Choisir les valeurs ci-dessous si elles sont disponibles dans les catalogues ;
sinon prendre l'équivalent actif le plus proche.

| Champ | Valeur conseillée |
|---|---|
| Titre | Coupure électrique au poste Adamavo |
| Type d'incident | Disjonction Franche |
| Cause | Câble pioché |
| Département | Adamavo |
| Localisation | Poste Adamavo, départ principal |
| Priorité | Haute |
| Description courte | Coupure constatée sur le départ principal, plusieurs clients privés d'alimentation. |
| Opérateur cible | Operateur A |
| Texte de prise en charge et d'intervention | Équipe terrain mobilisée, zone sécurisée et diagnostic lancé. |
| Actions de résolution | Câble isolé, raccordement sécurisé et réalimentation progressive effectuée. |
| Résultat de résolution | Alimentation rétablie et tension stabilisée sur le départ. |
| Résumé du rapport | Incident traité avec succès ; réseau rétabli et surveillance recommandée pendant 24 heures. |

## Parcours chronométré

### 1. Connexion Superviseur — 35 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** Connexion, `/login`.
- **Action exacte :** saisir les identifiants locaux du compte Superviseur et
  sélectionner **Se connecter**.
- **Données à saisir :** identifiant préparé localement ; aucune donnée
  d'authentification n'est reproduite ici.
- **Résultat attendu :** redirection vers `/dashboard` avec le menu
  Superviseur.
- **Phrase au jury :** « Je commence avec le rôle qui pilote le traitement des incidents. »
- **Plan B :** utiliser la session Superviseur déjà ouverte dans l'onglet de
  secours, sans modifier le compte.

### 2. Présentation rapide du dashboard — 40 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** Tableau de bord, `/dashboard` (`dashboard`).
- **Action exacte :** montrer les indicateurs, les incidents ouverts et les
  accès rapides, sans changer les filtres.
- **Données à saisir :** aucune.
- **Résultat attendu :** indicateurs et liste d'activité visibles.
- **Phrase au jury :** « Cette vue donne au superviseur une synthèse immédiate de l'état du réseau. »
- **Plan B :** si un indicateur tarde, présenter la liste déjà chargée puis
  poursuivre vers la création.

### 3. Création d'un incident — 1 min 20 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** **Déclarer un incident**, `/incidents/create`
  (`incidents.create`).
- **Action exacte :** remplir le formulaire avec le jeu conseillé, laisser la
  date de début proposée, puis sélectionner **Créer l'incident**.
- **Données à saisir :** titre, département Adamavo, localisation, description,
  type Disjonction Franche, cause Câble pioché, priorité Haute ; ne pas choisir
  d'opérateur à cette étape afin de montrer l'affectation séparément.
- **Résultat attendu :** création avec un code unique et statut **OUVERT**, puis
  affichage de la fiche ou de la liste.
- **Phrase au jury :** « Le formulaire standardise les informations techniques dès la déclaration. »
- **Plan B :** ouvrir l'incident de secours et annoncer que sa création a été
  préparée pour préserver le temps de démonstration.

### 4. Affectation à un opérateur — 45 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** liste ou fiche de l'incident (`incidents.index` ou
  `incidents.show`).
- **Action exacte :** sélectionner **Operateur A** dans l'action d'affectation
  et confirmer.
- **Données à saisir :** opérateur cible : Operateur A.
- **Résultat attendu :** responsable renseigné, statut **AFFECTE** et
  notification créée pour l'opérateur.
- **Phrase au jury :** « L'affectation rend la responsabilité opérationnelle traçable. »
- **Plan B :** ouvrir l'incident de secours déjà affecté au même opérateur.

### 5. Déconnexion — 15 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** menu du profil.
- **Action exacte :** sélectionner **Déconnexion**.
- **Données à saisir :** aucune.
- **Résultat attendu :** retour à `/login`.
- **Phrase au jury :** « Je change maintenant de rôle pour reproduire le travail terrain. »
- **Plan B :** ouvrir `/logout` uniquement via le bouton prévu dans l'interface ;
  si la page tarde, utiliser l'onglet Opérateur préparé.

### 6. Connexion Opérateur — 35 s

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** Connexion, `/login`.
- **Action exacte :** se connecter avec le compte local Operateur A.
- **Données à saisir :** identifiant local préparé hors document.
- **Résultat attendu :** dashboard Opérateur avec menus limités à ses droits.
- **Phrase au jury :** « L'opérateur ne voit que les fonctions nécessaires à son intervention. »
- **Plan B :** reprendre la session Opérateur de secours déjà authentifiée.

### 7. Consultation de la notification — 35 s

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** **Notifications**, `/notifications`
  (`notifications.index`).
- **Action exacte :** ouvrir la notification d'affectation et la marquer comme
  lue si nécessaire.
- **Données à saisir :** aucune.
- **Résultat attendu :** notification liée au code du nouvel incident et accès
  à sa fiche.
- **Phrase au jury :** « L'affectation est immédiatement visible, même sans serveur temps réel. »
- **Plan B :** ouvrir **Mes incidents** (`incidents.mine`) et rechercher le code ;
  le workflow ne dépend pas de Reverb.

### 8. Prise en charge de l'incident — 40 s

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** fiche incident, `/incidents/{incident}`
  (`incidents.show`).
- **Action exacte :** dans **Prise en charge**, saisir la description conseillée
  puis préparer la validation.
- **Données à saisir :** « Équipe terrain mobilisée, zone sécurisée et diagnostic lancé. »
- **Résultat attendu :** formulaire accepté pour l'incident affecté à cet
  opérateur.
- **Phrase au jury :** « La prise en charge horodate le début réel du traitement. »
- **Plan B :** utiliser l'incident de secours affecté et encore au statut
  **AFFECTE**.

### 9. Ajout d'une intervention — 20 s

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** même fiche incident.
- **Action exacte :** valider **Démarrer la prise en charge** ; cette action
  appelle `incidents.take` et enregistre l'intervention de prise en charge.
- **Données à saisir :** reprendre le texte préparé à l'étape 8.
- **Résultat attendu :** statut **EN COURS** et nouvelle ligne dans l'historique
  des interventions.
- **Phrase au jury :** « Une seule action métier met à jour le statut et ajoute la trace d'intervention. »
- **Plan B :** montrer la ligne d'intervention de l'incident de secours ; ne pas
  envoyer de requête technique manuelle pendant le passage.

### 10. Résolution de l'incident — 55 s

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** même fiche, bloc **Résoudre l'incident**.
- **Action exacte :** renseigner les actions, le résultat et le résumé, puis
  valider la résolution.
- **Données à saisir :** actions et résultat du jeu conseillé ; résumé :
  « Incident traité, alimentation rétablie et tension stabilisée. »
- **Résultat attendu :** statut **RESOLU** et fin d'intervention horodatée.
- **Phrase au jury :** « La résolution conserve les actions réalisées et le résultat technique. »
- **Plan B :** poursuivre avec un incident de secours déjà résolu mais sans
  rapport soumis.

### 11. Soumission du rapport — 1 min

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** même fiche, bloc **Rapport d'intervention**.
- **Action exacte :** compléter les actions réalisées, le résultat et les
  observations, puis sélectionner **Soumettre le rapport au superviseur**.
- **Données à saisir :** reprendre les textes de résolution ; observations :
  « Surveillance recommandée pendant 24 heures. »
- **Résultat attendu :** rapport au statut **SOUMIS** et incident au statut
  **RAPPORTE**.
- **Phrase au jury :** « Le rapport formalise le retour terrain avant toute clôture. »
- **Plan B :** utiliser un incident de secours dont le rapport est déjà soumis.

### 12. Déconnexion — 15 s

- **Rôle utilisé :** Opérateur.
- **Page à ouvrir :** menu du profil.
- **Action exacte :** sélectionner **Déconnexion**.
- **Données à saisir :** aucune.
- **Résultat attendu :** retour à la connexion.
- **Phrase au jury :** « Le contrôle revient au superviseur pour validation. »
- **Plan B :** basculer vers l'onglet Superviseur préparé.

### 13. Connexion Superviseur — 35 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** `/login`, puis `/dashboard`.
- **Action exacte :** se reconnecter avec le compte Superviseur.
- **Données à saisir :** identifiant local préparé hors document.
- **Résultat attendu :** retour au dashboard Superviseur.
- **Phrase au jury :** « Le superviseur reprend le dossier soumis par le terrain. »
- **Plan B :** utiliser la session Superviseur de secours.

### 14. Validation du rapport — 40 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** fiche du même incident (`incidents.show`).
- **Action exacte :** contrôler le rapport puis sélectionner **Valider le
  rapport** (`incidents.report.validate`).
- **Données à saisir :** aucune.
- **Résultat attendu :** rapport **VALIDE** et incident **VALIDE** ; l'action de
  clôture devient disponible.
- **Phrase au jury :** « La clôture est bloquée tant que le rapport n'a pas été validé. »
- **Plan B :** ouvrir un incident de secours avec rapport soumis ; si nécessaire,
  montrer sans exécuter les boutons Valider et Refuser.

### 15. Clôture de l'incident — 45 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** même fiche, bloc **Clôturer l'incident**.
- **Action exacte :** conserver la date proposée, vérifier le résumé puis
  confirmer la clôture (`incidents.close`).
- **Données à saisir :** résumé final du jeu conseillé.
- **Résultat attendu :** statut final **CLOTURE**, date de fin et durée
  calculées.
- **Phrase au jury :** « La clôture termine le cycle tout en conservant sa traçabilité. »
- **Plan B :** montrer un incident de secours déjà clôturé et sa chronologie.

### 16. Consultation de l'historique — 50 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** **Historique**, `/historique`
  (`historique.index`).
- **Action exacte :** rechercher le code de l'incident et montrer création,
  affectation, prise en charge, résolution, rapport, validation et clôture.
- **Données à saisir :** code de l'incident dans le filtre de recherche.
- **Résultat attendu :** chronologie complète et ordonnée.
- **Phrase au jury :** « Chaque transition importante est auditée avec son auteur et sa date. »
- **Plan B :** retirer le filtre et montrer les dernières lignes de l'historique.

### 17. Export PDF ou Excel — 55 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** **Rapports**, `/reports` (`reports.index`).
- **Action exacte :** choisir le mois courant puis lancer **Exporter Excel**
  (`reports.monthly`) ; l'export PDF mensuel est l'alternative immédiate.
- **Données à saisir :** mois courant et filtres par défaut.
- **Résultat attendu :** téléchargement d'un fichier Excel valide contenant les
  données du mois.
- **Phrase au jury :** « Les données opérationnelles sont directement exploitables pour le reporting. »
- **Plan B :** lancer l'export PDF ; si le téléchargement est bloqué, ouvrir un
  export testé et préparé localement avant le passage.

### 18. Déconnexion — 15 s

- **Rôle utilisé :** Superviseur.
- **Page à ouvrir :** menu du profil.
- **Action exacte :** sélectionner **Déconnexion**.
- **Données à saisir :** aucune.
- **Résultat attendu :** retour à `/login`.
- **Phrase au jury :** « Je termine avec les fonctions de gouvernance de l'administrateur. »
- **Plan B :** basculer vers la session Administrateur préparée.

### 19. Connexion Administrateur — 35 s

- **Rôle utilisé :** Administrateur.
- **Page à ouvrir :** `/login`.
- **Action exacte :** se connecter avec le compte Administrateur local.
- **Données à saisir :** identifiant local préparé hors document.
- **Résultat attendu :** dashboard Administrateur et menu complet.
- **Phrase au jury :** « L'administrateur supervise la configuration et les accès. »
- **Plan B :** utiliser la session Administrateur de secours.

### 20. Présentation des utilisateurs — 35 s

- **Rôle utilisé :** Administrateur.
- **Page à ouvrir :** **Utilisateurs**, `/users` (`users.index`).
- **Action exacte :** montrer la recherche, les rôles et l'état actif sans
  modifier ni supprimer de compte.
- **Données à saisir :** éventuellement « Operateur A » dans la recherche.
- **Résultat attendu :** liste filtrable avec rôles et états.
- **Phrase au jury :** « La gestion centralisée des utilisateurs applique une séparation claire des rôles. »
- **Plan B :** laisser la liste complète affichée et expliquer les trois profils.

### 21. Présentation des catalogues — 35 s

- **Rôle utilisé :** Administrateur.
- **Page à ouvrir :** **Catalogues**, `/catalogues`
  (`catalogues.index`).
- **Action exacte :** montrer départements, types, causes, priorités et statuts,
  sans créer ni modifier d'entrée.
- **Données à saisir :** aucune.
- **Résultat attendu :** référentiels métier disponibles et cohérents avec le
  formulaire d'incident.
- **Phrase au jury :** « Les catalogues évitent les saisies libres et fiabilisent les statistiques. »
- **Plan B :** ouvrir directement la liste des types ou des priorités.

### 22. Présentation du statut système — 40 s

- **Rôle utilisé :** Administrateur.
- **Page à ouvrir :** **Statut système**, `/system/status`
  (`system.status`).
- **Action exacte :** montrer l'état des services, du stockage et les
  informations de diagnostic, sans déclencher d'action destructive.
- **Données à saisir :** aucune.
- **Résultat attendu :** page accessible avec les indicateurs locaux.
- **Phrase au jury :** « Cette vue permet de contrôler rapidement la disponibilité technique de la plateforme. »
- **Plan B :** utiliser `docker compose ps mysql` dans le terminal déjà ouvert et
  rappeler que l'application vient d'exécuter tout le workflow.

## Plan B général

- **Laravel ne démarre pas :** vérifier que le port 8000 est libre, fermer
  l'ancien processus, puis relancer `bash scripts/start-presentation.sh`. Ne
  lancer aucune migration destructive.
- **MySQL ne répond pas :** exécuter `docker compose ps mysql`, puis
  `docker compose up -d --wait mysql`. Consulter `docker compose logs mysql` si
  l'état n'est pas `healthy`.
- **Un compte ne fonctionne pas :** utiliser la session de secours du même rôle
  déjà testée ; ne pas modifier le compte pendant la présentation.
- **L'export échoue :** essayer l'autre format, puis montrer le fichier de
  secours exporté lors de la répétition.
- **Une page est lente :** attendre quelques secondes sans recliquer, puis
  utiliser l'accès direct du menu ou l'onglet de secours.
- **Reverb est indisponible :** continuer normalement. Le mode présentation
  utilise `BROADCAST_CONNECTION=log` ; notifications et workflow persistent en
  base et restent consultables après actualisation.
- **Internet est coupé :** ne rien changer. Les assets critiques sont locaux et
  l'application de présentation est conçue pour fonctionner hors ligne.

## Checklist 10 minutes avant le passage

- [ ] Docker Desktop lancé.
- [ ] MySQL `healthy`.
- [ ] Script de présentation lancé.
- [ ] Application accessible sur <http://127.0.0.1:8000>.
- [ ] Trois comptes testés sans les modifier.
- [ ] Navigateur en plein écran.
- [ ] Zoom réglé pour garder tableaux et boutons visibles.
- [ ] Cache navigateur vidé si nécessaire, puis application retestée.
- [ ] Incident de secours identifié par son code.
- [ ] Exports PDF et Excel testés ; un exemplaire de secours disponible.
- [ ] Wi-Fi coupé ou connexion maîtrisée.
- [ ] Téléphone en silencieux et notifications système désactivées.
- [ ] Onglets inutiles fermés ; trois sessions de secours prêtes si possible.

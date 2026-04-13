# Guide utilisateur

## 1. Présentation de l'application
L’application de gestion des incidents CEET centralise la déclaration, le suivi, l’analyse et l’historique des incidents du réseau électrique. Elle permet aux équipes techniques et d’encadrement de suivre rapidement l’état du réseau et de documenter chaque action.

| Rôle | Accès | Description |
| --- | --- | --- |
| Administrateur | Tous les modules | Paramètre l’application, gère les utilisateurs, les catalogues et l’audit |
| Superviseur | Incidents, rapports, historique, catalogues selon permissions | Suit les incidents, contrôle les affectations et valide les résolutions |
| Opérateur Terrain | Incidents, incidents en cours, dashboard | Déclare les incidents, met à jour les actions menées et suit les affectations |

## 2. Connexion et gestion du profil
1. Accédez à la page de connexion.
2. Renseignez votre adresse e-mail et votre mot de passe.
3. Après connexion, utilisez le menu `Réglages` pour mettre à jour votre profil, votre mot de passe et vos informations personnelles.

## 3. Déclarer un incident
1. Ouvrez `Incidents` puis cliquez sur `Déclarer un incident`.
2. Renseignez le titre et sélectionnez le départ concerné.
3. Le code incident est généré automatiquement à l’enregistrement.
4. Sélectionnez le type d’incident.
5. La liste des causes est filtrée automatiquement selon le type choisi.
6. Indiquez la localisation, le statut initial et la priorité.
7. Saisissez la date de début, puis la date de fin si l’incident est déjà résolu.
8. Renseignez les actions menées, les intervenants (responsable, superviseur) et le résumé de résolution si disponible.
9. Validez le formulaire pour créer l’incident.

## 4. Suivre un incident
1. La page `Incidents` affiche la liste complète avec des filtres par statut, départ, type, cause, priorité, opérateur et dates.
2. Le champ de recherche permet de retrouver un incident par code ou par titre.
3. Les badges de statut distinguent les incidents ouverts et clôturés.
4. La fiche détail affiche la chronologie, les intervenants et l’historique des actions.

## 5. Page “Incidents en cours”
1. Le menu `Incidents en cours` affiche uniquement les incidents non résolus.
2. Les lignes sont triées par criticité puis par ancienneté.
3. Les KPI en haut indiquent :
   - le total des incidents ouverts ;
   - le nombre d’incidents critiques ;
   - la durée d’attente du plus ancien incident.
4. La colonne `Durée attente` change de couleur pour attirer l’attention :
   - vert : surveillance normale ;
   - orange : vigilance ;
   - rouge : dépassement important.
5. Le bouton `Prendre en charge` ouvre directement l’écran de mise à jour.

## 6. Tableau de bord et lecture des KPI
1. Le dashboard présente les incidents ouverts, les résolutions du jour, le temps moyen de rétablissement et la disponibilité réseau.
2. Le graphique `Évolution des incidents` montre la tendance sur les 30 derniers jours.
3. `Top départs` aide à repérer les zones les plus sollicitées.
4. Les donuts de statut et de priorité permettent d’identifier rapidement la répartition des incidents.
5. La disponibilité réseau traduit le niveau global de service observé.

## 7. Générer un rapport
1. Ouvrez `Rapports et analyse`.
2. Choisissez la période mensuelle, le départ et la cause si nécessaire.
3. Le rapport présente l’évolution mensuelle, la durée moyenne, la répartition par type, les causes et les départs critiques.
4. Utilisez `Générer PDF` pour un document de diffusion.
5. Utilisez `Exporter Excel` pour un traitement bureautique ou une analyse complémentaire.

## 8. Gérer les catalogues
1. Le menu `Catalogue` donne accès aux départs, types d’incidents, causes, statuts et priorités.
2. Les modifications sont réservées aux profils autorisés.
3. Pour un départ, vous pouvez renseigner le poste de répartition, le transformateur, l’arrivée et la charge maximale.

## 9. Consulter l’historique et les logs d’audit
1. Le module `Audit` est destiné aux profils d’encadrement autorisés.
2. Il retrace les créations, modifications, suppressions et actions importantes sur les incidents.
3. Cette traçabilité complète les actions visibles directement sur la fiche incident.

## 10. Gérer les utilisateurs
1. La gestion des utilisateurs est réservée à l’Administrateur.
2. Elle permet de créer des comptes, d’attribuer les rôles et d’activer ou désactiver les accès.
3. Chaque rôle ouvre des permissions adaptées au profil métier.

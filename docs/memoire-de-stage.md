# Développement d'une Application de Gestion des Incidents du Réseau Électrique de la CEET

## Page de garde

![Logo CEET](../public/images/logo-ceet.png)

**Mémoire de stage de fin de cycle / rapport de stage académique**  

**Titre :** Développement d'une Application de Gestion des Incidents du Réseau Électrique de la CEET  
**Stagiaire :** EKOM Koffi  
**Maître de stage :** EDOH Simon, Responsable Suivi, Cellule de Transformation Digitale (CTD)  
**Organisme d'accueil :** CEET — Compagnie Énergie Électrique du Togo  
**Service d'accueil :** Cellule de Transformation Digitale (CTD)  
**Période de stage :** du 16 mars 2026 au 15 mai 2026  
**Établissement :** [À COMPLÉTER — indiquer le nom complet de l'école, institut ou université]  
**Filière :** [À COMPLÉTER — indiquer la spécialité, par exemple Informatique de Gestion, Génie Logiciel, Réseaux et Systèmes, etc.]  
**Année académique :** 2025-2026  

---

## Remerciements

Je tiens à exprimer ma profonde gratitude à l'ensemble des personnes qui ont contribué, de près ou de loin, à la réussite de ce stage effectué au sein de la **Compagnie Énergie Électrique du Togo (CEET)**.

Mes remerciements s'adressent tout particulièrement à **Monsieur EDOH Simon**, Responsable Suivi au sein de la **Cellule de Transformation Digitale (CTD)**, pour son encadrement, sa disponibilité, ses conseils techniques et sa confiance tout au long de cette période de stage. Son accompagnement a joué un rôle déterminant dans la compréhension des enjeux opérationnels de la gestion des incidents du réseau électrique et dans la structuration de la solution développée.

J'adresse également mes sincères remerciements à l'ensemble du personnel de la CEET, et plus particulièrement aux collaborateurs de la CTD, pour l'accueil chaleureux, l'esprit d'ouverture et le partage d'expérience dont j'ai bénéficié. Le cadre professionnel offert m'a permis d'approfondir mes compétences techniques, mais aussi de mieux appréhender les réalités du fonctionnement d'une entreprise publique stratégique.

Je remercie par ailleurs mes enseignants et responsables académiques pour la formation reçue, qui a constitué la base méthodologique et technique indispensable à la conduite de ce projet.

Enfin, j'exprime ma reconnaissance à ma famille, à mes proches et à toutes les personnes qui m'ont soutenu moralement et intellectuellement durant cette période.

**[À PERSONNALISER]** Le stagiaire peut compléter cette section avec les noms de personnes ayant joué un rôle particulier dans l'accès aux données, les tests métier, l'accompagnement institutionnel ou le suivi académique.

---

## Résumé

Ce mémoire présente le développement d'une application web de gestion des incidents du réseau électrique de la CEET, réalisée dans le cadre d'un stage de deux mois au sein de la Cellule de Transformation Digitale. Le projet répond au besoin de centraliser la déclaration, le suivi, la traçabilité et l'analyse des incidents affectant les départs du réseau. Avant la mise en place de cet outil, la gestion des incidents reposait sur des mécanismes fragmentés, limitant la visibilité opérationnelle, la rapidité de traitement et l'exploitation statistique des données. La solution développée s'appuie sur Laravel 12, PHP 8.2+, MySQL 8.4, Blade, TailwindCSS, Laravel Reverb, DomPDF et Maatwebsite Excel. Elle intègre un CRUD complet des incidents, des catalogues métier, un dashboard statistique, des rapports PDF et Excel, un suivi en temps réel et un système de traçabilité. Les résultats obtenus montrent une amélioration de la structuration des informations, du pilotage des incidents et de la capitalisation des historiques.

## Abstract

This report presents the development of a web-based incident management application for the CEET electrical distribution network, carried out during a two-month internship within the Digital Transformation Unit. The project addresses the need to centralize incident reporting, monitoring, traceability, and analytics related to network feeders. Before this solution, incident handling relied on fragmented and mostly manual practices, reducing operational visibility, responsiveness, and statistical exploitation of data. The implemented solution is built with Laravel 12, PHP 8.2+, MySQL 8.4, Blade, TailwindCSS, Laravel Reverb, DomPDF, and Maatwebsite Excel. It provides a complete incident CRUD module, business catalog management, statistical dashboards, PDF and Excel reporting, real-time updates, and audit traceability. The outcomes highlight better information structuring, improved operational supervision, and stronger historical capitalization. The project also opens perspectives for future extensions such as notifications, mobile access, predictive analytics, and integration with advanced electrical supervision tools.

---

## Table des matières

1. Introduction générale  
2. Liste des abréviations  
3. Chapitre 1 — Présentation de l'organisme d'accueil  
   3.1 La CEET  
   3.2 La Cellule de Transformation Digitale (CTD)  
   3.3 Organisation du réseau électrique  
   3.4 Déroulement du stage  
4. Chapitre 2 — Analyse de l'existant et cahier des charges  
   4.1 État des lieux  
   4.2 Problèmes identifiés  
   4.3 Besoins fonctionnels et non-fonctionnels  
   4.4 Cas d'utilisation  
   4.5 Contraintes techniques et organisationnelles  
5. Chapitre 3 — Conception  
   5.1 Choix technologiques et justification  
   5.2 Architecture de l'application  
   5.3 Modèle de données  
   5.4 Maquettes des interfaces principales  
   5.5 Workflows métier  
6. Chapitre 4 — Réalisation et implémentation  
   6.1 Environnement de développement  
   6.2 Mise en place du projet  
   6.3 Implémentation des modules  
   6.4 Tests et validation  
   6.5 Difficultés rencontrées et solutions apportées  
7. Chapitre 5 — Résultats et perspectives  
   7.1 Fonctionnalités livrées  
   7.2 Captures d'écran commentées  
   7.3 Performances et retours utilisateurs  
   7.4 Limites de l'application  
   7.5 Perspectives d'évolution  
8. Conclusion générale  
9. Bibliographie et webographie  
10. Annexes  

---

## Liste des abréviations

| Abréviation | Signification |
| --- | --- |
| CEET | Compagnie Énergie Électrique du Togo |
| CTD | Cellule de Transformation Digitale |
| CDC | Cahier des charges |
| CRUD | Create, Read, Update, Delete |
| ERD | Entity Relationship Diagram |
| KPI | Key Performance Indicator |
| API | Application Programming Interface |
| MVC | Model View Controller |
| ORM | Object Relational Mapping |
| JWT | JSON Web Token |
| SQL | Structured Query Language |
| DARR | Déclenchement Automatique Régulé Réseau |
| DARL | Déclenchement Automatique Régulé Ligne |
| MT | Manque de tension |
| BDD | Base de données |
| PDF | Portable Document Format |
| CSV | Comma-Separated Values |
| XLSX | Format Excel Open XML |

---

## Introduction générale

La transformation numérique constitue aujourd'hui un levier essentiel de modernisation des organisations publiques et privées. Dans le secteur de l'énergie, cette transformation prend une importance particulière en raison de la nécessité d'assurer une continuité de service, une réactivité opérationnelle élevée et une capacité d'analyse fiable des incidents affectant le réseau. Pour une société de distribution d'électricité comme la **Compagnie Énergie Électrique du Togo (CEET)**, la gestion des incidents représente donc un enjeu à la fois technique, organisationnel et stratégique.

Dans le cadre de ses activités, la CEET doit faire face à différents types d'incidents susceptibles d'affecter les départs du réseau : disjonctions franches, manques de tension, défauts de cellule, surcharges, vandalisme, chutes d'arbres, ruptures de conducteurs, entre autres. La qualité de la prise en charge de ces incidents dépend fortement de la rapidité de détection, de la bonne circulation de l'information entre les acteurs, de la qualité de la traçabilité et de la disponibilité d'outils d'aide à la décision.

Avant la mise en place de la solution étudiée dans ce mémoire, les informations relatives aux incidents étaient souvent dispersées ou traitées selon des mécanismes insuffisamment centralisés. Une telle situation peut entraîner des difficultés de suivi, une faible visibilité sur l'état du réseau, une complexité dans l'élaboration des rapports et une exploitation statistique limitée. Le besoin d'un outil de gestion centralisé, structuré et évolutif s'est donc imposé naturellement.

Le présent stage, effectué du **16 mars 2026 au 15 mai 2026** au sein de la **Cellule de Transformation Digitale (CTD)** de la CEET, avait pour objectif principal de **concevoir et développer une application web de gestion des incidents du réseau électrique**. Cette application devait permettre de déclarer les incidents, de suivre leur cycle de vie, de gérer des catalogues métier, de générer des rapports, de fournir un tableau de bord d'analyse et d'assurer une traçabilité complète des actions effectuées.

Sur le plan technique, le projet s'appuie sur une architecture moderne construite autour de **Laravel 12**, **PHP 8.2+**, **MySQL 8.4**, **Blade**, **TailwindCSS**, **Laravel Reverb**, **DomPDF** et **Maatwebsite Excel**. Le choix de cette stack répond à des impératifs de productivité, de maintenabilité, de rapidité de développement et de compatibilité avec les besoins du projet.

Ce mémoire a pour objectif de présenter la démarche suivie, les choix de conception retenus, l'implémentation réalisée, les résultats obtenus ainsi que les perspectives d'évolution de l'application. Il est structuré en cinq chapitres principaux. Le premier présente l'organisme d'accueil et le contexte du stage. Le deuxième expose l'analyse de l'existant et le cahier des charges. Le troisième décrit la conception de la solution. Le quatrième détaille la réalisation technique et les modules implémentés. Enfin, le cinquième propose une synthèse des résultats, des limites et des perspectives.

---

# Chapitre 1 — Présentation de l'organisme d'accueil

## 1.1 La CEET : histoire, mission et place dans le réseau togolais

La **Compagnie Énergie Électrique du Togo (CEET)** est l'entreprise publique chargée de la distribution de l'énergie électrique sur le territoire togolais. Elle occupe une position stratégique dans le fonctionnement économique et social du pays, car l'électricité constitue une ressource indispensable au développement des activités industrielles, administratives, commerciales et domestiques.

La mission de la CEET peut être résumée autour de plusieurs axes :

- assurer la distribution de l'énergie électrique aux usagers ;
- maintenir et exploiter les infrastructures de distribution ;
- garantir une continuité de service compatible avec les exigences de sécurité et de disponibilité ;
- moderniser progressivement les outils de supervision, de gestion et de relation avec les utilisateurs internes ou externes.

Dans un contexte marqué par l'évolution des besoins énergétiques, l'accroissement des zones urbaines et périurbaines, et l'exigence croissante de fiabilité, la CEET doit renforcer ses mécanismes de pilotage opérationnel. La gestion rigoureuse des incidents représente à ce titre un facteur déterminant pour améliorer la réactivité des équipes techniques et optimiser la qualité de service.

Le réseau électrique exploité par la CEET s'appuie notamment sur :

- des postes sources ;
- des postes de répartition ;
- des transformateurs ;
- des départs alimentant différentes zones géographiques ;
- des structures de maintenance et de supervision réparties selon les réalités du territoire.

Cette organisation implique une circulation continue d'informations techniques entre les équipes de terrain, les responsables d'intervention, les superviseurs et les responsables de pilotage. Un système d'information adapté devient alors indispensable pour réduire la dépendance aux mécanismes manuels.

## 1.2 La Cellule de Transformation Digitale (CTD)

La **Cellule de Transformation Digitale (CTD)** constitue au sein de la CEET un espace structurant pour la modernisation des processus internes. Sa mission est d'identifier les besoins numériques de l'entreprise, de proposer des solutions adaptées, d'accompagner leur mise en œuvre et de participer à l'amélioration continue des outils déjà existants.

Les responsabilités de la CTD peuvent être regroupées autour des fonctions suivantes :

- analyse des besoins métiers ;
- conception de solutions numériques internes ;
- accompagnement à la digitalisation des processus ;
- amélioration de la traçabilité et de la circulation de l'information ;
- support à la conduite du changement.

Dans le cadre du présent stage, la CTD a joué un rôle central en fournissant :

- le cadre institutionnel du projet ;
- l'expression du besoin métier ;
- l'encadrement technique et fonctionnel ;
- les éléments de validation relatifs aux écrans, workflows et règles de gestion.

L'intégration dans cette cellule a permis d'observer de manière concrète la manière dont une équipe de transformation numérique intervient pour traduire une problématique opérationnelle en solution logicielle.

## 1.3 Organisation du réseau électrique : postes, départs et zones

L'application développée porte spécifiquement sur la gestion des incidents affectant les **départs** du réseau électrique. Dans ce contexte, un départ peut être compris comme une ligne ou une branche du réseau alimentée depuis un poste donné et destinée à desservir une zone géographique, industrielle ou résidentielle.

Le réseau manipulé dans le projet présente plusieurs caractéristiques structurantes :

- les départs sont identifiés par un **code**, un **nom**, une **zone**, un **poste de répartition** et éventuellement un **transformateur** ou une **arrivée** ;
- certains départs alimentent des zones urbaines majeures, d'autres des zones industrielles ou régionales ;
- la charge maximale de chaque départ constitue une information utile pour les analyses techniques et futures extensions de supervision ;
- les incidents doivent pouvoir être corrélés à ces départs afin de produire des statistiques pertinentes.

Le fait d'intégrer dans l'application un catalogue enrichi de départs CEET avec des champs comme `poste_repartition`, `transformateur`, `arrivee` et `charge_maximale` répond donc à une logique métier forte : permettre une meilleure contextualisation des incidents et préparer les évolutions vers des analyses plus fines.

## 1.4 Déroulement du stage

**[À COMPLÉTER]**  
Cette section doit présenter de manière chronologique le déroulement concret du stage. Il est conseillé d'y inclure :

- les conditions d'accueil et d'intégration dans l'équipe ;
- le planning général sur les deux mois ;
- les grandes phases du travail ;
- les réunions de cadrage, de validation et de restitution ;
- les principales tâches hebdomadaires ;
- les méthodes de suivi utilisées.

**Exemple de structure possible :**

1. **Semaine 1** : prise de connaissance de l'environnement CEET, compréhension du besoin, lecture du cahier des charges, installation du projet.
2. **Semaines 2 à 3** : modélisation des données, mise en place du socle Laravel, migrations, seeders, authentification.
3. **Semaines 4 à 5** : développement du module incidents, catalogues et traçabilité.
4. **Semaines 6 à 7** : dashboard, rapports, export PDF/Excel, temps réel.
5. **Semaine 8** : tests, corrections, documentation et finalisation.

---

# Chapitre 2 — Analyse de l'existant et cahier des charges

## 2.1 État des lieux

Avant la réalisation de l'application, la gestion des incidents du réseau électrique présentait les limites classiques des processus encore partiellement manuels ou insuffisamment intégrés. Les informations utiles à la déclaration, au suivi et à la clôture d'un incident pouvaient être dispersées entre plusieurs supports, ce qui engendrait des difficultés de consolidation.

Dans un tel contexte, plusieurs problèmes se posent généralement :

- difficulté à centraliser l'information au même endroit ;
- absence de standardisation des statuts, priorités et causes ;
- manque de visibilité globale sur les incidents ouverts ;
- complexité pour produire des rapports fiables dans des délais courts ;
- traçabilité insuffisante des modifications et décisions ;
- faible exploitation statistique des incidents sur une période donnée.

Le besoin métier exprimé visait donc la mise en place d'un **outil centralisé**, **sécurisé**, **structuré** et **orienté supervision**.

## 2.2 Problèmes identifiés

À partir du cahier des charges, les problèmes principaux peuvent être regroupés comme suit.

### a. Absence d'outil centralisé
Les informations relatives aux incidents ne disposaient pas d'un point d'entrée unique. Cela compliquait l'unification des processus et la fiabilité du suivi.

### b. Manque de visibilité opérationnelle
Sans tableau de bord synthétique ni vue spécifique sur les incidents non résolus, il était difficile d'identifier rapidement les départs les plus affectés, les incidents critiques ou les dossiers les plus anciens.

### c. Difficulté d'analyse
L'absence de structuration uniforme des données limitait la possibilité de produire :

- des répartitions par statut ;
- des analyses par priorité ;
- des tendances temporelles ;
- des rapports exploitables par la direction.

### d. Suivi insuffisant des actions
La simple existence d'un incident ne suffit pas ; il faut aussi savoir :

- qui a créé l'incident ;
- qui l'a mis à jour ;
- quelles valeurs ont changé ;
- quelles actions ont été menées ;
- à quel moment l'incident a été résolu ou clôturé.

### e. Production documentaire limitée
La génération de rapports journaliers et mensuels devait être facilitée par des exports structurés en **PDF** et **Excel**, alors que le besoin n'était pas correctement couvert de manière intégrée.

## 2.3 Expression des besoins fonctionnels et non-fonctionnels

### 2.3.1 Besoins fonctionnels
L'application devait permettre :

1. l'authentification sécurisée des utilisateurs ;
2. la gestion des rôles et permissions ;
3. la création, la consultation, la modification et la suppression des incidents ;
4. la génération automatique du code incident ;
5. le calcul automatique de la durée lors de la clôture ;
6. l'affectation d'un opérateur, d'un responsable et d'un superviseur ;
7. la gestion des catalogues de départs, types, causes, statuts et priorités ;
8. l'affichage de KPI et graphiques sur le dashboard ;
9. l'affichage d'une page dédiée aux incidents en cours ;
10. la génération de rapports journaliers et mensuels ;
11. l'export PDF, CSV et Excel ;
12. la traçabilité via un historique d'actions et un journal d'audit ;
13. la diffusion temps réel des changements via WebSocket ;
14. l'amélioration UX par autocomplétion et filtrage dynamique des causes.

### 2.3.2 Besoins non-fonctionnels
L'application devait également respecter plusieurs contraintes de qualité :

- ergonomie de l'interface ;
- sécurité d'accès ;
- cohérence et intégrité des données ;
- rapidité de consultation des incidents ;
- maintenabilité du code ;
- architecture évolutive ;
- compatibilité avec un déploiement Docker.

## 2.4 Cas d'utilisation

### 2.4.1 Acteurs
- Administrateur
- Superviseur
- Opérateur Terrain

### 2.4.2 Cas d'utilisation principaux

| Acteur | Fonctionnalité | Finalité |
| --- | --- | --- |
| Administrateur | Gérer les utilisateurs | Créer, modifier, activer ou désactiver les comptes |
| Administrateur | Gérer les catalogues | Maintenir les données de référence |
| Administrateur | Consulter l'audit | Contrôler la traçabilité globale |
| Superviseur | Consulter le dashboard | Piloter les incidents et les tendances |
| Superviseur | Mettre à jour un incident | Affecter, suivre, valider la résolution |
| Superviseur | Générer des rapports | Produire des synthèses périodiques |
| Opérateur Terrain | Déclarer un incident | Créer une fiche incident complète |
| Opérateur Terrain | Suivre les incidents en cours | Voir les incidents ouverts et leur ancienneté |
| Opérateur Terrain | Renseigner les actions menées | Capitaliser les interventions réalisées |

## 2.5 Contraintes techniques et organisationnelles

### 2.5.1 Contraintes techniques
Le cahier des charges impose l'utilisation ou la prise en compte des technologies suivantes :

- Laravel 12
- PHP 8.2+
- Blade
- TailwindCSS
- Chart.js
- Spatie laravel-permission
- Laravel Reverb
- DomPDF
- Maatwebsite Excel 3.1
- MySQL 8.4
- Docker Sail

### 2.5.2 Contraintes organisationnelles
Le projet s'inscrit dans un cadre de stage avec :

- une durée limitée de deux mois ;
- un besoin métier déjà formalisé ;
- un périmètre de réalisation prioritaire ;
- des validations progressives côté encadrement ;
- une exigence documentaire incluant guides et documentation technique.

### 2.5.3 Contraintes de continuité
Le développement devait s'effectuer sans compromettre les fonctionnalités déjà mises en place au fil du projet. Chaque nouveau module devait s'intégrer sans casser l'existant, ce qui implique une grande attention à la cohérence des routes, vues, services et schémas de données.

---

# Chapitre 3 — Conception

## 3.1 Choix technologiques et justification

### 3.1.1 Pourquoi Laravel 12
Laravel a été retenu pour plusieurs raisons :

- structure MVC claire ;
- ORM Eloquent facilitant la modélisation ;
- système d'authentification mature ;
- intégration simple des middlewares et permissions ;
- écosystème riche ;
- rapidité de mise en œuvre pour un projet de stage.

Face à d'autres frameworks PHP ou à des solutions Node.js, Laravel offrait un meilleur compromis entre productivité, lisibilité et robustesse pour un outil de gestion interne.

### 3.1.2 Pourquoi MySQL 8.4
Le besoin principal du projet concerne des données transactionnelles fortement structurées : utilisateurs, incidents, catalogues, statuts, priorités, logs.  
Un SGBD relationnel s'impose donc naturellement. MySQL 8.4 apporte :

- stabilité ;
- bonne compatibilité avec Laravel ;
- facilité d'administration ;
- performances adaptées au volume visé ;
- gestion fiable des contraintes d'intégrité.

Le choix d'une base NoSQL n'aurait pas été pertinent pour ce niveau de normalisation métier.

### 3.1.3 Pourquoi Blade et non une SPA
Le projet repose sur des écrans métiers essentiellement orientés formulaires, tableaux, filtres et rapports. L'usage de **Blade** permet :

- un rendu serveur simple ;
- une intégration rapide avec les routes Laravel ;
- une complexité front maîtrisée ;
- une maintenance plus accessible dans un contexte académique et institutionnel.

### 3.1.4 Pourquoi Laravel Reverb
Le cahier des charges exige un suivi en temps réel des changements d'incidents.  
Reverb a été retenu car :

- il est intégré à l'écosystème Laravel ;
- il évite une dépendance externe de type Pusher ;
- il facilite les tests et le déploiement en environnement contrôlé ;
- il répond au besoin de broadcast immédiat d'événements métier.

### 3.1.5 Pourquoi TailwindCSS et Chart.js
TailwindCSS permet de concevoir rapidement des interfaces cohérentes, adaptatives et sobres.  
Chart.js offre quant à lui un excellent compromis entre simplicité d'intégration et richesse visuelle pour :

- les courbes d'évolution ;
- les donuts de répartition ;
- les barres de comparaison ;
- les graphiques multi-axes.

## 3.2 Architecture de l'application

L'architecture retenue est une architecture **MVC enrichie**.  
Elle peut être comprise selon les couches suivantes :

- **Présentation** : vues Blade, formulaires, navigation, graphiques ;
- **Routage** : `routes/web.php` ;
- **Contrôle** : contrôleurs responsables de l'orchestration ;
- **Validation** : Form Requests dédiées ;
- **Services** : logique métier mutualisée ;
- **Persistance** : modèles Eloquent et base MySQL ;
- **Temps réel** : événements broadcastés via Reverb.

Cette structuration présente plusieurs avantages :

- lisibilité du code ;
- séparation des responsabilités ;
- réutilisabilité ;
- testabilité ;
- facilité d'évolution.

## 3.3 Modèle de données

Le modèle de données s'organise autour de **dix tables métier et d'administration principales**, auxquelles s'ajoutent les tables techniques de gestion des permissions.

Les tables les plus importantes sont :

- `users`
- `departements`
- `type_incidents`
- `causes`
- `statuses`
- `priorites`
- `incidents`
- `incident_actions`
- `logs`
- les tables Spatie : `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

### 3.3.1 Relations fondamentales
- un utilisateur peut déclarer plusieurs incidents ;
- un incident appartient à un départ ;
- un incident est associé à un type, un statut et une priorité ;
- une cause peut être liée à un type d'incident ;
- un incident possède plusieurs actions historiques ;
- un incident peut être référencé par plusieurs logs ;
- un utilisateur peut être rattaché à un départ ;
- les rôles et permissions encadrent les accès.

### 3.3.2 Description textuelle de l'ERD simplifié
Le cœur du diagramme repose sur la table `incidents`, qui se connecte aux tables de référence (`departements`, `type_incidents`, `causes`, `statuses`, `priorites`) ainsi qu'à la table `users` pour les rôles opérationnels (`operateur_id`, `responsable_id`, `superviseur_id`). Les tables `incident_actions` et `logs` gravitent autour d'`incidents` pour assurer la traçabilité. Enfin, les tables Spatie structurent la gouvernance des permissions.

Le schéma détaillé est fourni dans le document annexe `docs/schema-bdd.md`.

## 3.4 Maquettes des interfaces principales

Même si ce mémoire ne reproduit pas les maquettes graphiques sous forme d'images, cinq écrans majeurs peuvent être décrits.

### 3.4.1 Dashboard
Le dashboard présente :

- des KPI en haut de page ;
- un graphique d'évolution des incidents ;
- des donuts de répartition par statut et priorité ;
- un graphique des départs les plus touchés ;
- des listes de causes fréquentes et d'incidents récents.

### 3.4.2 Liste des incidents
La page de liste fournit :

- un tableau paginé ;
- des filtres multi-critères ;
- une recherche textuelle ;
- des boutons d'export ;
- des liens d'action vers la fiche détail et l'édition.

### 3.4.3 Formulaire incident
Le formulaire inclut :

- les champs de contexte technique ;
- la sélection du départ ;
- le type d'incident ;
- les causes filtrées dynamiquement selon le type ;
- la chronologie ;
- les intervenants ;
- les actions menées et le résumé de résolution.

### 3.4.4 Rapports
L'écran de rapport comporte :

- une sélection de période ;
- des filtres métier ;
- des KPI synthétiques ;
- un graphique d'évolution mensuelle ;
- des indicateurs par type, cause et départ critique ;
- des boutons PDF et Excel.

### 3.4.5 Historique et audit
L'écran d'historique met en avant :

- les événements enregistrés ;
- les utilisateurs impliqués ;
- les actions réalisées ;
- les exports de contrôle.

## 3.5 Workflows métier

### 3.5.1 Cycle de vie d'un incident
Le cycle de vie retenu suit la logique suivante :

1. **Ouvert / En cours** : l'incident est déclaré.
2. **En traitement** : une équipe ou un responsable intervient.
3. **Résolu** : la résolution technique est constatée.
4. **Clôturé** : la résolution est validée administrativement.

### 3.5.2 Règles métier importantes
- chaque incident possède un code unique ;
- la durée est calculée automatiquement à partir de `date_debut` et `date_fin` ;
- un statut final force la clôture logique ;
- chaque modification importante doit laisser une trace ;
- les rapports agrègent les données sur une base temporelle cohérente ;
- les incidents en cours doivent être priorisés par criticité et ancienneté.

### 3.5.3 Cohérence entre workflow et modélisation
La présence du champ `is_final` dans la table `statuses` simplifie la logique métier. Elle permet :

- de distinguer les incidents encore actifs ;
- de remplir automatiquement `date_fin` si nécessaire ;
- de recalculer `duree_minutes` ;
- d'afficher les indicateurs ouverts/fermés dans le dashboard et les listes.

# CHAPITRE 4 — RÉALISATION ET IMPLÉMENTATION

## 4.1 Environnement de développement

La réalisation du projet a été menée dans un environnement moderne, orienté productivité, reproductibilité et qualité logicielle. Le choix de l'outillage a eu pour objectif de faciliter aussi bien le développement individuel que la maintenance future par une équipe technique.

L'environnement de développement s'appuie sur les éléments suivants :

- **Système d'exploitation** : Windows avec exécution d'outils PHP, Node.js et Docker ;
- **Éditeur de code** : Visual Studio Code ;
- **Gestion de versions** : Git, avec hébergement du dépôt sur GitHub ;
- **Backend** : PHP 8.2+ et Laravel 12 ;
- **Base de données** : MySQL 8.4 ;
- **Frontend** : Blade, TailwindCSS, Vite, JavaScript ;
- **Temps réel** : Laravel Reverb ;
- **Conteneurisation** : Docker via Laravel Sail ;
- **Outils de build** : Composer pour les dépendances PHP et npm pour les dépendances front-end.

L'intérêt de cet environnement est double. D'une part, il accélère le cycle de développement grâce au rechargement des assets front-end et à l'intégration native des outils Laravel. D'autre part, il facilite le déploiement en rendant l'application plus portable grâce à Docker.

### 4.1.1 Rôle de Visual Studio Code
Visual Studio Code a permis :

- l'édition structurée du code PHP, Blade, JavaScript et Markdown ;
- l'intégration Git pour le suivi des modifications ;
- l'exécution rapide de commandes terminal ;
- l'utilisation d'extensions utiles pour Laravel, TailwindCSS et la coloration syntaxique Mermaid/Markdown.

### 4.1.2 Intérêt de Git et GitHub
L'utilisation de Git et GitHub a assuré :

- l'historisation du projet ;
- la sauvegarde distante du code ;
- la possibilité de travailler par incréments ;
- une meilleure traçabilité des changements ;
- une base claire pour une éventuelle collaboration future.

### 4.1.3 Place de Docker Sail
Laravel Sail a été retenu comme solution de conteneurisation légère afin de :

- normaliser l'environnement d'exécution ;
- limiter les problèmes de dépendances locales ;
- simplifier le démarrage du projet sur une autre machine ;
- embarquer facilement le serveur applicatif Laravel et MySQL.

## 4.2 Mise en place du projet Laravel 12 et configuration

La mise en place du projet a suivi une logique progressive : initialisation du socle applicatif, configuration de la base, création du modèle de données, puis ajout incrémental des modules métier.

### 4.2.1 Initialisation
Le socle du projet a été construit à partir de Laravel 12, choisi pour :

- sa maturité ;
- sa structure claire en MVC ;
- la richesse de son écosystème ;
- sa bonne intégration avec Blade, Eloquent, Reverb et les packages tiers nécessaires.

Les premières étapes ont consisté à :

1. créer ou récupérer le dépôt Git ;
2. installer les dépendances PHP avec Composer ;
3. installer les dépendances front-end avec npm ;
4. configurer le fichier `.env` ;
5. générer la clé d'application ;
6. exécuter les migrations ;
7. injecter les données de référence via les seeders.

### 4.2.2 Configuration de la base de données
La base MySQL a été configurée à partir des variables suivantes :

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Cette configuration permet à Laravel d'interagir avec MySQL via Eloquent ORM. Les migrations ont ensuite servi à créer l'ensemble des tables métier et d'administration.

### 4.2.3 Configuration des communications temps réel
Le module temps réel repose sur Laravel Reverb. Sa configuration s'appuie sur des variables comme :

- `REVERB_APP_ID`
- `REVERB_APP_KEY`
- `REVERB_APP_SECRET`
- `REVERB_HOST`
- `REVERB_PORT`

Cette couche permet de diffuser des événements lorsque l'état d'un incident change, afin que les autres utilisateurs voient immédiatement les mises à jour utiles.

### 4.2.4 Structuration du code
Le projet a été structuré autour des couches suivantes :

- **Routes** : définition des points d'entrée HTTP ;
- **Controllers** : orchestration des cas d'usage ;
- **Form Requests** : validation des saisies ;
- **Services** : logique métier transverse ;
- **Models** : accès et relations aux données ;
- **Views Blade** : rendu HTML côté serveur ;
- **Events** : publication des changements temps réel ;
- **Exports et Reports** : génération des sorties documentaires.

Cette organisation vise à éviter la concentration de toute la logique dans les contrôleurs et à rendre le code plus maintenable.

## 4.3 Implémentation des modules

## 4.3.1 Authentification et gestion des rôles

L'authentification constitue la première barrière de sécurité de l'application. Elle repose sur le socle Laravel d'authentification web et sur un système de rôles et permissions fourni par le package **Spatie laravel-permission**.

Trois profils fonctionnels ont été pris en compte :

- **Administrateur**
- **Superviseur**
- **Opérateur Terrain**

Ces rôles encadrent les droits d'accès selon le principe du moindre privilège. Concrètement :

- l'administrateur dispose des droits étendus de gestion ;
- le superviseur suit et coordonne les opérations ;
- l'opérateur terrain saisit et met à jour les incidents liés à ses interventions.

L'intérêt principal de ce mécanisme est d'éviter une application uniforme où tous les utilisateurs auraient les mêmes droits. Au contraire, chaque rôle voit les pages, actions et boutons qui correspondent à son périmètre opérationnel.

Sur le plan technique :

- les rôles et permissions sont injectés par un seeder dédié ;
- les routes sont protégées par des middlewares ;
- les vues Blade peuvent afficher ou masquer des actions selon les permissions ;
- les contrôleurs appliquent également des contrôles côté serveur.

Cette double protection, interface et backend, renforce la sécurité applicative.

## 4.3.2 Module Incidents

Le module Incident constitue le cœur fonctionnel du projet. Il gère le cycle de vie complet d'une panne ou anomalie sur le réseau électrique.

### a. Création d'un incident
Lorsqu'un opérateur déclare un incident, il renseigne notamment :

- le départ concerné ;
- le type d'incident ;
- la cause éventuelle ;
- la localisation ;
- la date de début ;
- la priorité ;
- le statut initial ;
- les acteurs impliqués ;
- les premières actions menées.

Un **code d'incident unique** est généré automatiquement. Cette automatisation évite les doublons et normalise la nomenclature des références.

### b. Consultation et filtrage
La liste des incidents propose des filtres multi-critères permettant de rechercher selon :

- le départ ;
- le statut ;
- la priorité ;
- le type ;
- la période ;
- un mot-clé.

Cette logique répond directement au besoin métier d'identifier rapidement un ensemble d'incidents comparables ou de retrouver une fiche spécifique.

### c. Mise à jour et clôture
Un incident peut évoluer au fil de son traitement :

- changement de statut ;
- affectation d'un responsable ;
- ajout d'actions menées ;
- enrichissement du résumé de résolution.

Lorsque l'incident passe à un statut final, l'application peut :

- renseigner `date_fin` si besoin ;
- calculer automatiquement `duree_minutes` ;
- enregistrer `clotured_at`.

Cette règle supprime les oublis fréquents observés dans des processus purement manuels.

### d. Tableau des incidents en cours
Un écran spécifique met en avant les incidents non résolus. Ils sont triés selon :

- la criticité ;
- l'ancienneté.

Ce choix est important dans un contexte électrique, où les incidents les plus critiques et les plus anciens doivent être visibles en priorité pour le pilotage opérationnel.

## 4.3.3 Module Catalogues

Les catalogues structurent les données de référence utilisées dans toute l'application. Ils évitent la saisie libre anarchique et contribuent à la qualité des données.

Les catalogues gérés sont :

- les départements ou départs ;
- les types d'incidents ;
- les causes ;
- les statuts ;
- les priorités.

### a. Départs CEET
Le catalogue des départs est particulièrement stratégique. Il contient des informations telles que :

- le code ;
- le nom ;
- la zone ;
- la direction d'exploitation ;
- le poste de répartition ;
- le poste source ;
- le transformateur ;
- l'arrivée ;
- la charge maximale ;
- l'unité de charge.

L'enrichissement de ces données permet de rapprocher davantage l'application de la réalité technique du réseau.

### b. Types et causes
Le lien entre type d'incident et cause a été modélisé pour améliorer l'ergonomie de saisie. Ainsi, le formulaire peut filtrer les causes selon le type choisi, ce qui :

- accélère la saisie ;
- réduit les incohérences ;
- améliore la qualité statistique des données enregistrées.

### c. Statuts et priorités
Les statuts déterminent la progression métier de l'incident, tandis que les priorités expriment son niveau d'urgence. Les deux catalogues sont utilisés dans :

- la saisie ;
- les filtres ;
- les dashboards ;
- les exports ;
- les règles de calcul de durée et de clôture.

## 4.3.4 Tableau de bord et statistiques

Le tableau de bord a été conçu pour transformer les données incident en indicateurs décisionnels directement exploitables.

Les indicateurs principaux portent sur :

- le nombre total d'incidents ;
- les incidents ouverts ;
- les incidents clôturés ;
- la durée moyenne ;
- la répartition par statut ;
- la répartition par priorité ;
- l'évolution temporelle ;
- les départs les plus touchés.

L'implémentation s'appuie sur :

- des requêtes d'agrégation côté serveur ;
- l'envoi des données aux vues Blade ;
- l'affichage graphique via Chart.js.

L'intérêt du dashboard est de fournir une lecture synthétique à plusieurs niveaux :

- **opérationnel** pour suivre les incidents en cours ;
- **managérial** pour repérer les tendances ;
- **analytique** pour identifier les points faibles du réseau.

## 4.3.5 Temps réel avec Laravel Reverb

Le temps réel constitue une valeur ajoutée importante du projet. Dans un environnement réseau, l'information perd rapidement de sa pertinence si elle n'est pas diffusée immédiatement.

Chaque changement significatif sur un incident peut déclencher un événement `IncidentChanged`. Cet événement est broadcasté via Laravel Reverb, ce qui permet à d'autres clients connectés de recevoir les mises à jour sans rechargement complet de page.

Les bénéfices métier sont les suivants :

- meilleure coordination entre opérateurs et superviseurs ;
- réduction du délai d'accès à l'information ;
- amélioration de la visibilité collective ;
- renforcement de la réactivité.

Sur le plan technique, l'événement transporte notamment :

- l'identifiant de l'incident ;
- son code ;
- son statut ;
- sa priorité ;
- l'horodatage de mise à jour ;
- la nature de l'action.

## 4.3.6 Module Rapports

La production de rapports répond à un besoin fort de synthèse et d'archivage. L'application distingue principalement :

- les rapports journaliers ;
- les rapports mensuels ;
- les exports tabulaires.

### a. Rapports PDF
Les rapports PDF sont générés avec **DomPDF**. Ils fournissent :

- un support de diffusion simple ;
- un format stable pour l'impression ;
- une restitution fidèle des indicateurs de synthèse.

### b. Exports Excel
Les exports natifs au format **XLSX** sont réalisés avec **Maatwebsite Excel**. Ils permettent :

- des analyses complémentaires dans Excel ;
- des tris et filtres avancés ;
- le partage de données auprès d'autres services ;
- l'archivage des historiques.

### c. Service de préparation des données
Le service `IncidentReportService` centralise la préparation des données nécessaires aux rapports. Cette centralisation limite la duplication de logique entre plusieurs contrôleurs ou vues.

## 4.3.7 Traçabilité et audit

La traçabilité a constitué un axe majeur du projet, compte tenu de la sensibilité des données de supervision et de la nécessité de justifier les opérations réalisées.

Deux mécanismes complémentaires ont été mis en place :

1. **IncidentAction** pour l'historique métier des modifications liées à un incident ;
2. **Log** pour la journalisation plus générale des actions utilisateur.

Cette double traçabilité permet :

- de savoir qui a fait quoi ;
- de connaître la date et l'heure d'une action ;
- de comparer les anciennes et nouvelles valeurs ;
- d'auditer le comportement des utilisateurs ;
- de reconstituer le cycle de vie d'un incident.

Dans un contexte de gestion d'incidents, cette traçabilité est précieuse aussi bien pour la supervision quotidienne que pour les audits internes ou les revues post-incident.

## 4.4 Tests et validation

La validation du projet ne s'est pas limitée à l'obtention d'une interface fonctionnelle. Elle a porté sur la cohérence métier, la fiabilité des calculs, la sécurité d'accès et la qualité générale de l'expérience utilisateur.

### 4.4.1 Validation fonctionnelle
La validation fonctionnelle a consisté à vérifier :

- la création correcte d'un incident ;
- l'unicité du code généré ;
- le calcul automatique de la durée ;
- le comportement des statuts finaux ;
- le bon affichage des dashboards ;
- la cohérence des rapports PDF et Excel ;
- la disponibilité des données dans les filtres ;
- la restriction d'accès selon les rôles.

### 4.4.2 Validation métier
Une attention particulière a été portée à l'adéquation avec les usages métier :

- tri des incidents en cours par criticité et ancienneté ;
- importance des départs et des priorités ;
- qualité des catalogues de référence ;
- visibilité de la traçabilité ;
- clarté des indicateurs de pilotage.

### 4.4.3 Validation technique
Des vérifications ont été menées sur :

- les migrations et seeders ;
- la robustesse des relations Eloquent ;
- les formulaires et validations ;
- les exports ;
- les builds front-end ;
- la diffusion temps réel.

### 4.4.4 Limites de la validation
Comme dans beaucoup de projets académiques et de stage, la validation reste perfectible sur certains points :

- couverture de tests automatisés plus large ;
- scénarios de charge ;
- retours utilisateurs sur une durée prolongée ;
- mise en production réelle sur un environnement d'exploitation complet.

## 4.5 Difficultés rencontrées et solutions apportées

Cette section doit être personnalisée par le stagiaire à partir de son vécu réel sur le terrain et pendant le développement.

### [À COMPLÉTER] Difficultés techniques rencontrées
Décrire ici, de manière sincère et argumentée :

- les problèmes de configuration rencontrés ;
- les difficultés liées à Laravel 12, Reverb, Docker ou MySQL ;
- les difficultés d'intégration front-end ;
- les obstacles liés à la modélisation des données ou à la logique métier.

Exemples possibles à adapter :

- incompatibilités de versions ou dépendances ;
- compréhension initiale du métier réseau ;
- gestion des dates et calcul des durées ;
- sécurisation des accès par rôles ;
- difficultés de diffusion temps réel.

### [À COMPLÉTER] Solutions apportées
Présenter ici :

- la méthode de résolution adoptée ;
- les recherches ou documentations consultées ;
- les arbitrages techniques réalisés ;
- les leçons tirées pour les développements futurs.

### [À COMPLÉTER] Apports personnels
Expliquer en quoi ces difficultés ont permis :

- de progresser techniquement ;
- de mieux comprendre le métier ;
- de structurer une démarche d'analyse et de résolution de problème.

# CHAPITRE 5 — RÉSULTATS ET PERSPECTIVES

## 5.1 Fonctionnalités livrées

Le tableau suivant met en regard les principales attentes du cahier des charges et leur niveau de réalisation dans l'application.

| Exigence du CDC | Réalisation | Commentaire |
|---|---|---|
| Authentification sécurisée | Réalisé | Auth web Laravel avec protection des routes |
| Gestion des rôles | Réalisé | Intégration de Spatie laravel-permission |
| CRUD incidents | Réalisé | Création, consultation, modification, suppression selon droits |
| Génération automatique du code incident | Réalisé | Via `IncidentService::generateCode()` |
| Calcul automatique de durée | Réalisé | Durée recalculée lors de la clôture |
| Affectation opérateur / responsable / superviseur | Réalisé | Relations utilisateurs intégrées |
| Filtres multi-critères | Réalisé | Liste incidents et page incidents en cours |
| Dashboard KPI | Réalisé | KPI, graphes, distributions, tendances |
| Temps réel | Réalisé | Diffusion des changements via Reverb |
| Rapports PDF | Réalisé | Export synthétique via DomPDF |
| Export Excel | Réalisé | Export natif `.xlsx` via Maatwebsite Excel |
| Traçabilité et audit | Réalisé | Tables `incident_actions` et `logs` |
| Gestion des catalogues | Réalisé | Départs, types, causes, statuts, priorités |
| Documentation technique | Réalisé | Architecture, schéma BDD, guides et mémoire |

### 5.1.1 Appréciation générale
Le projet livré couvre l'essentiel des besoins exprimés dans le cahier des charges. Il constitue une base logicielle cohérente, exploitable et extensible pour la gestion des incidents du réseau électrique.

Au-delà du simple respect fonctionnel, plusieurs aspects renforcent la valeur du livrable :

- une structuration propre du code ;
- une modélisation métier claire ;
- une attention portée à la traçabilité ;
- des outils d'analyse et d'export immédiatement utiles.

## 5.2 Captures d'écran commentées

### [À COMPLÉTER] Capture 1 — Tableau de bord
Insérer ici une capture du dashboard principal et commenter :

- les KPI affichés ;
- la lecture des graphiques ;
- l'intérêt opérationnel de la vue.

### [À COMPLÉTER] Capture 2 — Liste des incidents
Insérer ici une capture de la liste et commenter :

- les filtres ;
- la lisibilité du tableau ;
- l'apport du tri et des actions rapides.

### [À COMPLÉTER] Capture 3 — Formulaire de déclaration
Insérer ici une capture du formulaire et commenter :

- le rôle des champs ;
- l'auto-complétion des causes ;
- le calcul de durée ;
- la logique de clôture.

### [À COMPLÉTER] Capture 4 — Rapports
Insérer ici une capture de la page des rapports et commenter :

- la différence entre rapport journalier et mensuel ;
- la lecture des indicateurs ;
- les possibilités d'export.

### [À COMPLÉTER] Capture 5 — Historique / audit
Insérer ici une capture de l'historique ou des logs et commenter :

- les informations tracées ;
- la valeur pour le contrôle et l'analyse post-incident.

## 5.3 Performances et retours utilisateurs

### [À COMPLÉTER] Observations de performance
Indiquer ici, sur la base d'essais réels ou de démonstrations :

- le temps moyen de chargement perçu ;
- la fluidité des pages principales ;
- le comportement des exports ;
- la réactivité des mises à jour temps réel.

### [À COMPLÉTER] Retours des utilisateurs ou encadrants
Préciser ici :

- les remarques formulées par le maître de stage ;
- les retours des utilisateurs tests ;
- les suggestions d'amélioration émises ;
- les points particulièrement appréciés.

## 5.4 Limites de l'application actuelle

Malgré la qualité du résultat obtenu, l'application présente encore certaines limites, normales au regard du temps imparti du stage et du périmètre initial.

### 5.4.1 Limites fonctionnelles
- absence de module natif de notifications par e-mail ou SMS ;
- absence d'application mobile dédiée ;
- absence d'intégration automatique avec des systèmes externes comme SCADA ;
- workflow encore centré sur un usage web interne.

### 5.4.2 Limites techniques
- couverture de tests automatisés à renforcer ;
- absence d'observabilité avancée de production ;
- dimensionnement à consolider pour un nombre élevé d'utilisateurs simultanés ;
- dépendance à une saisie utilisateur correcte pour certaines informations métier.

### 5.4.3 Limites organisationnelles
- nécessité d'accompagner le changement auprès des utilisateurs ;
- besoin de normaliser davantage certains référentiels ;
- nécessité de gouvernance autour des mises à jour de catalogues.

## 5.5 Perspectives d'évolution

L'application constitue un socle solide pouvant évoluer vers un outil encore plus intégré au pilotage des opérations de distribution électrique.

Parmi les principales pistes d'évolution, on peut citer :

### 5.5.1 Notifications intelligentes
- envoi d'e-mails automatiques lors d'un incident critique ;
- notifications SMS pour les incidents prioritaires ;
- alertes ciblées selon la zone, le départ ou le rôle.

### 5.5.2 Application mobile
- consultation rapide depuis le terrain ;
- déclaration d'incident en mobilité ;
- ajout de photos ou pièces jointes ;
- fonctionnement partiel hors ligne.

### 5.5.3 Intégration avec les systèmes techniques
- interconnexion avec des systèmes SCADA ;
- récupération automatique d'alarmes ;
- corrélation entre données de supervision et incidents déclarés.

### 5.5.4 Analyse décisionnelle avancée
- tableaux de bord plus poussés ;
- analyse par zone, départ, période et cause ;
- indicateurs de disponibilité par segment réseau ;
- prévision des zones à risque.

### 5.5.5 Intelligence artificielle et analytique prédictive
- suggestion automatique de causes probables ;
- détection de récurrences ;
- prédiction des départs à fort risque ;
- aide à la priorisation opérationnelle.

# CONCLUSION GÉNÉRALE

Ce stage réalisé au sein de la **Cellule de Transformation Digitale (CTD)** de la **Compagnie Énergie Électrique du Togo (CEET)** a permis de répondre à une problématique concrète, actuelle et à forte portée opérationnelle : la gestion structurée des incidents affectant le réseau électrique. Dans un contexte où l'information terrain circule parfois de manière fragmentée, où le suivi des interventions peut manquer de continuité et où la production de statistiques fiables constitue un enjeu de pilotage, le développement d'une application dédiée apparaissait comme une réponse pertinente.

Le travail effectué a conduit à la conception et à la réalisation d'une application web basée sur **Laravel 12**, **MySQL 8.4**, **Blade**, **TailwindCSS**, **Laravel Reverb**, **DomPDF** et **Maatwebsite Excel**. Cette solution permet la déclaration, la mise à jour, la priorisation, la clôture, l'analyse et l'export des incidents. Elle introduit également une dimension importante de traçabilité et de temps réel, tout en s'appuyant sur un système de rôles adapté aux besoins métier.

Au-delà du produit livré, ce stage a constitué une expérience formatrice. Il a permis de mobiliser des compétences en analyse des besoins, modélisation de données, développement full stack, sécurité applicative, visualisation de données et documentation technique. Il a également montré combien la réussite d'une solution numérique dépend de sa capacité à épouser fidèlement les réalités opérationnelles du métier.

En définitive, l'application développée représente un socle fonctionnel solide pour la modernisation du suivi des incidents à la CEET. Elle peut désormais servir de base à des évolutions plus ambitieuses telles que la mobilité, la notification proactive, l'intégration avec les systèmes techniques de supervision ou encore l'analytique prédictive. Ce projet illustre ainsi la contribution concrète qu'un stage bien encadré peut apporter à la transformation digitale d'une organisation.

# BIBLIOGRAPHIE ET WEBOGRAPHIE

## Ouvrages et références techniques

1. Laravel. *Laravel 12.x Documentation*. Documentation officielle. Disponible sur : https://laravel.com/docs/12.x
2. Spatie. *Laravel Permission Documentation*. Documentation officielle. Disponible sur : https://spatie.be/docs/laravel-permission
3. Laravel. *Laravel Reverb Documentation*. Documentation officielle. Disponible sur : https://laravel.com/docs/12.x/reverb
4. Tailwind Labs. *Tailwind CSS Documentation*. Documentation officielle. Disponible sur : https://tailwindcss.com/docs
5. Chart.js. *Chart.js Documentation*. Documentation officielle. Disponible sur : https://www.chartjs.org/docs/latest/
6. barryvdh. *Laravel DomPDF Documentation*. Documentation officielle. Disponible sur : https://github.com/barryvdh/laravel-dompdf
7. Maatwebsite. *Laravel Excel Documentation*. Documentation officielle. Disponible sur : https://docs.laravel-excel.com/
8. Oracle. *MySQL 8.4 Reference Manual*. Documentation officielle. Disponible sur : https://dev.mysql.com/doc/
9. Docker. *Docker Documentation*. Documentation officielle. Disponible sur : https://docs.docker.com/
10. GitHub. *GitHub Docs*. Documentation officielle. Disponible sur : https://docs.github.com/

## Références méthodologiques

1. Pressman, R. S., Maxim, B. R. *Software Engineering: A Practitioner’s Approach*.
2. Sommerville, I. *Software Engineering*.
3. Documentation institutionnelle et échanges internes relatifs aux besoins du service d'accueil. [À COMPLÉTER selon sources réellement utilisées]

# ANNEXES

## Annexe A — Extrait du schéma SQL

Le schéma détaillé de la base de données, les relations entres tables, les index et les données de référence sont documentés dans le fichier :

- `docs/schema-bdd.md`

Cette annexe peut être enrichie, dans la version imprimée du mémoire, par :

- le diagramme ERD exporté en image ;
- un extrait SQL des migrations ;
- quelques requêtes représentatives de consultation et d'agrégation.

## Annexe B — Captures d'écran

### [À COMPLÉTER]
Insérer ici, dans la version finale du mémoire :

- les captures du dashboard ;
- la liste des incidents ;
- le formulaire de déclaration ;
- la page des rapports ;
- la vue historique / audit ;
- éventuellement la page incidents en cours.

Pour chaque capture, ajouter :

- un titre ;
- une légende ;
- un commentaire métier ou technique.

## Annexe C — Extraits significatifs du code source commentés

Les extraits ci-dessous peuvent être repris dans une version finalisée du mémoire pour illustrer concrètement certaines décisions d'implémentation.

### C.1 Génération du code incident

```php
public function generateCode(): string
{
    do {
        $code = 'INC-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    } while (Incident::where('code_incident', $code)->exists());

    return $code;
}
```

**Commentaire :**  
Cette méthode garantit une référence unique pour chaque incident. Le format retenu embarque la date du jour et un suffixe aléatoire, ce qui facilite à la fois la lecture humaine et l'unicité logique.

### C.2 Synchronisation de la durée lors de la clôture

```php
public function syncDurationOnClosure(Incident $incident): void
{
    $incident->loadMissing('status');

    if (! $incident->status?->is_final) {
        return;
    }

    $dateFin = $incident->date_fin ?: now();

    $incident->forceFill([
        'date_fin' => $dateFin,
        'duree_minutes' => $incident->date_debut
            ? Carbon::parse($incident->date_debut)->diffInMinutes($dateFin)
            : null,
        'clotured_at' => $incident->clotured_at ?: now(),
    ])->save();
}
```

**Commentaire :**  
Cette logique métier centralisée évite les erreurs de saisie et normalise le calcul de la durée. Elle montre l'intérêt d'une couche Service pour porter les règles réutilisables.

### C.3 Événement temps réel `IncidentChanged`

```php
class IncidentChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function broadcastOn(): Channel
    {
        return new Channel('incidents');
    }

    public function broadcastAs(): string
    {
        return 'incident.changed';
    }
}
```

**Commentaire :**  
Cet événement matérialise la diffusion temps réel. Chaque changement pertinent est poussé sur un canal dédié, puis relayé aux interfaces clientes connectées.

### C.4 Exemple de logique d'agrégation pour le dashboard

```php
$byStatus = Incident::query()
    ->selectRaw('status_id, COUNT(*) as total')
    ->with('status:id,libelle,couleur')
    ->groupBy('status_id')
    ->get();
```

**Commentaire :**  
Les indicateurs reposent sur des requêtes d'agrégation simples mais efficaces. Cette approche évite de charger inutilement tout l'historique détaillé lorsque seul un indicateur synthétique est attendu.

### C.5 Construction des données de rapport

```php
private function buildData(Carbon $start, Carbon $end, string $granularity): array
{
    $incidents = Incident::query()
        ->with(['statut', 'priorite', 'departement', 'typeIncident', 'cause'])
        ->whereBetween('date_debut', [$start, $end])
        ->get();

    return [
        'incidents' => $incidents,
        'total' => $incidents->count(),
        'avgDuration' => round($incidents->avg('duree_minutes') ?? 0, 2),
    ];
}
```

**Commentaire :**  
Le service de rapport prépare des données consolidées, réutilisables par plusieurs formats de sortie. Cette stratégie réduit les duplications et facilite les futures évolutions.

---

**Fin du mémoire**

# 🎫 WHEELBORN — Carnet de Bord des Tickets & Backlog de Développement

> **Projet Universitaire Full-Stack** : Laravel 11 • Livewire 3 • Tailwind CSS • Alpine.js • SQLite  
> **Équipe de développement :** Nathanaël & Mathéo  
> **Dernière mise à jour :** Octobre 2026  
> 
> **Légende des priorités :**
> - 🔴 **P0 (Bloquant / MVP)** : Indispensable au fonctionnement du jeu ou exigé par la soutenance.
> - 🟡 **P1 (Majeur)** : Fonctionnalité clé de la boucle de gameplay (rétention, équilibrage).
> - 🟢 **P2 (Confort & Polish)** : Finitions visuelles, animations avancées, QoL (Quality of Life).
> 
> **Répartition générale des rôles :**
> - 🧑‍💻 **Nathanaël** : Collection, Inventaire, Sécurité/Concurrence BDD, Outils Admin & Démo soutenance.
> - 🧑‍💻 **Mathéo** : Moteur de Combat, Système d'Élo FIDE, Matchmaking/Bots, Atelier & Économie d'or.
> - 👥 **Binôme** : Rendu graphique (Paper Doll), Game Feel, Codex & Intégration globale.

---

## 📊 Tableau Récapitulatif de l'Avancement

| Épic / Jalon | Priorité | Responsable Principal | Tickets | Statut |
| :--- | :---: | :---: | :---: | :---: |
| **Épic 0 : Fondations, Roue & Équilibrage** | 🔴 P0 | 👥 Binôme | 5 | ⏳ En cours (4/5) |
| **Épic 1 : Collection & Gestion d'Inventaire** | 🔴 P0 | 🧑‍💻 Nathanaël | 5 | ✅ Terminé (100%) |
| **Épic 2 : Moteur de Combat & Arène 1v1** | 🔴 P0 | 🧑‍💻 Mathéo | 6 | ✅ Terminé (100%) |
| **Épic 3 : Système d'Élo FIDE & Classement** | 🔴 P0 | 🧑‍💻 Mathéo | 6 | ✅ Terminé (100%) |
| **Épic 4 : Atelier & Économie Circulaire** | 🟡 P1 | 🧑‍💻 Mathéo | 5 | ✅ Terminé (100% - 5/5 ✅) |
| **Épic 5 : Game Feel, Rendu Visuel & Paper Doll** | 🟡 P1 | 👥 Binôme | 5 | ⏳ En cours (2/5 - Paper Doll V2 Auras & DA Cosmique ✅) |
| **Épic 6 : Robustesse, Concurrence & Edge Cases** | 🔴 P0 | 🧑‍💻 Nathanaël | 6 | ✅ Terminé (100%) |
| **Épic 7 : Outils Soutenance, Admin & Démo** | 🔴 P0 | 🧑‍💻 Nathanaël | 3 | ⏳ Partiel (2/3 - God Mode Forge Personnalisée & Seeder ✅) |
| **Épic 8 : Tests Automatisés & QA** | 🟡 P1 | 👥 Binôme (réparti) | 5 | ✅ Validé (189 tests, 784 assertions, 100% vert ✅) |
| **Épic 9 : Profil Joueur, Vitrine & Social** | 🔴 P0 | 🧑‍💻 Nathanaël | 5 | ✅ Terminé (5/5 - 100% ✅) |

---

## ✅ Épic 0 — Fondations & Roue de Tirage (Déjà réalisé)

- [x] **T0.1 [Architecture] Mise en place des fondations Laravel 11 + Livewire 3 + Breeze**
  - *Responsable :* 👥 Binôme
  - *Fichiers :* `composer.json`, `routes/web.php`, `resources/views/layouts/app.blade.php`
  - *Détails :* Auth fonctionnelle, layout sombre néon, support Livewire 3 et Alpine.js.
- [x] **T0.2 [Données & Modèles] Schéma BDD, Enums et Données Statiques**
  - *Responsable :* 👥 Binôme
  - *Fichiers :* `app/Models/*`, `app/Enums/*`, `app/Data/*`, `database/migrations/*`
  - *Détails :* 8 races, 8 armes, 8 pouvoirs, 16 synergies, raretés, tables `characters`, `battles`, etc.
- [x] **T0.3 [Génération] Services de génération procédurale et de recyclage**
  - *Responsable :* 👥 Binôme
  - *Fichiers :* `app/Services/CharacterGeneratorService.php`, `app/Services/RecycleService.php`, `app/Services/EnergyManagerService.php`
  - *Détails :* Tirage pondéré, stats dérivées, calcul de la valeur de recyclage en or.
- [x] **T0.4 [UI] Composant Livewire `CharacterWheel`, Cadran Céleste & Carte Physique TCG (3D Flip)**
  - *Responsable :* 👥 Binôme
  - *Fichiers :* `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `resources/css/app.css`
  - *Détails :* Refonte esthétique épurée : titre noble unique, capsules HUD compactes (charges et jauge de chance 10/10), cadran astral central en rotation douce (`animate-spin-slow`) alternant cycliquement entre les 3 disques (Races, Armes, Pouvoirs), bouton d'invocation central "Lancer la Roue", rituel de tirage à 3 roues désynchronisées, et révélation sous forme de vraie carte physique TCG (ratio standard 1:1.52, 440px de haut) avec 3D Flip recto/verso au clic et boutons Garder / Recycler.
- [ ] **T0.5 [Équilibrage & Game Design] Élimination du Paradoxe "Commun > Légendaire" & Scaling Réel de la Rareté**
  - *Priorité :* 🟡 P1 (Cohérence Gameplay & Rétention)
  - *Responsable :* 👥 Binôme
  - *Fichiers :* `docs/COMPENDIUM_GAME_DESIGN_ARMES_ET_SYNERGIES.md`, `app/Services/CharacterGeneratorService.php`, `app/Data/GameConfig.php`, `tests/Feature/CharacterGenerationTest.php`
  - *Problématique constatée :* Les statistiques brutes sont actuellement tirées avant la rareté. Un tirage chanceux sur un héros Commun peut ainsi générer un Power Score supérieur à une carte Légendaire (1% de drop ou Super Spin 10/10), créant une incompréhension et une déception chez le joueur.
  - *Critères d'acceptation :*
    - [ ] Déplacement de la décision de rareté en amont ou application d'un coefficient multiplicateur martial de stats par rareté (ex: Commun ×1.0, Rare ×1.10, Épique ×1.22, Légendaire ×1.35).
    - [ ] Définition de planchers minimaux garantis (Stat Floors) selon la rareté (ex: carte Légendaire = toutes stats $\ge 65$ et au moins une stat $\ge 85$).
    - [ ] Garantie mathématique qu'une carte Légendaire dépasse systématiquement la moyenne d'une carte Commune.
    - [ ] Mise à jour du calcul du Power Score et des tests automatisés pour valider les fourchettes cibles.

---

## 🗃️ Épic 1 — Collection & Inventaire (`CollectionGrid`)
> **Responsable principal :** 🧑‍💻 Nathanaël

### [x] T1.1 — Création du composant Livewire `CollectionGrid`
- **Priorité :** 🔴 P0 (MVP)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `routes/web.php`
- **Description :** Permettre au joueur connecté de consulter l'ensemble de ses cartes de combattants sous forme d'une grille réactive (cartes néon).
- **Critères d'acceptation :**
  - [x] Affichage en grille responsive (1 col mobile, 2 cols tablette, 3-4 cols desktop).
  - [x] Pagination propre (ex: 12 cartes par page) pour éviter de saturer le DOM.
  - [x] Compteur total de cartes possédées et indicateur de capacité.
  - [x] État vide élégant ("Aucun combattant — Fais tourner la Roue !").

### [x] T1.2 — Filtres multi-critères et Tri dynamique
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `tests/Feature/CollectionGridTest.php`
- **Description :** Ajouter une barre d'outils réactive permettant de filtrer et trier rapidement les cartes de sa collection.
- **Critères d'acceptation :**
  - [x] Recherche textuelle en direct (`wire:model.live.debounce`) par nom de héros avec bouton d'effacement rapide.
  - [x] Filtre rapide par rareté (badges cliquables du HUD & boutons de filtrage pour Commune, Rare, Épique, Légendaire).
  - [x] Filtre déroulant par race (8 races) et par arme principale (8 armes).
  - [x] Filtre rapide "Avec Synergie active uniquement" avec indicateur visuel pulsé.
  - [x] Tri dynamique par : Score Power (décroissant/croissant), Rareté (Légendaire -> Commun), Date d'obtention, Victoires en Arène.
  - [x] Mise à jour instantanée sans rechargement de page (Livewire `wire:model.live` + réinitialisation automatique de la pagination).
  - [x] Bouton d'action "Effacer les filtres" et état vide informatif en cas de recherche sans résultat.

### [x] T1.3 — Système de Verrouillage de Carte (Favori / Lock)
- **Priorité :** 🔴 P0 (Sécurité Joueur)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/migrations/2026_10_19_140000_add_is_locked_to_characters_table.php`, `app/Models/Character.php`, `app/Services/RecycleService.php`, `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `tests/Feature/CollectionGridTest.php`
- **Description :** Empêcher la destruction accidentelle d'une carte précieuse lors des sessions de recyclage.
- **Critères d'acceptation :**
  - [x] Champ booléen `is_locked` (défaut `false`) avec index SQL sur le modèle `Character`.
  - [x] Icône de cadenas interactive (🔒/🔓) sur chaque carte pour basculer l'état verrouillé en un clic avec notification.
  - [x] Le bouton "Recycler" individuel est désactivé et transformé en badge sécurisé "🔒 Protégé" si la carte est verrouillée.
  - [x] Le `RecycleService` bloque avec une exception (`DomainException`) toute tentative de recyclage d'une carte verrouillée (sécurité backend).
  - [x] Filtre rapide "🔒 Verrouillés uniquement" dans la barre d'outils de collection.

### [x] T1.4 — Recyclage en Masse Avancé (Multi-critères & Modale Dédiée)
- **Priorité :** 🟡 P1 (Confort de jeu / Économie circulaire)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Services/RecycleService.php`, `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `tests/Feature/CollectionGridTest.php`
- **Description :** Modale de suppression de masse ultra-complète permettant de filtrer les combattants à recycler par rareté, score de puissance, race, arme et préservation des synergies.
- **Critères d'acceptation :**
  - [x] Bouton d'action "♻️ Recyclage en masse" dans le bandeau de collection.
  - [x] Filtrage modulaire par raretés (cases à cocher Communs, Rares, Épiques, Légendaires).
  - [x] Filtrage par plafond de score de puissance (Power Score Max : saisie libre ou raccourcis ≤ 50, ≤ 65, ≤ 80).
  - [x] Ciblage par race spécifique (8 races au choix ou toutes) et arme spécifique (8 armes au choix ou toutes).
  - [x] Protection des synergies : toggle pour exclure automatiquement les héros dotés d'une synergie élémentaire active.
  - [x] Aperçu visuel en direct : liste scrollable affichant chaque héros ciblé (icône de race, nom, rareté, puissance, valeur en or).
  - [x] Calcul prévisionnel instantané du total de pièces d'or 🪙 et du nombre de héros sélectionnés.
  - [x] Protection absolue : exclusion inviolable des héros verrouillés (`is_locked`) et des combattants déployés en arène.
  - [x] Transaction SQL atomique (`DB::transaction` avec `lockForUpdate`) et mise à jour dynamique du solde dans le HUD (`stats-updated`).

### [x] T1.5 — Cartes Recto/Verso (3D Card Flip) & Modale Inspecteur « Fiche Détaillée Épurée »
- **Priorité :** 🟢 P2 (Game Feel & Immersion Collection)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `resources/css/app.css`, `app/Models/Character.php`, `tests/Feature/CollectionGridTest.php`
- **Description :** Cartes double-face immersives dotées d'une véritable animation de retournement 3D (`preserve-3d` / card flip physique) déclenchée **au clic sur la grille** (pour un confort visuel optimal sans tempête d'animations au passage de souris) et **au survol sur la modale d'inspection**, avec bouton "Fiche détaillée & Passifs" dédié ouvrant la modale technique grand format.
- **Critères d'acceptation :**
  - [x] **Animation de Retournement 3D (Physical Card Flip) :**
    - Conteneur avec perspective 3D (`perspective: 1000px;` `.perspective-1000`) et flipper interne (`transform-style: preserve-3d;` `.preserve-3d`).
    - Rotation fluide sur l'axe Y (`transform: rotateY(180deg);`) avec courbe d'accélération naturelle `cubic-bezier(0.23, 1, 0.32, 1)` pour un rendu physique et tactile.
    - Occultation naturelle des faces opposées (`backface-visibility: hidden;` `.backface-hidden`).
    - **Ergonomie Grille vs Modale :**
      - Sur la **grille de collection**, le flip se déclenche **au clic sur la carte** (retour au recto par simple clic sur le verso, bouton/émoji flip superflu supprimé pour alléger l'en-tête).
      - Sur la **modale d'inspection**, le flip reste fluide **au survol (hover)** sans texte indicatif redondant.
    - Isolation stricte des événements pointeur (`pointer-events-none` sur la face cachée, `pointer-events-auto` sur la face active) pour garantir l'interactivité 100% fiable des boutons du verso (cadenas, recyclage, inspection) sans fausse sélection.
  - [x] **Parité Dimensionnelle Recto/Verso :** Hauteur et largeur strictement identiques (`height: 490px; min-height: 490px; w-full`) éliminant tout décalage vertical ou écrasement.
  - [x] **Face Recto (Par défaut) :** Carte collector graphique avec portrait Paper Doll composite dynamique `<x-character-avatar>`, étoiles de rareté, score de puissance, rareté et titre.
  - [x] **Face Verso (Révélée au Flip) :** Tri-slot des composants avec icônes, 4 barres de stats colorées, badges de synergies actives (ou mention d'absence), palmarès d'arène et bouton cadenas.
  - [x] **Modale d'Inspection Grand Format Épurée & Compacte (Refonte UX & Graphique) :**
    - [x] En-tête épuré intégrant le titre du héros, son badge de rareté, son archétype et la croix de fermeture `✕` (suppression du doublon du score et du bouton inférieur "Fermer la fiche").
    - [x] Grille 3 colonnes responsive (`sm:grid-cols-3`) pour les 3 composants (Race, Arme, Pouvoir) affichant leurs passifs innés et effets élémentaires de façon équilibrée et lisible.
    - [x] Suppression de la section redondante des statistiques de combat brutes dans la colonne droite (déjà présentes sur le verso de la carte) pour éliminer tout scroll vertical.
    - [x] Section Synergies actives avec descriptions complètes sans troncature (`break-words`, `whitespace-normal`), affichage lisible et badge "Instable" le cas échéant.
    - [x] Barre inférieure unifiée regroupant l'historique d'arène (V/D, winrate) et les actions de gestion (Verrouiller, Recycler).
  - [x] Suite de tests automatisés validant l'ouverture/fermeture de l'inspecteur, le recyclage et les actions associées.


---

## ⚔️ Épic 2 — Moteur de Combat & Arène 1v1 (`BattleArena`)
> **Responsable principal :** 🧑‍💻 Mathéo

### [x] T2.1 — Implémentation du moteur de combat `BattleEngineService`
- **Priorité :** 🔴 P0 (Cœur de jeu)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/BattleEngineService.php`, `tests/Unit/BattleEngineServiceTest.php`
- **Description :** Développer le service PHP pur simulant l'affrontement tour par tour entre deux combattants de manière déterministe.
- **Critères d'acceptation :**
  - [x] Phase 1 (Initiative) : Le combattant avec la plus haute Vitesse frappe en premier (avec tirage au sort si égalité).
  - [x] Phase 2 (Échange de coups) : Calcul des dégâts bruts atténués par la défense ennemie.
  - [x] Prise en compte des effets d'armes (saignement, coup contondant) et des pouvoirs (brûlure DoT, gel, régénération).
  - [x] Application des synergies (ex: Pyro-Lame, Tempête Électrique, Instabilités).
  - [x] Calcul des coups critiques et esquives basés sur la stat Chance.
  - [x] Sécurité anti-boucle infinie (limite fixée à 25 tours max avec décision aux points).

### [x] T2.2 — Déterminisme, Seed aléatoire et Audit Log (`turns_log`)
- **Priorité :** 🔴 P0 (Anti-triche & Replay)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Models/Battle.php`, `database/migrations/2026_10_20_104207_rename_combat_log_to_turns_log_in_battles_table.php`, `app/Services/ArenaService.php`
- **Description :** Assurer la reproductibilité absolue de chaque combat pour permettre le replay et éliminer toute triche côté client.
- **Critères d'acceptation :**
  - [x] Utilisation d'un générateur pseudo-aléatoire seedé (`Mt19937` isolé).
  - [x] Enregistrement du déroulement complet dans un champ JSON `turns_log` (qui a frappé, dégâts, PV restants, effet déclenché) et figé dans `fighters_snapshot`.
  - [x] Possibilité de recalculer le combat à partir de la seed et d'obtenir un résultat 100% identique (`ArenaService::replay()` & `ArenaService::isAuthentic()`).

### [x] T2.3 — Gestion des Tickets d'Arène & Régénération
- **Priorité :** 🔴 P0 (Économie & Cadence de session)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/EnergyManagerService.php`, `app/Models/User.php`, `app/Data/GameConfig.php`
- **Description :** Réguler les combats via un stock limité de tickets (anti-farm compulsif).
- **Critères d'acceptation :**
  - [x] Stock maximum : 5 tickets d'arène.
  - [x] Vitesse de recharge : 1 ticket toutes les 5 minutes (avec calcul de rattrapage hors-ligne).
  - [x] Déduction atomique d'un ticket au lancement d'un duel (`decrement` conditionnel dans transaction SQL).
  - [x] Message explicite si tentative de combat avec 0 ticket et désactivation propre du bouton côté front.
  - [x] Timer de rechargement en temps réel seconde par seconde via composant Alpine.js (`+1 dans mm:ss`) synchronisé avec actualisation automatique Livewire à `00:00`.

### [x] T2.4 — Interface de Combat Face-à-Face Livewire (`BattleArena`)
- **Priorité :** 🔴 P0 (Expérience Utilisateur)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/BattleArena.php`, `resources/views/livewire/battle-arena.blade.php`
- **Description :** Interface interactive de préparation et de visualisation du combat.
- **Critères d'acceptation :**
  - [x] **Architecture Cockpit Zero-Scroll :**
    - Répartition ergonomique 65% (Arène de duel face-à-face) / 35% (Poste de commande & logs) sans scroll vertical nécessaire.
    - Hauteur synchronisée via `items-stretch` et `h-full` pour un alignement bas net.
  - [x] **Modale de Sélection de Combattant Flottante :**
    - Recherche instantanée par nom, race, arme ou pouvoir (`wire:model.live.debounce`).
    - Filtres par rareté (Tous, Commune, Rare, Épique, Légendaire) et tri dynamique (Puissance, Victoires, Récents).
    - Grille compacte avec avatar Paper Doll et badge de déploiement en arène.
  - [x] **Cartes Officielles WheelBorn & 3D Flip :**
    - Ratio physique collector 270px fixe sans déformation ni étirement.
    - Animation de retournement 3D physique au clic vers le verso (stats détaillées, tri-slot éléments, synergies et palmarès).
    - Épuration visuelle : suppression de la mention superflue "Cliquer pour les stats" dans l'arène et la collection.
  - [x] **Comparatif Dynamique des Forces Pré-combat :**
    - 4 jauges de confrontation horizontales bicolores (Vert 🟢 à gauche ➔ 🔴 Rouge à droite) pour PV, Force, Vitesse et Défense calculant le ratio de force exact entre les deux cartes.
    - Badge d'avantage de puissance globale et indicateur d'initiative explicite (`Tu frappes en 1er` / `Bot frappe en 1er`).
    - Comparatif des synergies élémentaires actives joueur vs bot.
  - [x] **Déroulement du Duel & Bilan :**
    - Défilement tour par tour des échanges avec barres de PV réactives animées via Alpine.js.
    - Bilan final avec delta d'Élo, pièces et XP gagnés.
    - Actions post-combat enrichies : bouton « Revanche » (rejouer contre le même bot) et bouton « Autre adversaire » (générer un nouveau bot).

### [x] T2.5 — Bouton Fast-Forward ("Passer le combat / Résultat direct")
- **Priorité :** 🟡 P1 (QoL)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `resources/views/livewire/battle-arena.blade.php`, `resources/js/battle-animation.js`
- **Description :** Permettre aux joueurs réguliers de passer l'animation pour aller directement à l'écran de résultat.
- **Critères d'acceptation :**
  - [x] Bouton bien visible "Passer le combat ⏭".
  - [x] Interrompt le timing Alpine.js et affiche instantanément les barres de PV finales et le bilan.
  - [x] Le calcul serveur restant identique, l'issue ne dépend pas de l'animation front.

### [x] T2.6 — Gestion des Cas Limites : Égalités & Double K.O.
- **Priorité :** 🟡 P1 (Edge case)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/BattleEngineService.php`, `app/Services/ArenaService.php`, `resources/views/livewire/battle-arena.blade.php`
- **Description :** Traiter proprement les scénarios où les deux combattants tombent à 0 PV au même tour (ex: dégâts de poison mutuels).
- **Critères d'acceptation :**
  - [x] Règle définie : Si double K.O., match déclaré "Match Nul" (`DRAW`, `winner_id = null`).
  - [x] En cas de Match Nul : aucun gain/perte d'Élo ($\Delta \text{Élo} = 0$), et petit lot de consolation (20 pièces + 20 XP).
  - [x] Feedback visuel adapté ("🩸 ÉGALITÉ SANGLANTE !").

---

## 🏆 Épic 3 — Système d'Élo FIDE & Matchmaking (`Leaderboard`)
> **Responsable principal :** 🧑‍💻 Mathéo

### [x] T3.1 — Service Mathématique d'Élo Standard FIDE
- **Priorité :** 🔴 P0 (Équilibrage)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/EloService.php`, `tests/Unit/EloServiceTest.php`
- **Description :** Implémenter la formule officielle d'Élo d'échecs (FIDE) avec facteur $K=32$.
- **Critères d'acceptation :**
  - [x] Calcul de la probabilité attendue : $E_A = \frac{1}{1 + 10^{(R_B - R_A)/400}}$.
  - [x] Calcul du delta : $\Delta = \text{round}(K \times (Score - E_A))$.
  - [x] Test des cas limites : victoire contre plus fort (+ fort gain), défaite contre plus faible (- forte perte).
  - [x] Plancher d'Élo fixé à 0 (l'Élo ne peut pas être négatif).

### [x] T3.2 — Système de Ligues dynamiques par Paliers
- **Priorité :** 🔴 P0 (Visuel & Rétention)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Enums/League.php`, `app/Models/User.php`
- **Description :** Catégoriser les joueurs selon leur score d'Élo avec badge et couleur associés.
- **Critères d'acceptation :**
  - [x] Paliers : Bronze (0-999), Argent (1000-1399), Or (1400-1799), Platine (1800-2199), Maître (2200+).
  - [x] Méthode sur `User` : `$user->league` / `$user->getLeague()`.
  - [x] Détection des changements de palier (promotion / relégation).

### [x] T3.3 — Matchmaking équilibré par Plage d'Élo & Pool Hybride (Anti-Farm)
- **Priorité :** 🔴 P0 (Fair-play & Variété)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/ArenaService.php`, `app/Services/EloService.php`, `tests/Feature/MatchmakingTest.php`
- **Description :** Trouver un adversaire réel dont l'Élo est proche de celui du joueur pour des duels équilibrés, avec garantie de diversité et régulation anti-farm.
- **Critères d'acceptation :**
  - [x] Recherche en priorité dans une fenêtre de $\pm 100$ Élo, élargie progressivement à $\pm 250$ Élo.
  - [x] Exclusion stricte des propres personnages du joueur connecté.
  - [x] **Garantie Pool Minimum de 10 Cibles :** Si la base contient moins de 10 cartes de vrais joueurs éligibles, constitution d'un pool hybride complété par des Fantômes d'Arène procéduraux (au moins 10 adversaires distincts).
  - [x] **Anti-Répétition / Anti-Loop :** Dépriorisation / rotation de la carte adverse récemment affrontée lors des derniers combats pour empêcher les boucles de farm unilatérales (ex: 2 comptes de test).
  - [x] **Modulateur de Difficulté de Puissance sur l'Élo :** Prise en compte du ratio de puissance (`power_score`). Gain d'Élo amorti en cas d'écrasement sans risque ($\Delta \approx +4$ à $+8$ si surpuissant), gain équilibré ($\Delta \approx +16$), et bonus récompensant l'exploit d'outsider ($\Delta \approx +22$ à $+28$).
  - [x] **Régulation Économique du Reroll & Anti-Triche F5 :**
    - Persistance en BDD (`has_free_arena_reroll`) : le reroll gratuit n'est rechargé qu'après avoir réellement terminé un combat d'arène. Recharger la page (F5) ne redonne pas de reroll gratuit.
    - Persistance de l'adversaire courant en session : impossible de contourner le coût de 10 🪙 en rafraîchissant la page pour obtenir un autre adversaire sans payer.
    - Synchronisation en direct du HUD (`stats-updated`) : déduction visuelle immédiate des 10 🪙 sans rechargement de page.

### [x] T3.4 — Système de Combattants Fantômes / Bots (Anti-Cold Start & Diversité)
- **Priorité :** 🔴 P0 (Indispensable pour la soutenance)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/BotGeneratorService.php`, `tests/Feature/ArenaGhostTest.php`
- **Description :** Permettre de jouer même si la BDD est vierge de vrais utilisateurs ou avec peu de comptes réels.
- **Critères d'acceptation :**
  - [x] Génération procédurale à la volée de "Fantômes d'Arène" adaptés à la ligue et au palier de puissance du joueur.
  - [x] Nom procédural évocateur ("Gladiateur de l'Ombre", "Garde de Fer", etc.).
  - [x] Le calcul d'Élo et les récompenses s'appliquent normalement pour le joueur.
  - [x] Complétion dynamique du pool de 10 adversaires éligibles en synergie avec le matchmaking.

### [x] T3.5 — Composant Livewire `Leaderboard`
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/Leaderboard.php`, `resources/views/livewire/leaderboard.blade.php`, `resources/views/leaderboard.blade.php`, `tests/Feature/LeaderboardTest.php`
- **Description :** Tableau des meilleurs joueurs de l'arène avec filtres par ligue.
- **Critères d'acceptation :**
  - [x] Podium stylisé pour le Top 3 (or, argent, bronze néon).
  - [x] Ligne épinglée indiquant le rang, l'Élo et la ligue du joueur connecté.
  - [x] Onglets de filtrage par ligue (Tous, Maître, Platine, Or, Argent, Bronze).
  - [x] Cache Laravel (durée 60 secondes) pour préserver les perfs BDD.

### [x] T3.6 — Historique & Replay des Duels
- **Priorité :** 🟢 P2
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/BattleHistory.php`, `resources/views/livewire/battle-history.blade.php`, `tests/Feature/BattleHistoryTest.php`
- **Description :** Historique des 10 derniers combats du joueur avec consultation du journal.
- **Critères d'acceptation :**
  - [x] Liste des combats récents : V/D, adversaire, delta d'Élo, date relative ("il y a 5 min").
  - [x] Bouton "Revoir le combat" qui recharge le `turns_log` dans l'arène.

---

## 🏛️ Épic 4 — Économie Circulaire & Atelier (`Workshop`)
> **Responsable principal :** 🧑‍💻 Mathéo

### [x] T4.1 — Modèle, Migration & Service `WorkshopService`
- **Priorité :** 🔴 P0 (Boucle de rétention V2)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/WorkshopService.php`, `app/Models/WorkshopUpgrade.php`, `tests/Feature/WorkshopServiceTest.php`
- **Description :** Logique d'achat d'améliorations passives permanentes financées par l'or du recyclage et des victoires.
- **Critères d'acceptation :**
  - [x] Définition des améliorations :
    1. *Aimant à Pouvoirs Rares* (Niveaux 1 à 5 : +3% par niveau de chance de pouvoirs épiques/mythiques).
    2. *Forge des Héros* (Niveaux 1 à 5 : +2 Force et +3 PV constants sur les futurs tirages).
    3. *Coffre de Guerre* (Niveaux 1 à 3 : +25%, +50%, +100% d'or par victoire d'arène).
  - [x] Vérification stricte du solde de pièces avant achat et déduction sécurisée.
  - [x] Sauvegarde du niveau de chaque amélioration par utilisateur.
  - [x] Validé par `tests/Feature/WorkshopServiceTest.php` (tests passants).

### [x] T4.2 — Intégration des bonus de l'Atelier dans le Moteur de Génération
- **Priorité :** 🔴 P0 (Cohérence Gameplay)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/CharacterGeneratorService.php`, `tests/Feature/CharacterGeneratorWorkshopTest.php`
- **Description :** Faire en sorte que les améliorations achetées affectent réellement les prochains tirages de roue.
- **Critères d'acceptation :**
  - [x] Injection du niveau de l'Aimant à Pouvoirs pour modifier les probabilités de `PowerData`.
  - [x] Injection du niveau de la Forge des Héros pour ajouter les points de stats bonus lors de la création du personnage.
  - [x] Validé par `tests/Feature/CharacterGeneratorWorkshopTest.php` (tests passants).

### [x] T4.3 — Ticket d'Urgence (Achat de tirage immédiat)
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/WorkshopService.php`, `app/Livewire/CharacterWheel.php`, `tests/Feature/EmergencySpinTest.php`
- **Description :** Donner une utilité immédiate à l'or quand le joueur a vidé ses charges de roue et veut continuer sa session.
- **Critères d'acceptation :**
  - [x] Achat d'une charge de spin pour 120 pièces d'or.
  - [x] Limitation : maximum 2 achats par tranche de 24 heures (coût progressif 120 🪙 $\rightarrow$ 250 🪙 $\rightarrow$ blocage).
  - [x] Validé par `tests/Feature/EmergencySpinTest.php` (tests passants).

### [x] T4.4 — Composant Livewire `Workshop`
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/Workshop.php`, `resources/views/livewire/workshop.blade.php`, `tests/Feature/WorkshopPageTest.php`
- **Description :** Interface moderne de l'Atelier avec arbre de cartes d'améliorations.
- **Critères d'acceptation :**
  - [x] Cartes d'améliorations avec barres de progression de niveau (ex: Niv. 2/5).
  - [x] Bouton d'achat dynamique affichant le coût du niveau suivant.
  - [x] Feedback visuel en cas de fonds insuffisants.
  - [x] Animation de déblocage avec notification de succès.
  - [x] Validé par `tests/Feature/WorkshopPageTest.php` (tests passants).

### [x] T4.5 — Succès & Déblocages Spéciaux (`Achievements`)
- **Priorité :** 🟢 P2
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Models/Achievement.php`, `app/Listeners/ProcessAchievements.php`, `tests/Feature/AchievementTest.php`
- **Description :** Récompenser les accomplissements des joueurs avec des titres et des bourses de pièces.
- **Critères d'acceptation :**
  - [x] Succès "Premier Sang" (1ère victoire en arène).
  - [x] Succès "Maître Alchimiste" (Recycler 10 cartes).
  - [x] Succès "Bénédiction Divine" (Obtenir une carte Légendaire).
  - [x] Succès "Combinaison Instable" (Tirer une synergie à risque).
  - [x] Validé par `tests/Feature/AchievementTest.php` (tests passants).

---

## 🎨 Épic 5 — Game Feel, Rendu Visuel & Paper Doll
> **Responsables :** 👥 Binôme (Nathanaël & Mathéo)

### [x] T5.1 — Système de Rendu Composite "Paper Doll" (Avatar Procédural V2 & Auras d'Armes) & Châssis Responsive
- **Priorité :** 🟡 P1 (Immersion & Note Soutenance)
- **Attribué à :** 👥 Binôme
- **Cible :** `docs/GUIDE_ASSETS_PAPER_DOLL.md`, `docs/COMPENDIUM_GAME_DESIGN_ARMES_ET_SYNERGIES.md`, `app/Services/PaperDollService.php`, `resources/views/components/character-avatar.blade.php`, `resources/views/livewire/collection-grid.blade.php`, `resources/views/livewire/character-wheel.blade.php`, `tests/Feature/PaperDollTest.php`
- **Description :** Générer une représentation graphique unique de chaque héros par superposition de calques WebP sur un portrait carré (512x512), enveloppé dans un châssis de carte réactif (CSS/Tailwind + SVG). Évolution V2 : intégration du Calque 4 des Auras Élémentaires d'Armes (`z-index: 40`), strictement affiché au-dessus de l'arme pour un rendu immersif des affinités élémentaires.
- **Critères d'acceptation :**
  - [x] Spécifications techniques & guide d'export rédigés dans [GUIDE_ASSETS_PAPER_DOLL.md](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/docs/GUIDE_ASSETS_PAPER_DOLL.md).
  - [x] Moteur de superposition dynamique `PaperDollService` et composant réutilisable `<x-character-avatar>` avec fallback résilient (affiche uniquement les calques disponibles sans casser le rendu).
  - [x] **Superposition & Ordre des Calques (Pile à 5 Calques) :**
    - [ ] *Calque 0 :* Fond d'aura / Décor de rareté (`public/images/characters/backgrounds/` - arborescence prête).
    - [x] *Calque 1 :* Silhouette / Corps selon la Race (`public/images/characters/races/`, `z-index: 10`, actif pour `human.webp`).
    - [ ] *Calque 2 :* Effet élémentaire général (`public/images/characters/elements/` - arborescence prête).
    - [x] *Calque 3 :* Arme tenue selon le gabarit d'alignement (`public/images/characters/weapons/`, `z-index: 30`, actifs en anglais : `sword.webp`, `staff.webp`, `bow.webp`, `dagger.webp`, `hammer.webp`, `spear.webp`, `grimoire.webp`).
    - [x] *Calque 4 :* **Aura Élémentaire de l'Arme** (`public/images/characters/weapons/{weapon}_auras/{weapon}_{element}.webp`, `z-index: 40`, strictement au-dessus de l'arme avec micro-mise à l'échelle synchronisée `group-hover/avatar:scale-[1.03]`).
  - [x] **Auras Actives Intégrées (8/8 Épée & 8/8 Bâton) :**
    - Épée élémentaire (8/8) : Feu 🔥 (`sword_fire.webp`), Glace ❄️ (`sword_ice.webp`), Foudre ⚡ (`sword_lightning.webp`), Poison 🧪 (`sword_poison.webp`), Gravité 🌌 (`sword_gravity.webp`), Régénération 💖 (`sword_regeneration.webp`), Téléportation 🌀 (`sword_teleportation.webp`), Invisibilité 👁️‍🗨️ (`sword_invisibility.webp`).
    - Bâton élémentaire (8/8) : Feu 🔥 (`staff_fire.webp`), Glace ❄️ (`staff_ice.webp`), Foudre ⚡ (`staff_lightning.webp`), Poison 🧪 (`staff_poison.webp`), Gravité 🌌 (`staff_gravity.webp`), Régénération 💖 (`staff_regeneration.webp`), Téléportation 🌀 (`staff_teleportation.webp`), Invisibilité 👁️‍🗨️ (`staff_invisibility.webp`).
  - [x] **Règle Spécifique d'Invisibilité Universelle :** L'aura d'invisibilité reproduisant l'outline sans couleurs, le calque de base de l'arme (Layer 3) est masqué automatiquement pour toutes les armes lorsque cet effet est actif afin de préserver l'effet translucide.
  - [x] **Arborescence Préparatoire Complète :** Dossiers créés pour toutes les armes (`sword_auras`, `bow_auras`, `staff_auras`, `dagger_auras`, `hammer_auras`, `spear_auras`, `gun_auras`, `grimoire_auras`) ainsi que `elements/` et `backgrounds/`.
  - [x] **Résolution Bilingue & Tolérante :** Méthode `PaperDollService::getNormalizedCandidates()` gérant de manière bidirectionnelle les alias anglais/français (`epee`/`sword`, `feu`/`fire`, `glace`/`ice`, `foudre`/`lightning`, `baton`/`staff`).
  - [x] **Respect Strict du Ratio & Élimination des Distorsions :** Gabarit carré 1:1 (`max-w-[270px] aspect-square w-full`) interdisant tout écrasement ou étirement d'image.
  - [x] **Détourage & Transparence :** Suppression des encadrements debug et badges temporaires pour une intégration directe sur le fond néon de la carte.
  - [x] Intégration sur la face recto des cartes de la Collection et dans la modale de révélation de la Roue.
  - [x] Châssis de carte responsive (bordure, liserés, halo glow de rareté en pur CSS Tailwind) s'adaptant à n'importe quelle taille de carte sans distorsion.
  - [x] Tests automatisés unitaires & fonctionnels complets (`tests/Feature/PaperDollTest.php` - 7 tests validés, 76 assertions ; suite globale 169 tests, 685 assertions ✅).

### [x] T5.5 — Direction Artistique "Le Vide et la Roue", Respiration Contemplative & Standardisation du Ratio Carte Physique (TCG 1:1.53)
- **Priorité :** 🟢 P2 (Direction Artistique & Polish Visuel Majeur)
- **Attribué à :** 👥 Binôme
- **Cible :** `resources/css/app.css`, `tailwind.config.js`, `resources/views/layouts/*`, `resources/views/welcome.blade.php`, `resources/views/collection.blade.php`, `resources/views/arena.blade.php`, `resources/views/dashboard.blade.php`, `resources/views/livewire/collection-grid.blade.php`, `resources/views/livewire/battle-arena.blade.php`, `resources/views/components/*`, `resources/views/auth/*`, `resources/views/profile/*`
- **Description :** Refonte esthétique complète orientée contemplation, espace et grandeur du vide sans agressivité, et harmonisation stricte du gabarit des cartes à collectionner pour assurer la continuité visuelle physique entre la Collection et l'Arène.
- **Critères d'acceptation :**
  - [x] **Fond Cosmique & Respiration :** Fond avec gradient cosmique profond (`#050a14` $\rightarrow$ `#0a0e1a`), champ d'étoiles procédural (`starfield-drift`), respiration d'orbe orbitale (`cosmos-breathe`), scrollbar discrète et correction du défilement vertical (`overflow-x: hidden`).
  - [x] **Typographie & Titres Majestueux :** Intégration de la police `Outfit` en 800 pour les titres dorés `.heading-majestic` et dégradés élémentaires, police `Inter` avec interligne 1.75 pour les textes narratifs aérés.
  - [x] **Hero Section Contemplatif :** Page d'accueil (`welcome.blade.php`) monumentale avec titre 8xl, grand espacement vertical contemplatif et cartes piliers flottantes.
  - [x] **Harmonisation UI Complète :** Refonte dark glassmorphism de toutes les pages d'authentification (login, register, reset, verification), de l'espace profil et de l'ensemble des composants Blade de base (`text-input`, `primary-button`, `secondary-button`, `danger-button`, `modal`, `nav-link`, `responsive-nav-link`).
  - [x] **Standardisation du Ratio Carte Physique (TCG ~1 : 1.53) :**
    - [x] Rétablissement du conteneur large `max-w-7xl` sur les vues principales (Collection, Arène, Dashboard) pour redonner aux 4 colonnes leur largeur naturelle (~289px).
    - [x] Calibrage de la hauteur des cartes de collection de 490px à 440px (grille et modale zoom inspecteur) pour éliminer l'effet étiré/trop haut (ratio TCG équilibré 1 : 1.52).
    - [x] Refonte intégrale du catalogue de sélection de héros de l'Arène (`battle-arena.blade.php`) : remplacement du format bouton carré 250px par une vraie carte miniature proportionnelle (hauteur 305px, ratio 1 : 1.55) avec châssis `rounded-3xl`, lueur de rareté, étoiles ★, en-tête de puissance et avatar Paper Doll valorisé (135px) pour préserver la continuité physique de la carte.
    - [x] Harmonisation des cartes déployées en duel 1v1 à 270px × 415px (ratio 1 : 1.54).
  - [x] **Validation & Tests :** 100% de la suite de tests automatisés au vert (164 tests passés, 621 assertions) et compilation Vite sans avertissement (`✓ built in 1.62s`).


### [ ] T5.2 — Ambiance Sonore & Haptique (SFX optionnels)
- **Priorité :** 🟢 P2 (Polish)
- **Attribué à :** 👥 Binôme
- **Cible :** `resources/js/audio-manager.js`
- **Description :** Ajouter des feedbacks sonores discrets et désactivables pour amplifier les moments forts.
- **Critères d'acceptation :**
  - [ ] Bruit de cliquetis mécanique lors de la rotation de la roue.
  - [ ] Fanfare d'impact différente selon la rareté lors de la révélation.
  - [ ] Bruit de pièces métalliques lors du recyclage.
  - [ ] Bouton toggle "Mute / Son On" persistant dans le `localStorage`.

### [ ] T5.3 — Micro-animations Alpine.js (Screen Shake & Confettis)
- **Priorité :** 🟡 P1
- **Attribué à :** 👥 Binôme
- **Cible :** `resources/views/livewire/character-wheel.blade.php`, `resources/views/livewire/battle-arena.blade.php`
- **Description :** Donner du punch à l'interface lors des événements critiques.
- **Critères d'acceptation :**
  - [ ] Secousse d'écran (`screen-shake`) sur les coups critiques en arène.
  - [ ] Éclatement de confettis dorés légers lors du déclenchement du Super Spin 10/10.
  - [ ] Animation de pulsation rouge lors des coups portés.

### [ ] T5.4 — Le Codex / Compendium des Connaissances
- **Priorité :** 🟢 P2 (Clarté académique)
- **Attribué à :** 👥 Binôme
- **Cible :** `resources/views/codex.blade.php`, `routes/web.php`
- **Description :** Une encyclopédie intégrée au jeu listant toutes les règles, races, armes, pouvoirs et combinaisons de synergies.
- **Critères d'acceptation :**
  - [ ] Fiches des 8 races avec modificateurs de stats.
  - [ ] Fiches des 8 armes avec types de dégâts.
  - [ ] Fiches des 8 pouvoirs classés par rareté.
  - [ ] Tableau des 16 synergies (dont les 4 combinaisons instables) avec statut "Découvert" ou "Mystère".

---

## 🛡️ Épic 6 — Robustesse, Concurrence & Edge Cases
> **Responsable principal :** 🧑‍💻 Nathanaël

### [x] T6.1 — Reprise automatique d'un tirage non finalisé (`pending_reveal`)
- **Priorité :** 🔴 P0 (Bug Critique / Expérience Utilisateur)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `tests/Feature/CharacterWheelTest.php`
- **Description :** Si l'utilisateur quitte la page après avoir cliqué sur "Spin" mais avant d'avoir choisi "Garder" ou "Recycler", le tirage ne doit pas être perdu.
- **Critères d'acceptation :**
  - [x] Lors du `mount()`, vérification en BDD si un personnage de l'utilisateur a `pending_reveal = true` et non recyclé via `checkPendingReveal()`.
  - [x] Si oui, chargement immédiat de ce personnage dans `$revealedCharacter`, notification de reprise et réouverture automatique de la modale.
  - [x] Empêchement strict de relancer un nouveau spin tant que le précédent n'a pas été validé ou recyclé (bouton spin désactivé avec message "⚠️ Finalisez le tirage ci-dessous" et blocage backend).
  - [x] Suite de tests automatisés validant la restauration automatique et le blocage d'un nouveau tirage.

### [x] T6.2 — Transactions Atomiques et Protection Multi-clic
- **Priorité :** 🔴 P0 (Sécurité & Intégrité des données)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Services/RecycleService.php`, `app/Livewire/CharacterWheel.php`, `app/Livewire/CollectionGrid.php`, `resources/views/livewire/character-wheel.blade.php`, `resources/views/livewire/collection-grid.blade.php`, `tests/Feature/CharacterWheelTest.php`
- **Description :** Éviter les failles de duplication d'or, de cartes ou de tirages en cas de clics frénétiques ou requêtes concurrentes.
- **Critères d'acceptation :**
  - [x] Encapsuler le spin, le recyclage (unitaire et masse) et le verrouillage dans des transactions `DB::transaction(...)`.
  - [x] Verrouillage pessimiste strict `lockForUpdate()` sur la ligne de l'utilisateur (`User`) pour interdire toute concurrence sur le solde de pièces ou les charges d'énergie.
  - [x] Verrouillage pessimiste `lockForUpdate()` sur les cartes (`Character`) pour interdire tout double recyclage simultané.
  - [x] Contrôle d'appartenance strict (`where('user_id', $user->id)`) et interdiction formelle de recycler un héros déployé dans l'arène (`is_active_fighter`).
  - [x] Attributs Blade `wire:loading.attr="disabled"` et indicateurs visuels animés sur tous les boutons d'action monétaire et de mutation (Spin, Garder, Recycler, Verrouiller, Recyclage en masse).
  - [x] Tests unitaires et d'intégration validant les transactions, la protection contre l'usurpation et les cas limites.

### [x] T6.3 — Calcul précis du rattrapage temporel (Catch-Up Time) & Correction Carbon 3
- **Priorité :** 🔴 P0 (Énergie)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Services/EnergyManagerService.php`, `tests/Feature/EnergyManagerTest.php`
- **Description :** Assurer que la régénération de charges s'effectue sans décalage ni blocage même après plusieurs jours (résolution du bug de différence négative Carbon 3).
- **Critères d'acceptation :**
  - [x] Calcul mathématique par timestamp : `elapsed = max(0, now()->timestamp - last_regen->timestamp)`.
  - [x] Plafonnement strict au maximum légal (`max_charges = 3`).
  - [x] Mise à jour correcte du timestamp pour ne pas perdre les secondes résiduelles (`addSeconds`).
  - [x] Initialisation propre si le joueur a moins de 3 charges sans timestamp posé.

### [x] T6.4 — Compte à rebours dynamique de recharge & synchronisation Livewire
- **Priorité :** 🔴 P0 (UX / Feedback)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `resources/js/app.js`
- **Description :** Affichage d'un timer précis (mm:ss) uniquement dans la carte des charges avec mise à jour automatique en temps réel.
- **Critères d'acceptation :**
  - [x] Pré-rendu direct en PHP (`sprintf('%02d:%02d', ...)`) pour garantir zéro texte vide ou `NaN` avant l'exécution JS.
  - [x] Compte à rebours animé fluide côté client via Alpine.js (`wire:key` isolé, sans `$watch` défaillant) dans la carte des charges.
  - [x] Suppression de l'instance manuelle `Alpine.start()` dans `resources/js/app.js` pour éliminer le conflit de double instance avec Livewire 3.
  - [x] Suppression des doublons d'affichage pour une interface épurée.
  - [x] Déblocage immédiat du bouton dès qu'une charge devient disponible.

### [x] T6.5 — Composant Livewire réactif `PlayerHud` (Synchronisation temps réel du solde de pièces)
- **Priorité :** 🔴 P0 (Économie & UI réactive)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/PlayerHud.php`, `resources/views/livewire/player-hud.blade.php`, `resources/views/layouts/navigation.blade.php`, `tests/Feature/PlayerHudTest.php`
- **Description :** Rendre la barre HUD de navigation réactive en temps réel via des événements Livewire (`stats-updated`) pour refléter immédiatement les gains de pièces (recyclage), les dépenses et les variations d'Élo sans recharger la page.
- **Critères d'acceptation :**
  - [x] Extraction du HUD statique de `navigation.blade.php` vers un composant Livewire `PlayerHud`.
  - [x] Double réactivité instantanée : gestion polyvalente du payload d'événement (`array` ou `object`) + écouteur Livewire `#[On('stats-updated')]`.
  - [x] Animation visuelle (bump / scale doré) lors de la mise à jour des pièces pour un feedback clair et immédiat au joueur.
  - [x] Émission automatique avec payload complet (`coins`, `spinCharges`, `elo`) depuis `CharacterWheel` et `CollectionGrid` lors de chaque recyclage ou spin.
  - [x] Régénération automatique de l'énergie de spin dès le chargement du HUD sur n'importe quelle page du site (catalogue, profil, etc.) sans dépendre de la visite préalable sur la roue.
  - [x] Affichage formaté des pièces (`Intl.NumberFormat`) et icône dynamique de ligue.

### [x] T6.6 — Unicité Stricte du Pseudo (`name`), Résolution des Doublons & Connexion Hybride (Email / Pseudo)
- **Priorité :** 🔴 P0 (Sécurité des comptes, Intégrité BDD & UX)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/migrations/2026_10_23_211500_make_name_unique_in_users_table.php`, `app/Http/Controllers/Auth/RegisteredUserController.php`, `app/Http/Requests/ProfileUpdateRequest.php`, `app/Http/Requests/Auth/LoginRequest.php`, `resources/views/auth/login.blade.php`, `tests/Feature/Auth/RegistrationTest.php`, `tests/Feature/Auth/AuthenticationTest.php`
- **Description :** Empêcher strictement la création de comptes avec des pseudonymes identiques, assainir les doublons existants et permettre la connexion indifféremment via email ou pseudo.
- **Critères d'acceptation :**
  - [x] **Migration BDD d'unicité avec auto-résolution :** Script de migration sécurisé qui regroupe les doublons existants par date de création, préserve le compte originel (ex: `NathOof` ID 3) et suffixe automatiquement les doublons (ex: `NathOof_2` ID 4) avant d'appliquer l'index `$table->unique('name')`.
  - [x] **Validation multi-niveaux à l'inscription et profil :** Règle `unique:users,name` avec messages d'erreur explicites en français ("Ce pseudonyme est déjà pris par un autre joueur. Veuillez en choisir un autre.") dans `RegisteredUserController` et `ProfileUpdateRequest`.
  - [x] **Connexion hybride (Email ou Pseudo) :** `LoginRequest` détecte automatiquement si l'identifiant saisi est une adresse email (`filter_var(..., FILTER_VALIDATE_EMAIL)`) ou un pseudonyme (`name`) et authentifie le joueur sans nécessiter son email s'il l'a oublié.
  - [x] **Refonte UX du formulaire de connexion :** Champ `login.blade.php` mis à jour en `Email ou Pseudo` avec placeholder explicite et look glassmorphism néon.
  - [x] **Tests automatisés :** Ajout de `test_user_cannot_register_with_duplicate_name` et `test_users_can_authenticate_using_their_username` (166 tests passants au total, 100% au vert).

---

## 🎓 Épic 7 — Outils Soutenance, Admin & Démo Universitaire
> **Responsable principal :** 🧑‍💻 Nathanaël

### [ ] T7.1 — Commande Artisan de Démo Clé en Main (`php artisan game:demo-setup`)
- **Priorité :** 🔴 P0 (Soutenance)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Console/Commands/GameDemoSetupCommand.php`
- **Description :** Commande unique permettant de préparer le projet en 5 secondes avant le passage devant le jury.
- **Critères d'acceptation :**
  - [ ] Réinitialise et peuple la base avec un environnement vivant.
  - [ ] Crée 3 comptes de démonstration :
    - `bronze@wheelborn.test` (Débutant, 950 Élo, 2 cartes).
    - `argent@wheelborn.test` (Joueur intermédiaire, 1250 Élo, 8 cartes).
    - `admin@wheelborn.test` (Compte Maître, 2300 Élo, atelier maxé, ressources illimitées).
  - [ ] Génère 40 cartes dans l'arène pour avoir un classement complet et des adversaires immédiats.

### [x] T7.2 — Panneau de Débogage / God Mode dans l'Interface (Barre Admin & Forge Personnalisée)
- **Priorité :** 🟡 P1 (Soutenance)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `app/Services/CharacterGeneratorService.php`, `tests/Feature/PaperDollTest.php`
- **Description :** Barre d'administration interactive et outil de Forge Personnalisée réservés aux administrateurs pour forcer, simuler et démontrer n'importe quelle combinaison de carte, d'effet visuel ou de synergie en direct devant le jury.
- **Critères d'acceptation :**
  - [x] Visible uniquement si `$isAdmin === true`.
  - [x] **Ressources Illimitées (God Mode) :** Charges de roue et tickets d'arène à 999, or à 999 999 🪙.
  - [x] **Raccourcis Démo Rapides :**
    - Bouton "🌟 Forcer Super Spin (10/10)" (démonstration garantie de l'animation dorée au jury).
    - Bouton "+50 000 🪙" (pour tester immédiatement le recyclage et les transactions).
    - Bouton "Reset Pity" (remise à zéro instantanée du compteur de pitié).
  - [x] **Outil Avancé "Forge Personnalisée & Simulateur de Roue" (`showAdminForge`) :**
    - 4 sélecteurs déroulants dynamiques : Race (8 races), Arme (8 armes), Pouvoir Élémentaire (8 pouvoirs), Rareté (Aléatoire ou fixée : Commune, Rare, Épique, Légendaire).
    - Détection en temps réel des assets Paper Doll avec pastilles d'état (Race ✅, Arme ✅, Aura ✅ Présente ou ⚠️ Absente avec nom de fichier cible attendu).
    - Détection et affichage en temps réel des synergies d'armes et d'éléments (`SynergyData::all()`).
    - Prévisualisation live du rendu `<x-character-avatar>` avant invocation.
    - Deux modes d'invocation :
      - 🎯 **Révélation Directe (`adminSpawnCustom(false)`)** : invocation instantanée pour tester les calques et visuels sans délai.
      - 🎡 **Lancer la Roue Synchronisée (`adminSpawnCustom(true)`)** : lance l'animation physique des 3 cadrans de la Roue pour s'arrêter précisément sur les éléments forgés.
    - Méthode de génération dédiée `CharacterGeneratorService::generateCustom()` instanciant un combattant cohérent (stats calculées selon la race/arme, rareté et valeur de recyclage).
    - Rétro-compatibilité assurée avec `adminForceTestSpin()`.

### [x] T7.3 — Seeder de Base de Données Bimodal (Admin Démo & Joueur UX Standard)
- **Priorité :** 🔴 P0 (Soutenance & Prise en main collaborateur)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/seeders/DatabaseSeeder.php`, `README.md`
- **Description :** Seeder bimodal préparant automatiquement la base de données pour deux cas d'usage distincts : démonstration complète (Admin) et test d'onboarding vierge (Joueur UX).
- **Critères d'acceptation :**
  - [x] **Compte Admin (`admin@wheelborn.test` / `password`)** : 999 999 🪙, 999 charges, barre d'outils admin.
  - [x] **5 cartes de démonstration assignées uniquement à l'Admin** :
    1. `Valérian Brise-Fer` (Humain + Épée + Feu, Rare, calque Paper Doll arme 1).
    2. `Syrus l'Archiviste` (Humain + Bâton + Foudre, Épique, calque Paper Doll arme 2, verrouillé 🔒).
    3. `Malakor le Dévoreur` (Démon + Marteau + Feu, Légendaire, synergie démoniaque).
    4. `Sylvana Vente-Noire` (Elfe + Arc + Poison, Épique, synergie naturelle).
    5. `Thorgal Poing-de-Roc` (Nain + Hache + Terre, Commune).
  - [x] **Compte Joueur Normal (`player@wheelborn.test` / `password`)** : Collection vierge (0 carte), 250 pièces, 3 charges standard, 5 tickets d'arène (expérience utilisateur débutant).
  - [x] Idempotence totale via `updateOrCreate` pour éviter les duplications lors des ré-exécutions de `php artisan db:seed`.

---

## 🧪 Épic 8 — Tests Automatisés & Assurance Qualité (PHPUnit)
> **Responsables :** 👥 Répartition selon les modules

### [x] T8.1 — Tests du Composant Livewire `CharacterWheel`
- **Priorité :** 🔴 P0
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `tests/Feature/Livewire/CharacterWheelTest.php`
- **Statut :** ✅ Validé (15 tests passants).

### [ ] T8.2 — Tests Unitaires du Moteur de Génération & Raretés
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `tests/Unit/CharacterGeneratorServiceTest.php`
- **Critères d'acceptation :**
  - [ ] Vérifier que le 10ème spin (Super Spin) ne produit JAMAIS de carte Commune.
  - [ ] Tester que le score Power calculé correspond bien à la formule du GDD.
  - [ ] Valider la détection des synergies selon la table de correspondance.

### [x] T8.3 — Tests Unitaires de la Formule d'Élo FIDE
- **Priorité :** 🔴 P0
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `tests/Unit/EloServiceTest.php`
- **Statut :** ✅ Validé (10 tests passants).
- **Critères d'acceptation :**
  - [x] Élo égal (1000 vs 1000) : gain de +16 en victoire, perte de -16 en défaite.
  - [x] Victoire outsider (1000 vs 1300) : gain élevé (> +24).
  - [x] L'Élo ne descend jamais en dessous de zéro.
  - [x] Pondération par ratio de puissance (anti-farm & valorisation outsider).

### [x] T8.4 — Tests de Robustesse des Transactions de Recyclage, Collection & Paper Doll
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `tests/Feature/CollectionGridTest.php`, `tests/Feature/PaperDollTest.php`
- **Critères d'acceptation :**
  - [x] Recycler une carte supprime bien l'enregistrement ou le marque recyclé et crédite le montant exact d'or (+ pièces).
  - [x] Impossible de recycler la carte d'un autre joueur (sécurité d'appartenance `user_id`).
  - [x] Impossible de recycler une carte verrouillée (`is_locked = true` - exception levée et bouton protégé).
  - [x] Recyclage en masse multi-critères avec transaction atomique et protection des héros verrouillés.
  - [x] Résolution et superposition dynamique des calques Paper Doll (Race, Arme, Aura d'arme Layer 4 bilingue fr/en, avatar Blade) et invocation Forge Admin (`tests/Feature/PaperDollTest.php` - 6 tests, 45 assertions).
  - [x] Suite complète validée (100% passante).

### [x] T8.5 — Tests Unitaires & Feature du Moteur de Combat & BattleArena
- **Priorité :** 🔴 P0 (Intégrité du jeu & Anti-triche)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `tests/Unit/BattleEngineServiceTest.php`, `tests/Feature/BattleArenaTest.php`
- **Critères d'acceptation :**
  - [x] Déterminisme absolu de la seed aléatoire (`Mt19937`).
  - [x] Tests unitaires complets des passifs et mécaniques : saignement/brûlure DoT, étourdissement marteau, régénération/vol de vie vampire, atténuation bouclier arcanique, initiative avec tirage si égalité.
  - [x] Sécurité limite 25 tours max et gestion du double K.O. (égalité).
  - [x] Test de conformité anti-triche : `isAuthentic()` valide un replay et rejette toute falsification.
  - [x] Tests d'intégration Livewire : chargement de l'arène, régénération et blocage à 0 ticket, renouvellement d'adversaire bot (`newOpponent`), calcul des jauges de confrontation dynamiques et réinitialisation par revanche (`rematch`).
  - [x] Suite validée (18/18 tests arène & moteur passants, 78 tests globaux).


---

## 🛡️ Épic 9 — Profil Joueur, Niveaux, Vitrine & Social (`UserProfile`)
> **Responsable principal :** 🧑‍💻 Nathanaël

### [x] T9.1 — Système de Niveaux, Formule d'XP & Migration des Favoris (⭐)
- **Priorité :** 🔴 P0 (Fondation de Profil)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/migrations/2026_10_27_170000_add_is_favorite_to_characters_table.php`, `app/Models/User.php`, `app/Models/Character.php`, `app/Data/GameConfig.php`, `tests/Feature/LevelProgressionTest.php`
- **Description :** Établir la formule mathématique du niveau joueur selon son XP, et ajouter le champ `is_favorite` pour le système de cartes phares (top 3 vitrine).
- **Critères d'acceptation :**
  - [x] Migration BDD : colonne `is_favorite` (booléen, défaut `false`, indexé) sur la table `characters`.
  - [x] Méthode sur `User` : `$user->currentLevelProgress()` calculant le niveau actuel, l'XP du palier en cours, le seuil suivant et le pourcentage de progression (ex: 50%).
  - [x] Formule de paliers définie : Seuil $\text{XP}(N) = 25 \times (N-1) \times (N+2)$ avec paliers progressifs de +50 XP par niveau (100, 150, 200, 250...).
  - [x] Méthode `$user->addXp(int $amount)` centralisant le gain d'XP, calculant le niveau et détectant le level-up.
  - [x] Protection des cartes favorites dans `RecycleService` (rejet unitaire et exclusion stricte du recyclage en masse).
  - [x] Relation `$user->favoriteCharacters()` plafonnée à 3 cartes.
  - [x] Suite de tests validée (`tests/Feature/LevelProgressionTest.php` - 6 tests, 38 assertions).

### [x] T9.2 — Gestion des Favoris dans `CollectionGrid` (Quota de 3 Max)
- **Priorité :** 🔴 P0 (Cohérence Collection)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `app/Services/RecycleService.php`, `tests/Feature/CollectionGridTest.php`
- **Description :** Permettre au joueur d'étoiler jusqu'à 3 cartes favorites directement depuis sa collection pour constituer sa vitrine de profil.
- **Critères d'acceptation :**
  - [x] Bouton interactif étoile (⭐/☆) sur chaque carte de la collection (recto badge, verso bouton) et dans l'inspecteur.
  - [x] Règle métier stricte : 3 cartes favorites maximum simultanées par utilisateur.
  - [x] Notification explicite si tentative d'ajouter une 4e carte ("Vitrine pleine ! Tu as déjà 3 héros favoris sélectionnés").
  - [x] Protection anti-recyclage inviolable : une carte en favori ne peut pas être recyclée (bouton transformé en badge sécurisé `⭐ Favori`).
  - [x] Filtre rapide "⭐ Favoris (X/3)" dans la barre d'outils de la grille de collection.
  - [x] Suite de tests automatisés validant la bascule, le quota de 3 et le filtre (`tests/Feature/CollectionGridTest.php` - 20 tests passants).

### [x] T9.3 — Refonte Majeure de la Page Profil (`/profile`) en Deux Onglets
- **Priorité :** 🔴 P0 (Expérience Utilisateur Majeure)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/UserProfile.php`, `resources/views/livewire/user-profile.blade.php`, `resources/views/profile/edit.blade.php`, `resources/views/components/character-modal.blade.php`, `routes/web.php`
- **Description :** Remplacer la page brute de réglages Breeze par une véritable Fiche de Destinée & Palmarès complète avec onglet de paramètres de compte et composant d'inspection de héros unifié.
- **Critères d'acceptation :**
  - [x] **Bannière Joueur Noble (Hero Header) :**
    - Blason animé de Ligue FIDE (Bronze, Argent, Or, Platine, Maître).
    - Pseudo, Titre honorifique sélectionnable parmi les succès débloqués (ex: « Élu des Dieux »).
    - Jauge d'XP animée avec niveau, texte explicite ("160 / 228 XP") et pourcentage.
    - Élo FIDE, Solde d'or, Date d'inscription et nombre total de héros en collection.
  - [x] **Vitrine des 3 Combattants Phares & 3D Flip Bidirectionnel :**
    - Affichage des 3 cartes favorites au format physique TCG avec rendu Paper Doll V2 et halos de rareté.
    - Animation de retournement 3D fluide et bidirectionnelle au clic (recto $\leftrightarrow$ verso au clic sur le corps de carte ou bouton `🔄`, sans conflit avec les boutons d'action).
    - État vide incitatif ("Emplacement libre — Étoile un héros dans ta collection").
  - [x] **Section Statistiques de Carrière & Calcul Centralisé des Duels :**
    - Centralisation du calcul des statistiques de duels via `$user->battleStats()` dans `User.php` : résout les incohérences de comparaison d'UUID `winner_id` et garantit un calcul exact du total de duels, victoires réelles, défaites, matchs nuls et taux de victoire (Winrate %) partagé entre Arène, Profil, Profil Public et Classement.
    - Collection & Roue : Total cartes tirées, Cartes Légendaires obtenues, Synergies découvertes (X/16).
    - Économie : Total d'or amassé, Cartes recyclées, Niveau de l'Atelier.
  - [x] **Composant Partagé d'Inspection de Carte `<x-character-modal />` :**
    - Extraction d'un composant Blade réutilisable factorisant ~425 lignes de code dupliqué, partagé entre `collection-grid`, `user-profile` et `public-player-profile`.
    - Colonne gauche : carte physique TCG (440px) avec Paper Doll composite, 3D flip (recto/verso), jauges de stats et synergies actives.
    - Colonne droite : passifs innés détaillés des 3 composants (Race, Arme, Pouvoir) avec icônes et effets, liste complète des synergies élémentaires actives sans coupure, et historique d'arène (V/D, winrate).
    - Actions contextuelles en bas de modale : bascule favori (⭐), verrouillage (🔒), recyclage avec confirmation en mode propriétaire, ou vue consultation pure (`readOnly`).
  - [x] **Galerie des Trophées & Succès :**
    - Grille des succès (`Achievement::DEFINITIONS`). Badges colorés dorés pour les succès obtenus avec date, cartes grisées avec cadenas pour les succès verrouillés.
  - [x] **Onglet Paramètres de Compte Intégré :**
    - Onglet séparé fluide permettant de modifier ses identifiants (pseudo, email), son mot de passe ou supprimer son compte (formulaires Breeze intégrés dans le style cosmique).

### [x] T9.4 — Page Profil Public (`/players/{name}`) & Modale d'Inspection Rival
- **Priorité :** 🟡 P1 (Fonctionnalité Sociale & Immersion)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/PublicPlayerProfile.php`, `resources/views/livewire/public-player-profile.blade.php`, `resources/views/livewire/battle-arena.blade.php`, `resources/views/livewire/leaderboard.blade.php`, `routes/web.php`
- **Description :** Permettre de consulter le profil d'un autre joueur en mode lecture seule ou via une modale rapide depuis l'Arène et le Classement.
- **Critères d'acceptation :**
  - [x] Route dédiée `/players/{user:name}` affichant le profil public d'un joueur (bannière, niveau, 3 cartes favorites, stats et succès) sans accès aux paramètres de compte.
  - [x] Modale d'inspection rival dans l'Arène : clic sur le pseudo de l'adversaire (vrai joueur ou bot) ouvre une carte d'identité popup affichant son palmarès, sa ligue et ses héros de prédilection.
  - [x] Clic sur une ligne du Classement (`Leaderboard`) permettant d'inspecter le profil du joueur rival.
  - [x] Réutilisation du composant unifié `<x-character-modal :read-only="true" />` pour inspecter les héros du rival en toute sécurité.

### [x] T9.5 — Tests Automatisés & Validation QA du Profil
- **Priorité :** 🟡 P1 (Qualité)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `tests/Feature/UserProfileTest.php`, `tests/Feature/CollectionGridTest.php`, `tests/Feature/LevelProgressionTest.php`
- **Critères d'acceptation :**
  - [x] Test de limitation stricte à 3 cartes favorites (rejet ou blocage de la 4e).
  - [x] Test de la progression de niveau et calcul d'XP.
  - [x] Test d'accès au profil personnel et redirection/affichage du profil public.
  - [x] Test de protection des cartes favorites contre le recyclage unitaire et de masse.
  - [x] Test d'exactitude du calcul des duels d'arène, victoires, défaites et winrate dans le profil (`test_user_profile_accurately_calculates_and_displays_duels_wins_losses_and_winrate`).
  - [x] Test d'ouverture de la fiche détaillée d'inspection avec composants, passifs innés et synergies (`test_user_profile_modal_opens_full_detailed_sheet_with_passives_and_synergies`).
  - [x] Suite de tests `tests/Feature/UserProfileTest.php` validée à 100% (11 tests passants, 53 assertions).
  - [x] Validation globale de la suite de tests du projet : 189 tests, 784 assertions, 100% au vert.

---

## 🗺️ Ordre de Développement Conseillé (Roadmap Étape par Étape)

```mermaid
graph TD
    A["Épic 0 : Fondations & Roue ✅"] --> B["Nathanaël : Épic 1 - CollectionGrid & Lock ✅"]
    A --> C["Mathéo : Épic 2 - Moteur de Combat & Tickets ✅"]
    C --> D["Mathéo : Épic 3 - Élo FIDE, Bots & Leaderboard ✅"]
    B --> E["Nathanaël : Épic 6 - Robustesse & Pending Reveal ✅"]
    C --> F["Mathéo : Épic 4 - Atelier & Économie d'Or ✅"]
    B & C --> G["Binôme : Épic 5 - Paper Doll & Polish Visuel ⏳"]
    B & D --> J["Nathanaël : Épic 9 - Profil Joueur, Niveaux & Vitrine ✅"]
    E & F & J --> H["Nathanaël : Épic 7 - Démo Soutenance & God Mode ⏳"]
    H --> I["Binôme : Épic 8 - Validation Tests PHPUnit (189 tests ✅)"]
```

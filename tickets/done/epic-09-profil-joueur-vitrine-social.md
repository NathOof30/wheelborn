# 🛡️ Épic 9 — Profil Joueur, Niveaux, Vitrine & Social (`UserProfile`)

> **Statut :** ✅ Terminé (100% - 5/5 tickets validés)  
> **Responsable principal :** 🧑‍💻 Nathanaël  

---

### [x] T9.1 — Système de Niveaux, Formule d'XP & Migration des Favoris (⭐)
- **Priorité :** 🔴 P0 (Fondation de Profil)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/migrations/2026_10_27_170000_add_is_favorite_to_characters_table.php`, `app/Models/User.php`, `tests/Feature/LevelProgressionTest.php`
- **Réalisations validées :**
  - [x] Migration BDD : colonne `is_favorite` (booléen, indexé) sur la table `characters`.
  - [x] Formule de progression mathématique : Seuil $\text{XP}(N) = 25 \times (N-1) \times (N+2)$ avec paliers progressifs de +50 XP par niveau (100, 150, 200, 250...).
  - [x] Méthode `$user->currentLevelProgress()` calculant le niveau actuel, l'XP du palier et le pourcentage de jauge.
  - [x] Méthode `$user->addXp(int $amount)` centralisant le gain d'XP et détectant le level-up.
  - [x] Protection des cartes favorites dans `RecycleService` (rejet unitaire et exclusion stricte du recyclage en masse).
  - [x] Relation `$user->favoriteCharacters()` plafonnée à 3 cartes.
  - [x] Validé par `tests/Feature/LevelProgressionTest.php` (6 tests, 38 assertions).

---

### [x] T9.2 — Gestion des Favoris dans `CollectionGrid` (Quota de 3 Max)
- **Priorité :** 🔴 P0 (Cohérence Collection)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `tests/Feature/CollectionGridTest.php`
- **Réalisations validées :**
  - [x] Bouton interactif étoile (⭐/☆) sur chaque carte (recto badge, verso bouton) et dans l'inspecteur.
  - [x] Quota strict : 3 cartes favorites maximum simultanées par utilisateur.
  - [x] Notification explicite si tentative d'ajouter une 4e carte ("Vitrine pleine ! Tu as déjà 3 héros favoris sélectionnés").
  - [x] Protection anti-recyclage inviolable : une carte favorite ne peut pas être recyclée (bouton transformé en badge sécurisé `⭐ Favori`).
  - [x] Filtre rapide "⭐ Favoris (X/3)" dans la barre d'outils de collection.
  - [x] Validé par `tests/Feature/CollectionGridTest.php` (20 tests passants).

---

### [x] T9.3 — Refonte Majeure de la Page Profil (`/profile`) en Deux Onglets
- **Priorité :** 🔴 P0 (Expérience Utilisateur Majeure)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/UserProfile.php`, `resources/views/livewire/user-profile.blade.php`, `resources/views/profile/edit.blade.php`, `resources/views/components/character-modal.blade.php`
- **Réalisations validées :**
  - [x] **Bannière Joueur Noble (Hero Header) :**
    - Blason animé de Ligue FIDE, pseudo, titre honorifique sélectionnable parmi les succès débloqués.
    - Jauge d'XP animée avec niveau, texte explicite ("160 / 228 XP") et pourcentage.
    - Élo FIDE, Solde d'or, Date d'inscription et total de combattants.
  - [x] **Vitrine des 3 Combattants Phares & 3D Flip Bidirectionnel :**
    - Affichage des 3 cartes favorites au format physique TCG avec rendu Paper Doll V2 et halos de rareté.
    - Animation de retournement 3D fluide et bidirectionnelle au clic (recto $\leftrightarrow$ verso au clic sur le corps de carte ou bouton `🔄`, sans conflit avec les boutons d'action).
    - État vide incitatif ("Emplacement libre — Étoile un héros dans ta collection").
  - [x] **Statistiques de Carrière & Calcul Centralisé des Duels :**
    - Centralisation du calcul des statistiques de duels via `$user->battleStats()` dans `User.php` : résout les incohérences de comparaison d'UUID `winner_id` et garantit un calcul exact du total de duels, victoires réelles, défaites, matchs nuls et taux de victoire (Winrate %) partagé entre Arène, Profil, Profil Public et Classement.
    - Statistiques de Collection & Roue (cartes tirées, légendaires, synergies).
    - Statistiques d'Économie (or amassé, cartes recyclées, niveau atelier).
  - [x] **Composant Partagé d'Inspection de Carte `<x-character-modal />` :**
    - Extraction d'un composant Blade réutilisable factorisant ~425 lignes de code dupliqué, partagé entre `collection-grid`, `user-profile` et `public-player-profile`.
    - Colonne gauche : carte physique TCG (440px) avec Paper Doll composite, 3D flip (recto/verso), jauges de stats et synergies actives.
    - Colonne droite : passifs innés détaillés des 3 composants (Race, Arme, Pouvoir) avec icônes et effets, liste complète des synergies actives sans coupure, et historique d'arène (V/D, winrate).
    - Actions contextuelles : ⭐ Favori, 🔒 Verrouiller, 🪙 Recycler ou mode lecture seule (`readOnly`).
  - [x] **Galerie des Trophées & Succès :** Badges dorés pour les succès obtenus avec date, cartes grisées verrouillées.
  - [x] **Onglet Paramètres de Compte Intégré :** Formulaires Breeze (pseudo, email, mot de passe, suppression de compte) intégrés au style cosmique sombre.

---

### [x] T9.4 — Page Profil Public (`/players/{name}`) & Modale d'Inspection Rival
- **Priorité :** 🟡 P1 (Fonctionnalité Sociale & Immersion)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/PublicPlayerProfile.php`, `resources/views/livewire/public-player-profile.blade.php`, `resources/views/livewire/battle-arena.blade.php`, `resources/views/livewire/leaderboard.blade.php`, `routes/web.php`
- **Réalisations validées :**
  - [x] Route dédiée `/players/{user:name}` affichant le profil public d'un joueur (bannière, niveau, 3 cartes favorites, stats et succès) en lecture seule sécurisée.
  - [x] Modale d'inspection rival dans l'Arène : clic sur le pseudo de l'adversaire (vrai joueur ou bot) ouvre une carte d'identité popup affichant son palmarès et ses héros de prédilection.
  - [x] Clic sur une ligne du Classement (`Leaderboard`) permettant d'inspecter le profil du joueur rival.
  - [x] Réutilisation du composant unifié `<x-character-modal :read-only="true" />`.

---

### [x] T9.5 — Tests Automatisés & Validation QA du Profil
- **Priorité :** 🟡 P1 (Qualité)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `tests/Feature/UserProfileTest.php`, `tests/Feature/CollectionGridTest.php`, `tests/Feature/LevelProgressionTest.php`
- **Réalisations validées :**
  - [x] Test de limitation stricte à 3 cartes favorites.
  - [x] Test de la progression de niveau et calcul d'XP.
  - [x] Test d'accès au profil personnel et redirection/affichage du profil public.
  - [x] Test de protection des cartes favorites contre le recyclage.
  - [x] Test d'exactitude du calcul des duels d'arène, victoires, défaites et winrate dans le profil.
  - [x] Test d'ouverture de la fiche détaillée d'inspection avec composants, passifs innés et synergies.
  - [x] Suite de tests `tests/Feature/UserProfileTest.php` validée à 100% (11 tests passants, 53 assertions).


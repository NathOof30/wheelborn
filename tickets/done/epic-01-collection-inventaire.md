# 🗃️ Épic 1 — Collection & Inventaire (`CollectionGrid`)

> **Statut :** ✅ Terminé (100% - 5/5 tickets validés)  
> **Responsable principal :** 🧑‍💻 Nathanaël  

---

### [x] T1.1 — Création du composant Livewire `CollectionGrid`
- **Priorité :** 🔴 P0 (MVP)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `routes/web.php`
- **Réalisations validées :**
  - [x] Affichage en grille responsive (1 col mobile, 2 cols tablette, 3-4 cols desktop).
  - [x] Pagination fluide (12 cartes par page) pour éviter de surcharger le DOM.
  - [x] Compteur total de cartes possédées et indicateur de capacité.
  - [x] État vide incitatif ("Aucun combattant — Fais tourner la Roue !").

---

### [x] T1.2 — Filtres multi-critères et Tri dynamique
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `tests/Feature/CollectionGridTest.php`
- **Réalisations validées :**
  - [x] Recherche textuelle en direct (`wire:model.live.debounce`) par nom de héros avec bouton d'effacement rapide.
  - [x] Filtre rapide par rareté (badges cliquables du HUD & boutons de filtrage pour Commune, Rare, Épique, Légendaire).
  - [x] Filtre déroulant par race (8 races) et par arme principale (8 armes).
  - [x] Filtre rapide "Avec Synergie active uniquement" avec indicateur visuel pulsé.
  - [x] Tri dynamique par : Score Power (décroissant/croissant), Rareté (Légendaire -> Commun), Date d'obtention, Victoires en Arène.
  - [x] Réinitialisation instantanée sans rechargement de page via `wire:model.live`.

---

### [x] T1.3 — Système de Verrouillage de Carte (Favori / Lock)
- **Priorité :** 🔴 P0 (Sécurité Joueur)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/migrations/2026_10_19_140000_add_is_locked_to_characters_table.php`, `app/Models/Character.php`, `app/Services/RecycleService.php`, `tests/Feature/CollectionGridTest.php`
- **Réalisations validées :**
  - [x] Colonne `is_locked` (booléen, indexé) sur la table `characters`.
  - [x] Icône de cadenas interactive (🔒/🔓) sur chaque carte pour basculer l'état verrouillé en un clic avec notification.
  - [x] Le bouton "Recycler" individuel est désactivé et transformé en badge sécurisé "🔒 Protégé" si la carte est verrouillée.
  - [x] Le `RecycleService` bloque avec une exception (`DomainException`) toute tentative de recyclage d'une carte verrouillée (sécurité backend).
  - [x] Filtre rapide "🔒 Verrouillés uniquement" dans la barre d'outils de collection.

---

### [x] T1.4 — Recyclage en Masse Avancé (Multi-critères & Modale Dédiée)
- **Priorité :** 🟡 P1 (Économie Circulaire)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Services/RecycleService.php`, `app/Livewire/CollectionGrid.php`, `tests/Feature/CollectionGridTest.php`
- **Réalisations validées :**
  - [x] Modale de suppression de masse complète permettant de filtrer les combattants par rareté, score de puissance, race, arme et préservation des synergies.
  - [x] Aperçu visuel en direct : liste scrollable affichant chaque héros ciblé (icône de race, nom, rareté, puissance, or).
  - [x] Calcul prévisionnel instantané du total de pièces d'or 🪙 et du nombre de héros sélectionnés.
  - [x] Protection absolue : exclusion inviolable des héros verrouillés (`is_locked`), favoris (`is_favorite`) et déployés en arène (`is_active_fighter`).
  - [x] Transaction atomique (`DB::transaction` avec `lockForUpdate`) et mise à jour dynamique du solde HUD (`stats-updated`).

---

### [x] T1.5 — Cartes Recto/Verso (3D Card Flip) & Modale Inspecteur « Fiche Détaillée Épurée »
- **Priorité :** 🟢 P2 (Game Feel & Immersion)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CollectionGrid.php`, `resources/views/livewire/collection-grid.blade.php`, `resources/views/components/character-modal.blade.php`, `tests/Feature/CollectionGridTest.php`
- **Réalisations validées :**
  - [x] **Animation de Retournement 3D (Physical Card Flip) :**
    - Conteneur perspective 3D (`perspective: 1000px;` `.perspective-1000`) et flipper interne (`transform-style: preserve-3d;` `.preserve-3d`).
    - Rotation fluide sur l'axe Y (`transform: rotateY(180deg);`) avec courbe d'accélération naturelle `cubic-bezier(0.23, 1, 0.32, 1)`.
    - Occultation des faces opposées (`backface-visibility: hidden;` `.backface-hidden`).
    - Sur la grille : déclenchement au clic sur la carte avec isolation stricte des clics sur boutons action.
  - [x] **Parité Dimensionnelle Recto/Verso :** Hauteur et largeur équilibrées (440px de haut, ratio TCG 1:1.52) éliminant tout décalage.
  - [x] **Face Recto :** Portrait Paper Doll composite dynamique `<x-character-avatar>`, étoiles de rareté, score de puissance, rareté et titre.
  - [x] **Face Verso :** Tri-slot des composants avec icônes, 4 barres de stats colorées, badges de synergies actives, palmarès d'arène et bouton cadenas.
  - [x] **Modale Inspecteur `<x-character-modal />` :** Fiche technique grand format avec passifs innés des composants, synergies détaillées et palmarès V/D.


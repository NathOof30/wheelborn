# 🎨 Épic 5 — Paper Doll, Game Feel & Direction Artistique

> **Statut :** ⏳ En cours (2/5 tickets terminés : Paper Doll V2 Auras & DA Cosmique validés ✅, 3 tickets audio/animations/codex reportés en todo)  
> **Responsables :** 👥 Binôme (Nathanaël & Mathéo)  

---

### [x] T5.1 — Système de Rendu Composite "Paper Doll" (Avatar Procédural V2 & Auras d'Armes) & Châssis Responsive
- **Priorité :** 🟡 P1 (Immersion & Note Soutenance)
- **Attribué à :** 👥 Binôme
- **Cible :** `docs/GUIDE_ASSETS_PAPER_DOLL.md`, `app/Services/PaperDollService.php`, `resources/views/components/character-avatar.blade.php`, `tests/Feature/PaperDollTest.php`
- **Réalisations validées :**
  - [x] Moteur de superposition dynamique `PaperDollService` et composant `<x-character-avatar>` avec fallback résilient.
  - [x] **Pile de Rendu à 5 Calques (`z-index`) :**
    - Calque 0 : Fond d'aura de rareté.
    - Calque 1 : Silhouette / Corps de race (Humain actif).
    - Calque 2 : Effet élémentaire général.
    - Calque 3 : Arme tenue (8 armes actives en anglais : `sword`, `staff`, `bow`, `dagger`, `hammer`, `spear`, `grimoire`, `gun`).
    - Calque 4 : **Aura Élémentaire d'Arme** (`z-index: 40`, synchronisée au-dessus de l'arme avec micro-mise à l'échelle au hover).
  - [x] **16 Auras Actives Intégrées (8/8 Épée & 8/8 Bâton) :**
    - Épée : Feu 🔥, Glace ❄️, Foudre ⚡, Poison 🧪, Gravité 🌌, Régénération 💖, Téléportation 🌀, Invisibilité 👁️‍🗨️.
    - Bâton : Feu 🔥, Glace ❄️, Foudre ⚡, Poison 🧪, Gravité 🌌, Régénération 💖, Téléportation 🌀, Invisibilité 👁️‍🗨️.
  - [x] **Règle Spécifique d'Invisibilité Universelle :** Calque de base de l'arme masqué automatiquement pour préserver l'effet translucide.
  - [x] **Résolution Bilingue & Tolérante :** Alias français/anglais gérés de façon transparente (`epee`/`sword`, `feu`/`fire`, etc.).
  - [x] **Gabarit Carré Strict 1:1 :** `max-w-[270px] aspect-square w-full` éliminant tout étirement d'image.
  - [x] Validé par `tests/Feature/PaperDollTest.php` (7 tests, 76 assertions).

---

### [x] T5.5 — Direction Artistique "Le Vide et la Roue", Respiration Contemplative & Standardisation du Ratio Carte Physique (TCG 1:1.53)
- **Priorité :** 🟢 P2 (Direction Artistique & Polish Visuel Majeur)
- **Attribué à :** 👥 Binôme
- **Cible :** `resources/css/app.css`, `tailwind.config.js`, `resources/views/*`
- **Réalisations validées :**
  - [x] **Fond Cosmique Profond :** Dégradé néon `#050a14` $\rightarrow$ `#0a0e1a`, champ d'étoiles procédural (`starfield-drift`), respiration d'orbe orbitale (`cosmos-breathe`).
  - [x] **Typographies Majestueuses :** Police `Outfit` 800 pour les titres dorés `.heading-majestic`, police `Inter` pour les textes narratifs aérés.
  - [x] **Page d'Accueil Monumentale :** Hero section contemplative avec grand espacement vertical et piliers flottants.
  - [x] **Harmonisation UI Complète :** Glassmorphism sombre sur toutes les pages d'auth, profil, boutons et modales.
  - [x] **Standardisation Ratio TCG (~1:1.52) :**
    - Largeur de conteneur unifiée `max-w-7xl` sur Collection, Arène et Dashboard.
    - Cartes de collection calibrées à 440px de haut (ratio TCG 1:1.52) éliminant l'effet étiré.
    - Catalogue de sélection de l'Arène refondu en cartes physiques 305px avec bordures arrondies et halo de rareté.
    - Cartes déployées en duel 1v1 à 270px × 415px (ratio 1:1.54).


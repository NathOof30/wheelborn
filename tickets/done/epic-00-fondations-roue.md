# 🏛️ Épic 0 — Fondations, Roue de Tirage & Architecture

> **Statut :** ✅ Validé (4/5 tickets complétés, 1 ticket d'équilibrage reporté en todo)  
> **Responsables :** 👥 Binôme (Nathanaël & Mathéo)  

---

### [x] T0.1 — Mise en place des fondations Laravel 11 + Livewire 3 + Breeze
- **Priorité :** 🔴 P0 (Architecture)
- **Attribué à :** 👥 Binôme
- **Cible :** `composer.json`, `routes/web.php`, `resources/views/layouts/app.blade.php`
- **Description :** Socle technique moderne pour le projet WheelBorn.
- **Réalisations validées :**
  - [x] Authentification fonctionnelle via Laravel Breeze.
  - [x] Layout sombre cosmique, responsive mobile/desktop.
  - [x] Intégration fluide de Livewire 3 et Alpine.js sans conflit d'instance.

---

### [x] T0.2 — Données, Modèles, Schéma BDD & Enums
- **Priorité :** 🔴 P0 (Modèle de Données)
- **Attribué à :** 👥 Binôme
- **Cible :** `app/Models/*`, `app/Enums/*`, `app/Data/*`, `database/migrations/*`
- **Description :** Modélisation de l'univers martial et élémentaire de WheelBorn.
- **Réalisations validées :**
  - [x] 8 races (Humain, Elfe, Nain, Orc, Mort-Vivant, Démon, Céleste, Cyborg).
  - [x] 8 armes (Épée, Hache, Arc, Dague, Bâton, Marteau, Lance, Arme à feu).
  - [x] 8 pouvoirs élémentaires (Feu, Glace, Foudre, Terre, Poison, Gravité, Régénération, Téléportation).
  - [x] 16 synergies élémentaires (dont 4 combinaisons instables à risque).
  - [x] Tables BDD indexées : `users`, `characters`, `battles`, etc.

---

### [x] T0.3 — Services de Génération Procédurale & Recyclage
- **Priorité :** 🔴 P0 (Algorithmique & Économie)
- **Attribué à :** 👥 Binôme
- **Cible :** `app/Services/CharacterGeneratorService.php`, `app/Services/RecycleService.php`, `app/Services/EnergyManagerService.php`
- **Description :** Moteur de génération des combattants et valorisation en or.
- **Réalisations validées :**
  - [x] Tirage pondéré des composants et calcul des statistiques dérivées (PV, Force, Vitesse, Défense, Chance).
  - [x] Calcul de la valeur marchande de recyclage en or indexée sur la rareté et les attributs.
  - [x] Gestion des charges d'énergie de spin (3 max).

---

### [x] T0.4 — Composant Livewire `CharacterWheel`, Cadran Céleste & Carte Physique TCG (3D Flip)
- **Priorité :** 🔴 P0 (Interface Principale)
- **Attribué à :** 👥 Binôme
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `resources/css/app.css`
- **Description :** Rendu immersif de la Roue du Destin et révélation sous forme de carte physique.
- **Réalisations validées :**
  - [x] Titre noble unique, capsules HUD compactes (charges et jauge de chance pity 10/10).
  - [x] Cadran astral central en rotation douce (`animate-spin-slow`) alternant cycliquement entre les 3 disques (Races, Armes, Pouvoirs).
  - [x] Bouton d'invocation central "Lancer la Roue" et rituel de tirage à 3 roues désynchronisées.
  - [x] Révélation sous forme de vraie carte physique TCG (ratio standard 1:1.52, 440px de haut) avec 3D Flip recto/verso au clic et boutons Garder / Recycler.


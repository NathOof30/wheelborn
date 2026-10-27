# 🎓 Épic 7 — Outils Soutenance, Admin & Démo Universitaire

> **Statut :** ⏳ Partiel (2/3 tickets validés : God Mode & Seeder Bimodal ✅, ticket T7.1 reporté en todo)  
> **Responsable principal :** 🧑‍💻 Nathanaël  

---

### [x] T7.2 — Panneau de Débogage / God Mode dans l'Interface (Barre Admin & Forge Personnalisée)
- **Priorité :** 🟡 P1 (Outil Démo Soutenance)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `app/Services/CharacterGeneratorService.php`, `tests/Feature/PaperDollTest.php`
- **Réalisations validées :**
  - [x] Visible uniquement si l'utilisateur est administrateur (`$isAdmin === true`).
  - [x] **Ressources Illimitées :** 999 charges de roue, 999 tickets d'arène, 999 999 🪙.
  - [x] **Raccourcis Démo Rapides :**
    - Bouton "🌟 Forcer Super Spin (10/10)" pour démonstration instantanée devant le jury.
    - Bouton "+50 000 🪙" pour tester les transactions et l'Atelier.
    - Bouton "Reset Pity" pour réinitialiser le compteur.
  - [x] **Outil Avancé "Forge Personnalisée & Simulateur de Roue" (`showAdminForge`) :**
    - 4 sélecteurs déroulants dynamiques : Race, Arme, Pouvoir, Rareté fixée ou aléatoire.
    - Détection en temps réel des calques Paper Doll avec pastilles (Race ✅, Arme ✅, Aura ✅).
    - Détection et affichage en temps réel des synergies d'armes et d'éléments (`SynergyData::all()`).
    - Prévisualisation live du rendu `<x-character-avatar>` avant invocation.
    - Deux modes d'invocation : Révélation Directe (instantanée) ou Lancer de Roue Synchronisée (cadrans s'arrêtant exactement sur le résultat).
    - Méthode `CharacterGeneratorService::generateCustom()`.

---

### [x] T7.3 — Seeder de Base de Données Bimodal (Admin Démo & Joueur UX Standard)
- **Priorité :** 🔴 P0 (Soutenance & Démo)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/seeders/DatabaseSeeder.php`, `README.md`
- **Réalisations validées :**
  - [x] **Compte Admin (`admin@wheelborn.test` / `password`)** : 999 999 🪙, 999 charges, accès direct à la barre God Mode.
  - [x] **5 cartes de démonstration assignées à l'Admin** :
    1. `Valérian Brise-Fer` (Humain + Épée + Feu, Rare).
    2. `Syrus l'Archiviste` (Humain + Bâton + Foudre, Épique, verrouillé 🔒).
    3. `Malakor le Dévoreur` (Démon + Marteau + Feu, Légendaire, synergie démoniaque).
    4. `Sylvana Vente-Noire` (Elfe + Arc + Poison, Épique, synergie naturelle).
    5. `Thorgal Poing-de-Roc` (Nain + Hache + Terre, Commune).
  - [x] **Compte Joueur Normal (`player@wheelborn.test` / `password`)** : Collection vierge (0 carte), 250 pièces, 3 charges standard, 5 tickets d'arène.
  - [x] Idempotence totale via `updateOrCreate`.


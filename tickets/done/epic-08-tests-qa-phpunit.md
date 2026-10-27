# 🧪 Épic 8 — Tests Automatisés & Assurance Qualité (PHPUnit)

> **Statut :** ✅ Validé (4/5 tickets complétés, 189 tests passants, 784 assertions, 100% vert ✅ — 1 ticket T8.2 reporté en todo)  
> **Responsables :** 👥 Répartition selon les modules (Nathanaël & Mathéo)  

---

### [x] T8.1 — Tests du Composant Livewire `CharacterWheel`
- **Priorité :** 🔴 P0
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `tests/Feature/CharacterWheelTest.php`
- **Réalisations validées :**
  - [x] Rendu du composant, initialisation des cadrans célestes.
  - [x] Lancement de tirage, décrémentation des charges et incrémentation de la jauge de pitié.
  - [x] Validation de conservation de carte en inventaire et recyclage direct.
  - [x] Restauration du tirage en attente (`pending_reveal`) et blocage de concurrence.
  - [x] Validé par 15 tests passants.

---

### [x] T8.3 — Tests Unitaires de la Formule d'Élo FIDE
- **Priorité :** 🔴 P0
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `tests/Unit/EloServiceTest.php`
- **Réalisations validées :**
  - [x] Élo égal (1000 vs 1000) : gain de +16 en victoire, perte de -16 en défaite.
  - [x] Victoire outsider (1000 vs 1300) : gain élevé (> +24).
  - [x] L'Élo ne descend jamais en dessous de zéro.
  - [x] Pondération par ratio de puissance martial : gain amorti contre un adversaire trop faible (anti-farm) et sur-bonification d'exploit.
  - [x] Validé par 10 tests unitaires passants.

---

### [x] T8.4 — Tests de Robustesse des Transactions de Recyclage, Collection & Paper Doll
- **Priorité :** 🟡 P1
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `tests/Feature/CollectionGridTest.php`, `tests/Feature/PaperDollTest.php`
- **Réalisations validées :**
  - [x] Recyclage unitaire : suppression ou archivage propre et crédit du montant exact d'or.
  - [x] Sécurité d'appartenance `user_id` : rejet strict du recyclage de la carte d'un autre joueur.
  - [x] Protection des cartes verrouillées (`is_locked = true`) : exception levée et bouton sécurisé.
  - [x] Protection des cartes favorites (`is_favorite = true`) : rejet unitaire et exclusion en masse.
  - [x] Recyclage en masse multi-critères avec transaction atomique.
  - [x] Résolution et superposition dynamique des 5 calques Paper Doll (Race, Arme, Aura d'arme Layer 4 bilingue fr/en, avatar Blade).
  - [x] Validé par `tests/Feature/CollectionGridTest.php` (20 tests) et `tests/Feature/PaperDollTest.php` (7 tests).

---

### [x] T8.5 — Tests Unitaires & Feature du Moteur de Combat & BattleArena
- **Priorité :** 🔴 P0 (Anti-triche & Équilibre)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `tests/Unit/BattleEngineServiceTest.php`, `tests/Feature/BattleArenaTest.php`
- **Réalisations validées :**
  - [x] Déterminisme absolu de la seed aléatoire (`Mt19937`).
  - [x] Effets et passifs : saignement, étourdissement, régénération/vol de vie vampire, bouclier, initiative avec tirage si égalité.
  - [x] Sécurité limite 25 tours max et gestion du double K.O. (match nul).
  - [x] Test de conformité anti-triche : `isAuthentic()` valide un replay et rejette toute falsification.
  - [x] Tests d'intégration Livewire : régénération tickets, adversaires procéduraux, jauges de confrontation dynamiques et revanche (`rematch`).
  - [x] Validé par `tests/Unit/BattleEngineServiceTest.php` et `tests/Feature/BattleArenaTest.php` (18 tests arène & moteur passants).


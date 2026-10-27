# ⚔️ Épic 2 — Moteur de Combat & Arène 1v1 (`BattleArena`)

> **Statut :** ✅ Terminé (100% - 6/6 tickets validés)  
> **Responsable principal :** 🧑‍💻 Mathéo  

---

### [x] T2.1 — Implémentation du moteur de combat `BattleEngineService`
- **Priorité :** 🔴 P0 (Cœur de jeu)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/BattleEngineService.php`, `tests/Unit/BattleEngineServiceTest.php`
- **Réalisations validées :**
  - [x] Phase 1 (Initiative) : Vitesse la plus haute frappe en premier (avec tirage au sort si égalité).
  - [x] Phase 2 (Échange de coups) : Calcul des dégâts bruts atténués par la défense ennemie.
  - [x] Effets d'armes (saignement, contondant, critique) et de pouvoirs (brûlure DoT, gel, régénération).
  - [x] Prise en compte des 16 synergies élémentaires (ex: Pyro-Lame, Tempête Électrique, Instabilités).
  - [x] Coups critiques et esquives basés sur la Chance.
  - [x] Sécurité anti-boucle infinie (plafond strict à 25 tours max avec décision aux points).

---

### [x] T2.2 — Déterminisme, Seed aléatoire et Audit Log (`turns_log`)
- **Priorité :** 🔴 P0 (Anti-triche & Replay)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Models/Battle.php`, `app/Services/ArenaService.php`
- **Réalisations validées :**
  - [x] Générateur pseudo-aléatoire seedé (`Mt19937` isolé).
  - [x] Audit log complet enregistré dans un champ JSON `turns_log` (frappes, dégâts, PV restants, effets) et figé dans `fighters_snapshot`.
  - [x] Reproductibilité 100% identique du combat via la seed (`ArenaService::replay()` & `ArenaService::isAuthentic()`).

---

### [x] T2.3 — Gestion des Tickets d'Arène & Régénération
- **Priorité :** 🔴 P0 (Économie & Cadence de session)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/EnergyManagerService.php`, `app/Models/User.php`
- **Réalisations validées :**
  - [x] Stock max : 5 tickets d'arène.
  - [x] Vitesse de recharge : 1 ticket toutes les 5 minutes (avec calcul de rattrapage hors-ligne).
  - [x] Déduction atomique d'un ticket au lancement d'un duel dans une transaction SQL.
  - [x] Timer de rechargement en temps réel via composant Alpine.js (`+1 dans mm:ss`) synchronisé avec actualisation Livewire à `00:00`.

---

### [x] T2.4 — Interface de Combat Face-à-Face Livewire (`BattleArena`)
- **Priorité :** 🔴 P0 (Expérience Utilisateur)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/BattleArena.php`, `resources/views/livewire/battle-arena.blade.php`
- **Réalisations validées :**
  - [x] **Architecture Cockpit Zero-Scroll :** Répartition 65% (Arène 1v1 face-à-face) / 35% (Logs & commandes).
  - [x] **Modale de Sélection de Combattant Flottante :** Filtres rareté, recherche temps réel, tri dynamique.
  - [x] **Cartes Officielles WheelBorn :** Ratio physique TCG avec 3D Flip et avatar Paper Doll V2.
  - [x] **Comparatif Dynamique Pré-combat :** 4 jauges de confrontation horizontales bicolores (Vert 🟢 ➔ Rouge 🔴) pour PV, Force, Vitesse, Défense et indicateur d'initiative explicite.
  - [x] **Déroulement & Bilan :** Défilement tour par tour des échanges, barres de PV animées, récapitulatif Élo/or/XP, et boutons « Revanche » / « Autre adversaire ».

---

### [x] T2.5 — Bouton Fast-Forward ("Passer le combat / Résultat direct")
- **Priorité :** 🟡 P1 (QoL)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `resources/views/livewire/battle-arena.blade.php`, `resources/js/battle-animation.js`
- **Réalisations validées :**
  - [x] Bouton visible "Passer le combat ⏭".
  - [x] Interruption du timing d'animation pour affichage instantané des barres de vie finales et du bilan.

---

### [x] T2.6 — Gestion des Cas Limites : Égalités & Double K.O.
- **Priorité :** 🟡 P1 (Edge Case)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/BattleEngineService.php`, `app/Services/ArenaService.php`
- **Réalisations validées :**
  - [x] Règle officielle : Si double K.O., match déclaré "Match Nul" (`DRAW`, `winner_id = null`).
  - [x] En cas de match nul : variation d'Élo nulle ($\Delta \text{Élo} = 0$), et lot de consolation (20 pièces + 20 XP).
  - [x] Feedback visuel dédié "🩸 ÉGALITÉ SANGLANTE !".


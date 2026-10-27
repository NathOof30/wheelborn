# 🏆 Épic 3 — Système d'Élo FIDE & Matchmaking (`Leaderboard`)

> **Statut :** ✅ Terminé (100% - 6/6 tickets validés)  
> **Responsable principal :** 🧑‍💻 Mathéo  

---

### [x] T3.1 — Service Mathématique d'Élo Standard FIDE
- **Priorité :** 🔴 P0 (Équilibrage)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/EloService.php`, `tests/Unit/EloServiceTest.php`
- **Réalisations validées :**
  - [x] Calcul de la probabilité attendue : $E_A = \frac{1}{1 + 10^{(R_B - R_A)/400}}$.
  - [x] Calcul du delta officiel d'échecs : $\Delta = \text{round}(K \times (Score - E_A))$ avec $K=32$.
  - [x] Plancher d'Élo fixé à 0 (aucun score négatif possible).
  - [x] Validé par 10 tests unitaires dans `EloServiceTest.php`.

---

### [x] T3.2 — Système de Ligues dynamiques par Paliers
- **Priorité :** 🔴 P0 (Rétention)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Enums/League.php`, `app/Models/User.php`
- **Réalisations validées :**
  - [x] Paliers de rang : Bronze (0-999), Argent (1000-1399), Or (1400-1799), Platine (1800-2199), Maître (2200+).
  - [x] Blasons vectoriels et couleurs thématiques associés à chaque ligue.
  - [x] Promotion / relégation automatique à chaque mise à jour de l'Élo.

---

### [x] T3.3 — Matchmaking équilibré par Plage d'Élo & Pool Hybride (Anti-Farm)
- **Priorité :** 🔴 P0 (Fair-play & Variété)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/ArenaService.php`, `tests/Feature/MatchmakingTest.php`
- **Réalisations validées :**
  - [x] Fenêtre de recherche ciblée $\pm 100$ Élo, élargie à $\pm 250$ Élo.
  - [x] Exclusion formelle des propres cartes du joueur connecté.
  - [x] **Garantie Pool Minimum de 10 Cibles :** Complétion automatique par des combattants procéduraux si moins de 10 adversaires réels éligibles.
  - [x] **Anti-Loop :** Dépriorisation de la dernière carte affrontée pour empêcher les boucles de farm.
  - [x] **Modulateur de Difficulté de Puissance :** Gain d'Élo amorti en cas d'écrasement sans risque ($\Delta \approx +4$ à $+8$), normal ($\Delta \approx +16$), et bonus exploit outsider ($\Delta \approx +22$ à $+28$).
  - [x] **Anti-Triche F5 / Reroll :** Reroll gratuit persisté en BDD (`has_free_arena_reroll`), adversaire persistant en session.

---

### [x] T3.4 — Système de Combattants Fantômes / Bots (Anti-Cold Start)
- **Priorité :** 🔴 P0 (Indispensable Démo & Solitude)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/BotGeneratorService.php`, `tests/Feature/ArenaGhostTest.php`
- **Réalisations validées :**
  - [x] Génération procédurale à la volée de "Fantômes d'Arène" calibrés selon la ligue et le niveau du joueur.
  - [x] Noms procéduraux évocateurs ("Gladiateur de l'Ombre", "Sentinelle Céleste").
  - [x] Gains de pièces, d'XP et d'Élo appliqués normalement.

---

### [x] T3.5 — Composant Livewire `Leaderboard`
- **Priorité :** 🟡 P1 (Classement)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/Leaderboard.php`, `resources/views/livewire/leaderboard.blade.php`, `tests/Feature/LeaderboardTest.php`
- **Réalisations validées :**
  - [x] Podium stylisé pour le Top 3 (or, argent, bronze néon).
  - [x] Ligne épinglée indiquant le rang, l'Élo et la ligue du joueur connecté.
  - [x] Onglets de filtrage par ligue (Tous, Maître, Platine, Or, Argent, Bronze).
  - [x] Caching Laravel (durée 60 secondes) pour préserver les performances BDD.

---

### [x] T3.6 — Historique & Replay des Duels
- **Priorité :** 🟢 P2 (Historique)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/BattleHistory.php`, `resources/views/livewire/battle-history.blade.php`, `tests/Feature/BattleHistoryTest.php`
- **Réalisations validées :**
  - [x] Liste des 10 derniers combats du joueur (V/D, adversaire, delta Élo, date relative).
  - [x] Bouton "Revoir le combat" rechargeant le déroulé `turns_log` dans l'arène.


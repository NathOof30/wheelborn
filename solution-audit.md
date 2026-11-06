# 🛠️ Solutions de l'audit WheelBorn

> Suivi des corrections des problèmes relevés dans [audit.md](audit.md). La numérotation reprend celle de l'audit.
> **Branche :** `Test_bug`
> **Légende :** ✅ corrigé et testé · ⏳ à faire

## Avancement

| Section de l'audit | Statut |
|---|---|
| 1. Failles de triche (1 à 5) | ✅ Corrigées |
| 2. Bugs (6 à 12) | ⏳ À faire |
| 3. Performances (3.1 à 3.8) | ⏳ À faire |
| 4. Code à réduire | ⏳ À faire |

---

## 1. Failles de triche

**Principe commun :** Livewire laisse le navigateur modifier toute propriété publique qui n'est pas marquée `#[Locked]`, et appeler toute méthode publique. Désormais :
- tout l'état calculé par le serveur est verrouillé ;
- toute action sensible vérifie la base de données, jamais une propriété du composant.

Tests de non-régression : [tests/Feature/AntiCheatTest.php](tests/Feature/AntiCheatTest.php) (11 tests). Ils reprennent les sondes de l'audit à l'envers et vérifient que chaque triche est bloquée.

### ✅ Faille 1 — Un joueur pouvait se déclarer admin

- **Avant :** `$isAdmin` était modifiable depuis la console du navigateur, et c'était la seule protection des outils admin. `adminAddCoins(5000000)` créditait donc n'importe quel montant.
- **Correctif :**
  - `$isAdmin` est `#[Locked]` dans la roue, l'arène, le HUD et l'atelier ([CharacterWheel.php](app/Livewire/CharacterWheel.php), [BattleArena.php](app/Livewire/BattleArena.php), [PlayerHud.php](app/Livewire/PlayerHud.php), [Workshop.php](app/Livewire/Workshop.php)).
  - Les 5 outils admin de la roue appellent la nouvelle méthode `authorizeAdmin()`, qui vérifie `auth()->user()->isAdmin()` en base. Même verrou contourné, un joueur n'a donc toujours pas accès aux outils.
  - Sont aussi verrouillés, dans la roue : `spinCharges`, `maxCharges`, `pityCounter`, `coins`, `elo`, `nextRegenSeconds`, `emergencyCost`, `emergencyRemaining` et `revealedCharacter`. Dans l'arène : `arenaTickets`, `maxTickets` et `nextTicketSeconds`. Dans l'atelier : `coins`.
- **Tests :** `test_player_cannot_declare_himself_admin`, `test_admin_tools_check_the_real_account` (les outils appelés directement par un joueur ne font rien) et `test_real_admin_still_has_his_tools`.

### ✅ Faille 2 — Super Spin à chaque tirage

- **Avant :** la condition du Super Spin comparait la jauge à `$this->pityMax`, modifiable par le client. Avec `pityMax = -1`, chaque tirage devenait un Super Spin.
- **Correctif :**
  - `spin()` lit maintenant le seuil dans `GameConfig::pityThreshold()`, et non plus dans l'état du composant.
  - `$pityMax` est `#[Locked]`, ne sert plus qu'à l'affichage et est réinitialisé depuis la config à chaque `refreshState()`.
- **Tests :** `test_player_cannot_lower_the_super_spin_threshold` et `test_normal_spin_is_not_a_super_spin`.

### ✅ Faille 3 — Changer d'adversaire sans payer

- **Avant :** `newOpponent()` était publique et gratuite. Le bouton « Autre adversaire » d'après-combat l'appelait directement, et n'importe qui pouvait l'appeler depuis la console. Re-sélectionner la même carte donnait aussi un adversaire gratuit.
- **Correctif :**
  - `newOpponent()` est **privée** : le serveur ne s'en sert plus qu'à l'arrivée dans l'arène, au changement de combattant et à la sortie d'un replay sans adversaire.
  - Le bouton « Autre adversaire » appelle `rerollOpponent()` ([battle-arena.blade.php](resources/views/livewire/battle-arena.blade.php)). Il accepte maintenant d'être appelé après un combat et applique la règle du jeu : le 1er changement après un combat est gratuit, les suivants coûtent 10 🪙.
  - `selectFighter()` ne fait rien si la carte choisie est déjà sélectionnée.
- **Tests :** `test_free_new_opponent_method_is_not_callable`, `test_reselecting_the_same_fighter_does_not_change_the_opponent` et `test_after_a_fight_another_opponent_uses_the_free_reroll_then_costs_coins`.
- **Test modifié :** [BattleArenaTest.php](tests/Feature/BattleArenaTest.php) appelait `newOpponent()`. Il passe maintenant par `rerollOpponent()`, et vérifie toujours qu'un nouvel adversaire est proposé.
- **Limite connue :** changer de combattant (A → B → A) donne toujours un nouvel adversaire gratuit, car l'adversaire est choisi selon la puissance de la carte. Il faut alors des cartes différentes. Le bloquer complètement demanderait de garder l'adversaire en changeant de carte : c'est une décision de game design.

### ✅ Faille 4 — Combattre l'adversaire d'un replay

- **Avant :** après « Revoir le combat », l'adversaire de l'historique restait chargé. `fight()` ou « Revanche » permettaient alors de le combattre pour de vrai, et donc de farmer un adversaire faible.
- **Correctif :**
  - `fight()` est refusé pendant un replay, avec le message « Quitte le replay avant de lancer un vrai combat. ».
  - Au lancement d'un replay, l'adversaire réel est mis de côté dans `$opponentBeforeReplay` (`#[Locked]`).
  - Quitter le replay (« Revanche » ou « Autre adversaire ») appelle `leaveReplay()`, qui remet cet adversaire réel. S'il n'y en avait pas, un nouvel adversaire est proposé sans coût.
- **Tests :** `test_cannot_fight_during_a_replay` et `test_leaving_a_replay_restores_the_real_opponent` (avec deux replays enchaînés).

### ✅ Faille 5 — Charger toute la collection d'un coup

- **Avant :** `$perPage` était modifiable : `perPage = 100000` chargeait et affichait toute la collection en une fois.
- **Correctif :** `$perPage` est `#[Locked]` dans [CollectionGrid.php](app/Livewire/CollectionGrid.php).
- **Test :** `test_player_cannot_change_page_size`.

### Vérification

- Suite complète : **200 tests verts** (803 assertions).
- Les sondes de l'audit pour les failles 1 à 5 **échouent** désormais, avec le blocage attendu :
  - `CannotUpdateLockedPropertyException` sur `isAdmin`, `pityMax` et `perPage` ;
  - `MethodNotFoundException` sur `newOpponent` ;
  - aucun second combat enregistré pendant un replay.

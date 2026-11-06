# 🔍 Audit WheelBorn — Failles, bugs et performances

> **Date :** 6 novembre 2026
> **Code audité :** branche `Test_bug`, commit `9345dcd` (identique à `T4-atelier-economie`)
> **Méthode :** relecture de tout `app/`, `routes/` et des vues Livewire, selon les règles de la skill *laravel-best-practices* (identifiants `AUTH-001`, `PERF-001`… dans les tableaux). Les points marqués ✅ ont été **reproduits par un test automatisé** ; les autres viennent de la lecture du code.
> **Jeu de données des mesures :** 1 joueur avec 300 cartes et 400 duels, 1 rival avec 50 cartes (SQLite, machine de dev).

## Sommaire

1. [Failles de triche](#1--failles-de-triche-à-corriger-en-priorité) — 5 points, à corriger en premier
2. [Bugs](#2--bugs) — 7 points
3. [Performances](#3--ce-qui-fait-ramer-le-site) — 8 points, mesurés
4. [Code à réduire](#4--fichiers-où-le-code-peut-être-réduit) — 10 fichiers ou groupes de fichiers
5. [Ordre de correction conseillé](#5--ordre-de-correction-conseillé)

---

## 1. 🔴 Failles de triche (à corriger en priorité)

Livewire envoie l'état du composant au navigateur et accepte que le client modifie toute **propriété publique** qui n'est pas marquée `#[Locked]`. Il suffit d'ouvrir la console du navigateur sur la page du jeu.

| # | Problème | Où | Effet | Règle |
|---|---|---|---|---|
| 1 ✅ | `$isAdmin` est publique et non verrouillée, et c'est la **seule** vérification des outils admin | [CharacterWheel.php:37](app/Livewire/CharacterWheel.php#L37), méthodes `admin*` lignes 455-499 | N'importe quel joueur peut se donner un montant illimité d'or : `adminAddCoins(int $amount)` accepte n'importe quel montant. Il peut aussi forger des Légendaires et remettre sa jauge de chance au maximum. | AUTH-001 |
| 2 ✅ | `$pityMax` est publique et non verrouillée | [CharacterWheel.php:33](app/Livewire/CharacterWheel.php#L33), utilisée ligne 244 | Avec `pityMax = -1`, **chaque** tirage devient un Super Spin (0 % de Commune, 10 % de Légendaire). | AUTH-001 |
| 3 ✅ | `newOpponent()` est publique et gratuite | [BattleArena.php:343](app/Livewire/BattleArena.php#L343) | Changer d'adversaire autant qu'on veut sans payer les 10 🪙 de `rerollOpponent()`. Toute la protection « anti-F5 » du ticket T3.3 est contournée. `selectFighter()` sur la carte déjà choisie donne le même résultat. | SVC-001 |
| 4 ✅ | `fight()` n'est pas bloqué pendant un replay | [BattleArena.php:383](app/Livewire/BattleArena.php#L383) | Après « Revoir le combat », l'adversaire de l'historique reste chargé et on peut le combattre pour de vrai. Il suffit de rejouer le duel le plus facile de son historique pour farmer Élo et or en boucle. | — |
| 5 ✅ | `$perPage` est publique et non verrouillée | [CollectionGrid.php:21](app/Livewire/CollectionGrid.php#L21) | Avec `perPage = 100000`, toute la collection est chargée et rendue d'un coup (cartes et avatars), ce qui permet de saturer le serveur. | PERF-001 |

**Reproduire la faille 1** (connecté avec un compte joueur normal, sur `/dashboard`, console du navigateur) :

```js
const wheel = Livewire.all().find(c => c.name === 'character-wheel').$wire;
await wheel.$set('isAdmin', true);
await wheel.adminAddCoins(5000000);   // +5 000 000 🪙
```

**Correctifs :**
- Ajouter `#[Locked]` sur `isAdmin` (roue, arène, HUD, atelier), `pityMax`, `maxCharges` et `perPage`.
- Dans chaque méthode admin, vérifier le vrai compte : `if (!auth()->user()->isAdmin()) return;`. Ne pas se fier à une propriété du composant.
- Rendre `newOpponent()` privée. Le bouton « Autre adversaire » doit passer par `rerollOpponent()`.
- Dans `fight()`, refuser le combat si `$this->replayFighter !== null`.

---

## 2. 🟠 Bugs

| # | Problème | Où | Effet visible |
|---|---|---|---|
| 6 ✅ | `recycle($user, $character)` reçoit la carte entière au lieu de son id. PHP convertit le modèle en texte JSON, donc aucune carte n'est trouvée. | [UserProfile.php:129](app/Livewire/UserProfile.php#L129) | Recycler depuis le profil renvoie une **erreur 404** et la carte n'est jamais recyclée. Correctif : passer `$character->id`. |
| 7 ✅ | Les succès sont indexés par `slug`, qui n'existe pas (la colonne s'appelle `achievement_key`). La vue affiche `title` (le titre honorifique) au lieu de `name`, et lit `reward_coins` alors que la clé est `reward`. | [PublicPlayerProfile.php:75-121](app/Livewire/PublicPlayerProfile.php#L75-L121) | Sur le profil public, **aucun succès n'apparaît jamais débloqué**. Les cartes affichent « Gladiateur » au lieu de « Premier Sang », et toujours « 100 🪙 ». |
| 8 | `workshop_level` n'existe pas sur `User`. | [PublicPlayerProfile.php:72](app/Livewire/PublicPlayerProfile.php#L72) | Le niveau d'atelier affiché vaut toujours 1. |
| 9 ✅ | `race_label`, `weapon_label` et `power_label` sont utilisés mais n'existent pas sur `Character`. Eloquent renvoie `null` sans erreur. | [BattleArena.php:142](app/Livewire/BattleArena.php#L142), [Leaderboard.php:56](app/Livewire/Leaderboard.php#L56), [public-player-profile.blade.php:236](resources/views/livewire/public-player-profile.blade.php#L236) | La race, l'arme et le pouvoir sont **vides** dans la modale du rival (arène), la modale du classement et la vitrine du profil public. Correctif : ajouter les 3 accesseurs dans `Character` (`race_data['name']`…). |
| 10 | Aucun code ne crée jamais de ligne `PlayerStat`. | [RecycleService.php](app/Services/RecycleService.php), [UserProfile.php:199](app/Livewire/UserProfile.php#L199) | Les mises à jour de `total_recycled` et `coins_earned_total` ne s'exécutent jamais. Le « total d'or gagné » du profil est une **estimation inventée** (cartes recyclées × 65). |
| 11 | Le changement d'adversaire payant lit le solde puis le débite en deux temps, sans transaction ni verrou. | [BattleArena.php:362-367](app/Livewire/BattleArena.php#L362-L367) | Deux clics simultanés : deux changements pour un seul paiement, ou un solde qui passe sous zéro. Correctif : débit conditionnel `User::whereKey($id)->where('coins', '>=', $cost)->decrement('coins', $cost)`. |
| 12 | Une carte en vitrine (favorite) est protégée du recyclage dans le profil, mais pas dans la collection. `RecycleService` ne vérifie pas `is_favorite`. | [RecycleService.php](app/Services/RecycleService.php), [UserProfile.php:123](app/Livewire/UserProfile.php#L123) | Règle incohérente selon la page. Il faut décider si une favorite est recyclable, puis appliquer la règle dans `RecycleService` (unitaire et en masse). |

---

## 3. 🟡 Ce qui fait ramer le site

### Mesures

| Action | Requêtes SQL | Temps |
|---|---|---|
| **Arène : ouvrir la modale de sélection, ou taper une lettre dans sa recherche** | 1 | **≈ 750 ms** |
| Collection : chaque frappe dans la recherche | 12 | 115 ms |
| `GET /collection` | 13 | 130 ms |
| `GET /profile` | 13 | 89 ms |
| `GET /players/{nom}` (profil public) | 11 | 138 ms |
| `GET /arena` | 6 | 90 ms |
| `GET /dashboard` | 6 | 46 ms |
| `GET /leaderboard` | 6 | 67 ms |
| `GET /workshop` | 4 | 30 ms |

### Causes, de la plus coûteuse à la moins coûteuse

1. **Avatars : les vérifications de fichiers ne sont jamais mises en cache** — [PaperDollService.php](app/Services/PaperDollService.php) (règle PERF-001)
   - Pour chaque calque, `findFirstAsset()` et `getAuraAsset()` testent tous les alias × toutes les extensions avec `file_exists`, soit jusqu'à environ 110 tests par avatar quand un calque manque. Rien n'est gardé en mémoire.
   - **Mesuré : 1,57 ms par avatar.** La modale de l'arène affiche un avatar pour **chaque** combattant (300 ici) et se recalcule à chaque frappe, d'où les 750 ms.
   - **Correctif :** garder en mémoire le résultat de `resolveLayers()` (tableau statique indexé par race/arme/pouvoir/rareté), ou mieux, scanner une fois `public/images/characters` et mettre la liste des fichiers en cache (`Cache::rememberForever`). Paginer aussi la modale de l'arène.
2. **Collection : requêtes en trop à chaque rendu** — [CollectionGrid.php:349-434](app/Livewire/CollectionGrid.php#L349-L434) (règle PERF-001)
   - 8 requêtes de comptage (total, verrouillées, favorites, max, 4 raretés) peuvent tenir en **une seule** avec `SUM(CASE WHEN …)`.
   - `filteredCount` refait le comptage que la pagination fait déjà : utiliser `$characters->total()`.
   - **`getBatchRecyclePreview()` charge toutes les cartes concernées à chaque rendu, même modale fermée.** Ne le calculer que si `$showBatchModal` est vrai.
3. **`User::battleStats()` charge tous les duels en mémoire** — [User.php:112](app/Models/User.php#L112) (règle ELO-001)
   - Appelée par le profil, le profil public, la modale du classement et la modale du rival. Elle grossit avec l'historique.
   - **Correctif :** une seule requête d'agrégat (`COUNT(*)`, `SUM(CASE WHEN winner_id = challenger_id …)`).
4. **Profil public : toutes les cartes chargées pour compter les synergies** — [PublicPlayerProfile.php:58-68](app/Livewire/PublicPlayerProfile.php#L58-L68)
   - Ce calcul et toutes les stats du profil sont refaits à chaque clic, par exemple à l'ouverture de la modale d'inspection.
   - **Correctif :** calculer une fois au `mount()` ou dans un `#[Computed(persist: true)]`.
5. **Rafraîchissements automatiques en double** — [character-wheel.blade.php:1](resources/views/livewire/character-wheel.blade.php#L1), [battle-arena.blade.php:6](resources/views/livewire/battle-arena.blade.php#L6)
   - La roue relance `refreshState()` toutes les 10 s : 4 à 6 requêtes et le rendu complet d'une vue de 739 lignes. Or le minuteur Alpine appelle déjà `refreshState` quand le compte à rebours atteint 00:00.
   - L'arène fait de même toutes les 15 s.
   - **Correctif :** supprimer les `wire:poll` et garder les minuteurs.
6. **`WorkshopService::upgradesOf()` fait une requête à chaque appel** — [WorkshopService.php](app/Services/WorkshopService.php)
   - C'est un `firstOrCreate`, appelé 4 à 5 fois par rendu de la roue (roue des pouvoirs, angles d'arrêt, Ticket d'Urgence).
   - **Correctif :** garder le résultat par joueur pour la durée de la requête.
7. **Les données statiques sont reconstruites à chaque appel** — `app/Data/RaceData.php`, `WeaponData.php`, `PowerData.php`, `SynergyData.php`
   - `find()` reconstruit tout le tableau puis le parcourt. On compte 37 appels, dont certains dans des boucles (avatars, accesseurs de `Character`, détection des synergies).
   - **Correctif :** mémoriser le tableau dans une propriété statique, avec un index par `id`.
8. **Matchmaking** — [ArenaService.php](app/Services/ArenaService.php) `findOpponent()`
   - 10 fantômes complets sont générés à chaque recherche pour n'en garder qu'un.
   - **Correctif :** ne générer que le fantôme retenu.

---

## 4. 📉 Fichiers où le code peut être réduit

| Fichier(s) | Constat | Réduction proposée |
|---|---|---|
| [user-profile.blade.php](resources/views/livewire/user-profile.blade.php) (604 l.) + [public-player-profile.blade.php](resources/views/livewire/public-player-profile.blade.php) (517 l.) | **167 lignes identiques sur 334** lignes distinctes | Composants Blade communs : en-tête de profil, stats, armoire à trophées, vitrine |
| [UserProfile.php](app/Livewire/UserProfile.php) + [PublicPlayerProfile.php](app/Livewire/PublicPlayerProfile.php) | Même calcul des statistiques (niveau, duels, collection, succès), écrit deux fois avec des bugs différents (voir 7 et 8) | Un `ProfileStatsService` unique (DRY-001) |
| [PublicPlayerProfile.php](app/Livewire/PublicPlayerProfile.php) | ~35 lignes de catalogue de succès « de secours » derrière `Achievement::DEFINITIONS ?? [...]`, qui ne servent jamais (la constante n'est jamais nulle) | Supprimer |
| [CollectionGrid.php](app/Livewire/CollectionGrid.php) + [UserProfile.php](app/Livewire/UserProfile.php) | `toggleFavorite()` et `toggleLock()` dupliqués, et la version du profil n'a même pas de transaction | Les déplacer dans un service (ex. `CharacterService`) appelé par les deux composants (SVC-001) |
| CharacterWheel, CollectionGrid, UserProfile, Workshop | `notify()` / `dismissNotification()` copiés 4 fois | Un trait `HasNotifications` |
| [BattleArena.php:121](app/Livewire/BattleArena.php#L121) + [Leaderboard.php:37](app/Livewire/Leaderboard.php#L37) | Même construction de la fiche du rival (niveau, ligue, stats, favoris) | Une seule méthode, par exemple `User::rivalCard()` ou un service |
| [battle-arena.blade.php](resources/views/livewire/battle-arena.blade.php) (1006 l.), [collection-grid.blade.php](resources/views/livewire/collection-grid.blade.php) (792 l.), [character-modal.blade.php](resources/views/components/character-modal.blade.php) (419 l.) | Le rendu des cartes (stats, synergies, badges) est recopié : 84 lignes communes entre collection et modale, 67 entre arène et collection | Un composant `<x-character-card>` |
| [ArenaService.php](app/Services/ArenaService.php) `describeFighter()` | Recopie les libellés de rareté avec un `match` | Utiliser `Rarity::from(...)->label()` |
| [PaperDollService.php](app/Services/PaperDollService.php) `getAuraAsset()` | 4 boucles imbriquées, dont une sur un tableau à un seul élément (`$dirs`) | Simplifier, puis brancher le cache du point 3.1 |
| `app/Models/ArenaSession.php` (+ table `arena_sessions`), `app/Models/PlayerStat.php` | `ArenaSession` n'est jamais utilisé ; `PlayerStat` n'est jamais créé (voir 10) | Supprimer `ArenaSession`, et brancher ou supprimer `PlayerStat` |

---

## 5. Ordre de correction conseillé

1. **Failles 1 à 5** : petites modifications (attributs `#[Locked]`, vérifications côté serveur), mais impact maximal sur l'économie du jeu.
2. **Bugs 6, 7 et 9**, visibles par tous les joueurs, puis 8, 10, 11 et 12.
3. **Performances 3.1 (avatars) et 3.2 (collection)** : à elles deux, elles couvrent l'essentiel de la lenteur ressentie.
4. **Performances 3.3 à 3.8**, puis les **réductions de code** de la section 4. Les faire après les correctifs évite de déplacer du code bogué.

Pour chaque correctif, ajouter un test qui reproduit le problème avant de le corriger. Les cas ✅ de cet audit ont déjà été écrits sous cette forme et peuvent être repris.

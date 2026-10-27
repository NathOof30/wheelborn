# 🛡️ Épic 6 — Robustesse, Concurrence & Sécurité

> **Statut :** ✅ Terminé (100% - 6/6 tickets validés)  
> **Responsable principal :** 🧑‍💻 Nathanaël  

---

### [x] T6.1 — Reprise automatique d'un tirage non finalisé (`pending_reveal`)
- **Priorité :** 🔴 P0 (Intégrité Joueur)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `tests/Feature/CharacterWheelTest.php`
- **Réalisations validées :**
  - [x] Vérification automatique lors du `mount()` si un héros a `pending_reveal = true`.
  - [x] Restauration immédiate du personnage dans la modale avec notification explicite.
  - [x] Blocage strict d'un nouveau spin tant que le tirage en attente n'est pas gardé ou recyclé.

---

### [x] T6.2 — Transactions Atomiques et Protection Multi-clic
- **Priorité :** 🔴 P0 (Sécurité des Données)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Services/RecycleService.php`, `app/Livewire/CharacterWheel.php`, `app/Livewire/CollectionGrid.php`, `tests/Feature/CharacterWheelTest.php`
- **Réalisations validées :**
  - [x] Encapsulation dans des transactions `DB::transaction(...)`.
  - [x] Verrouillage pessimiste `lockForUpdate()` sur `User` (solde pièces, énergie) et `Character` (recyclage).
  - [x] Contrôle d'appartenance strict (`where('user_id', $user->id)`) et interdiction de recycler un héros déployé dans l'arène.
  - [x] Désactivation côté client des boutons au clic (`wire:loading.attr="disabled"`).

---

### [x] T6.3 — Calcul précis du rattrapage temporel (Catch-Up Time) & Correction Carbon 3
- **Priorité :** 🔴 P0 (Énergie)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Services/EnergyManagerService.php`, `tests/Feature/EnergyManagerTest.php`
- **Réalisations validées :**
  - [x] Calcul mathématique par timestamp : `elapsed = max(0, now()->timestamp - last_regen->timestamp)`.
  - [x] Plafonnement strict au maximum légal (3 charges).
  - [x] Préservation des secondes résiduelles (`addSeconds`).

---

### [x] T6.4 — Compte à rebours dynamique de recharge & synchronisation Livewire
- **Priorité :** 🔴 P0 (UX / Feedback)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/CharacterWheel.php`, `resources/views/livewire/character-wheel.blade.php`, `resources/js/app.js`
- **Réalisations validées :**
  - [x] Pré-rendu direct en PHP pour éviter tout glitch `NaN` ou vide avant exécution JS.
  - [x] Compte à rebours animé fluide côté client via Alpine.js (`wire:key` isolé).
  - [x] Suppression du double appel `Alpine.start()` dans `resources/js/app.js` pour éliminer le conflit avec Livewire 3.

---

### [x] T6.5 — Composant Livewire réactif `PlayerHud` (Synchronisation temps réel du solde)
- **Priorité :** 🔴 P0 (Économie en Temps Réel)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `app/Livewire/PlayerHud.php`, `resources/views/livewire/player-hud.blade.php`, `tests/Feature/PlayerHudTest.php`
- **Réalisations validées :**
  - [x] Extraction du HUD vers un composant Livewire réactif dédié.
  - [x] Écouteur d'événement `#[On('stats-updated')]` synchronisant instantanément les pièces, charges et l'Élo.
  - [x] Animation visuelle (bump / scale doré) lors de la mise à jour des pièces.
  - [x] Régénération automatique de l'énergie dès le chargement du HUD sur toutes les pages.

---

### [x] T6.6 — Unicité Stricte du Pseudo (`name`), Résolution des Doublons & Connexion Hybride
- **Priorité :** 🔴 P0 (Sécurité & Auth)
- **Attribué à :** 🧑‍💻 Nathanaël
- **Cible :** `database/migrations/2026_10_23_211500_make_name_unique_in_users_table.php`, `app/Http/Requests/Auth/LoginRequest.php`, `tests/Feature/Auth/*`
- **Réalisations validées :**
  - [x] Migration de dédoublonnage automatique préservant le compte originel et index unique sur `name`.
  - [x] Validation `unique:users,name` avec messages personnalisés en français.
  - [x] Connexion hybride : détection transparente email ou pseudonyme dans `LoginRequest`.
  - [x] Validé par `tests/Feature/Auth/RegistrationTest.php` et `tests/Feature/Auth/AuthenticationTest.php`.


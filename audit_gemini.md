🛡️ Audit Produit & Plan de Tranches Verticales — WHEELBORN

Application : WHEELBORN (RNGboy) • Laravel 11 / Livewire 3-4 / Tailwind CSS / SQLite
Cadre méthodologique : Disciplines .agents/skills (revisar, planejar, laravel-deep-modules, laravel-tdd-seams, laravel-verification, ste100-clean-code)
Configuration centrale : docs/agents/projeto.md
Profil étalon débutant : player@wheelborn.test (collection vide, 250 🪙, 3 ⚡)
Mesure initiale de la porte de vérification : 189 tests passants (784 assertions) • Pint : écarts de style détectés sur 32 fichiers

1. Revue Adversariale (revisar) — Les 2 Axes
Axe 1 : Standards, Sécurité & Módulos Profundos (laravel-deep-modules)
A. Preuve par exécution : Crash de typage au recyclage depuis le profil
Localisation : UserProfile.php:129
Code défaillant :
php
$recycleService = app(RecycleService::class);
$coins = $recycleService->recycle($user, $character); // Modèle Eloquent passé au lieu de string $characterId
Contrat du Service : RecycleService.php:19
php
public function recycle(User $user, string $characterId): int
Preuve d'exécution : En appelant $userProfile->recycleCharacter($char->id), PHP convertit l'instance Character en chaîne JSON. La méthode interne Character::where('id', $characterId)->firstOrFail() lève une exception non interceptée ModelNotFoundException (erreur 500 en production).
Lacune de test : UserProfileTest.php teste la modale mais ne teste jamais l'action Livewire recycleCharacter.
B. Asymétrie de régénération d'énergie (Concurrence & Global HUD)
Localisation : PlayerHud.php:30-33
Constat : Le HUD régénère $energy->regenSpins($user), mais omet systématiquement regenArenaTickets($user).
Preuve : regenArenaTickets n'est invoqué que sur la route /arena (BattleArena.php:312). Si le joueur passe 20 minutes sur la Roue ou l'Atelier, ses tickets d'arène restent gelés à 0 jusqu'à ce qu'il visite /arena.
C. Protection d'état Livewire (#[Locked])
Vérification : CollectionGrid.php et BattleArena.php protègent bien leurs IDs avec #[Locked].
Atomicité des transactions : RecycleService.php et CharacterWheel.php utilisent DB::transaction() avec lockForUpdate(). La sécurité contre le double-clic et les requêtes concurrentes est assurée au niveau BDD.
Axe 2 : Spécification & Expérience Joueur (NOUVELLE_BOUCLE_GAMEPLAY.md)
A. Paradoxe d'équilibrage T0.5 : "Commun supérieur à Légendaire"
Localisation : CharacterGeneratorService.php:31-35
Code défaillant :
php
$stats = $this->rollStats($race, $weapon, $synergies, $user);
$powerScore = $this->calculatePowerScore($stats, $synergies, $isUnstable);
$rarity = $this->determineRarity($isLuckySpin, $powerScore, count($synergies));
Preuve d'incohérence mathématique :
Les statistiques martiales (PV, Force, Vitesse, Défense) sont tirées sans connaître la rareté.
La rareté n'applique aucun multiplicateur martial et aucun plancher minimum garanti.
Un combattant Commun avec de bons jets naturels atteint un Power Score de 78.
Un combattant Légendaire (tiré sur le Super Spin Pity 10/10) avec des jets naturels faibles obtient un Power Score de 52.
L'attente centrale du joueur (récompense de la rareté) est contredite par la formule.
B. Anomalie visuelle Paper Doll : "Armes volantes sans personnage"
Localisation : character-avatar.blade.php:35
Constat : Dans public/images/characters/races/, seul human.webp existe physiquement. Les 7 autres races n'ont aucun asset graphique de corps.
Preuve : La condition $hasVisualLayer = !empty($layers['race']) || !empty($layers['weapon']) || ...; s'évalue à true dès qu'une arme existe. Pour un Elfe ou un Démon armé d'une épée, $layers['race'] est vide mais $hasVisualLayer est vrai : le composant affiche une épée flottant dans le vide sans aucun corps, tout en masquant le fallback d'avatar texturé/émoji.
C. Friction d'onboarding débutant (player@wheelborn.test)
Localisation : dashboard.blade.php:1-19
Constat : À sa première connexion, le joueur découvre une collection à 0 sans aucun guidage textuel. La jauge de Pity 0/10, le compte à rebours de 4 minutes des charges, et le choix entre Conserver et Recycler ne sont accompagnés d'aucun panneau d'orientation.
2. Cartographie des Flux de Bout en Bout (Mapa de Fluxos)
[Flux 1 : Recyclage depuis le Profil]
Action UI : Modale d'inspection Profil (Bouton "Recycler")
  → Entrée : UserProfile::recycleCharacter($characterId) [UserProfile.php:107]
  → Consommateur : RecycleService::recycle(User, string) [RecycleService.php:19]
  → Mutation BDD : DB::transaction + lockForUpdate sur user & character [RecycleService.php:25]
  → Sortie : $character->recycled_at = now(), user->coins += $coins, dispatch('stats-updated')
[Flux 2 : Régénération Temporelle d'Énergie]
Action UI : Chargement / Polling de n'importe quel écran
  → Entrée : PlayerHud::refreshStats() [PlayerHud.php:23]
  → Consommateurs :
      1. EnergyManagerService::regenSpins($user) [PlayerHud.php:32]
      2. EnergyManagerService::regenArenaTickets($user) [MANQUANT DANS HUD]
  → Mutation BDD : user->spin_charges, user->arena_tickets mis à jour
  → Sortie : Affichage instantané des jauges ⚡ (3/3) et ⚔️ (5/5)
[Flux 3 : Génération Procédurale & Rareté]
Action UI : CharacterWheel::spin()
  → Entrée : Tirage standard ou Super Spin (pity 10/10) [CharacterWheel.php:220]
  → Consommateurs :
      1. CharacterGeneratorService::determineRarity() [AVANT les stats]
      2. CharacterGeneratorService::rollStatsWithRarity() [Applique plancher + multiplicateur]
      3. CharacterGeneratorService::calculatePowerScore()
  → Mutation BDD : Insertion atomique du Character avec power_score cohérent
  → Sortie : Révélation d'une carte dont la puissance reflète sa rareté
[Flux 4 : Rendu Paper Doll]
Action UI : Affichage carte (Collection, Profil, Arène)
  → Entrée : <x-character-avatar :character="$character" />
  → Consommateur : PaperDollService::resolveLayers() [character-avatar.blade.php:34]
  → Règle d'affichage : Si pas de calque 'race', basculer sur le piédestal émoji ou la silhouette
  → Sortie : Zéro arme flottant dans le vide sans corps
3. Plan de Tranches Verticales (planejar)
⚡ Throughput Checkpoint
Item de Contrôle	Analyse Technique	Décision d'Architecture
1. Fondation Bloquante	Le crash de UserProfile.php:129 et le bug de régénération bloquent le parcours utilisateur.	Priorité absolue immédiate. Traiter ces 2 anomalies en TDD pur avant tout ajout.
2. Fronts Parallélisables	3 chantiers ne touchent aucun fichier en commun :
• Rendu Avatar (character-avatar.blade.php)
• Moteur de Rareté (CharacterGeneratorService.php)
• Commande de Démo (GameDemoSetupCommand.php)	Peuvent être implémentés et testés de manière totalement indépendante sans conflit de merge.
3. État Mutável Compartilhado	Fichiers partagés à séquencer :
• PlayerHud.php et player-hud.blade.php
• UserProfile.php et user-profile.blade.php	Ne pas modifier ces fichiers simultanément dans plusieurs tickets. Terminer et valider les tests du Profil avant d'étendre le HUD.
4. Menor Decomposição Segura	Découpage en 5 fatias verticais autonomes d'environ 40 à 100 lignes de code chacune, chacune livrée avec son test Pest/PHPUnit dédié.	Zéro ticket horizontal (pas de "ticket de migrations seules").
Graphe des Fatias Verticais (Tickets d'Exécution)
🟢 Ticket T-01 : Sécuriser et tester le recyclage unitaire depuis la modale Profil
Objectif observable : Le joueur connecté peut recycler une carte non verrouillée et non favorite depuis la modale d'inspection de son profil sans déclencher d'erreur 500, ses pièces augmentent immédiatement et la carte disparaît de sa collection.
Bloqué par : Aucun.
Pointeurs de contexte :
Modifier : app/Livewire/UserProfile.php:128-132
Compléter : tests/Feature/UserProfileTest.php
Critère d'acceptation de test (laravel-tdd-seams) :
Test RED : test_user_can_recycle_character_from_profile_modal() appelant Livewire::actingAs($user)->test(UserProfile::class)->call('recycleCharacter', $char->id).
Vérifier que $user->fresh()->coins a augmenté de la valeur de recyclage et que $char->fresh()->recycled_at n'est pas nul.
Test de protection : vérifier qu'un appel avec is_locked=true ou is_favorite=true refuse le recyclage et ne débite rien.
🟢 Ticket T-02 : Harmoniser la régénération globale des tickets d'arène et enrichir le HUD
Objectif observable : Les 5 tickets d'arène se régénèrent toutes les 12 minutes où que se trouve le joueur sur le site, et le compteur de tickets d'arène (⚔️ 5/5) est visible en permanence dans le HUD supérieur à côté des charges de spin.
Bloqué par : Aucun.
Pointeurs de contexte :
Modifier : app/Livewire/PlayerHud.php:30-43
Modifier : resources/views/livewire/player-hud.blade.php:40-52
Modifier : tests/Feature/PlayerHudTest.php
Critère d'acceptation de test (laravel-tdd-seams) :
Test RED : test_hud_triggers_arena_tickets_regeneration_globally().
Vérifier qu'un utilisateur avec 0 ticket et un timestamp de 30 minutes récupère ses tickets dès que le HUD monte ou reçoit l'événement stats-updated.
Vérifier que le rendu Blade contient l'indicateur textuel de tickets ⚔️.
🟢 Ticket T-03 : Corriger le rendu Paper Doll pour interdire les armes flottantes sans corps
Objectif observable : Tout héros dont la race n'a pas d'illustration officielle (Elfe, Démon, Robot, Mutant, Golem, Vampire, Alien) affiche son socle stylisé avec son émoji de race et ses badges, et ne rend jamais une arme isolée flottant sur fond noir.
Bloqué par : Aucun.
Pointeurs de contexte :
Modifier : resources/views/components/character-avatar.blade.php:34-45
Compléter : tests/Feature/PaperDollTest.php
Critère d'acceptation de test (laravel-tdd-seams) :
Test Feature : test_non_human_character_with_weapon_renders_pedestal_fallback_instead_of_orphan_weapon().
Rendu du composant pour une carte race_id = 'demon' et weapon_id = 'sword' : assertion que le conteneur de fallback est présent et que l'image de l'arme seule n'est pas injectée sans calque de race parent.
🟢 Ticket T-04 : Implémenter le scaling martial et les planchers de stats garantis (T0.5)
Objectif observable : La rareté est calculée au début de la génération. Les cartes Rares (
×
1.10
×1.10), Épiques (
×
1.22
×1.22) et Légendaires (
×
1.35
×1.35) bénéficient de multiplicateurs et de planchers de caractéristiques stricts, garantissant qu'une carte Légendaire surpasse systématiquement une carte Commune.
Bloqué par : Ticket T-01.
Pointeurs de contexte :
Modifier : app/Services/CharacterGeneratorService.php:25-59 et méthodes rollStats / determineRarity.
Compléter : app/Data/GameConfig.php avec les planchers par rareté.
Créer : tests/Unit/CharacterGeneratorServiceTest.php (Ticket T8.2).
Critère d'acceptation de test (laravel-tdd-seams) :
Test unitaire : 100 tirages consécutifs de cartes Légendaires ont un Power Score 
≥
75
≥75 et des stats minimales garanties (
PV
≥
65
PV≥65, 
Force
≥
35
Force≥35).
Vérifier que lors d'un Super Spin (lucky spin = true), aucune carte Commune ne peut être générée.
🟢 Ticket T-05 : Créer la commande Artisan de préparation de démo clé en main (T7.1)
Objectif observable : La commande php artisan game:demo-setup réinitialise et prépare la base en 5 secondes, avec 3 profils calibrés (Bronze, Argent, Maître) et 40 combattants d'arène actifs avec historiques de duels.
Bloqué par : Ticket T-04.
Pointeurs de contexte :
Créer : app/Console/Commands/GameDemoSetupCommand.php
Enregistrer dans : routes/console.php
Créer : tests/Feature/GameDemoSetupCommandTest.php
Critère d'acceptation de test (laravel-tdd-seams) :
Test de commande : this->artisan('game:demo-setup')->assertExitCode(0).
Vérifier la présence des 3 utilisateurs clés (demo.bronze@wheelborn.test, demo.argent@wheelborn.test, demo.maitre@wheelborn.test) et d'un Leaderboard immédiatement peuplé d'au moins 40 combattants.
4. Porte de Vérification Finale (laravel-verification)

À la livraison de chaque tranche :

Exécution du formatage Pint : ./vendor/bin/pint --dirty (ou ./vendor/bin/pint).
Exécution ciblée du test de la costure : php artisan test --filter=<NomDuTest>.
Exécution complète de non-régression : php artisan test (maintien du 100% vert sur l'ensemble des 189+ tests).
Validation STE100 : zéro commentaire narratif redondant dans le code produit.
# 📖 WHEELBORN — Compendium Officiel de Game Design
## Armes, Races, Pouvoirs, Synergies & Équilibrage des Raretés

> **Document de Référence Gameplay & Équilibrage Mathématique**  
> **Version :** 1.1.0 — Octobre 2026  
> **Auteurs :** Nathanaël & Mathéo (Équipe WheelBorn)  
> **Moteur :** Laravel 11 • Livewire 3 • Tailwind CSS • SQLite  

---

## 📑 Sommaire
1. [Vision du Jeu & Philosophie Procédurale](#1-vision-du-jeu--philosophie-procédurale)
2. [Clarification sur le Roster des Armes (Le Cas du Marteau)](#2-clarification-sur-le-roster-des-armes-le-cas-du-marteau)
3. [Catalogue Détaillé des 8 Armes](#3-catalogue-détaillé-des-8-armes)
4. [Catalogue Détaillé des 8 Races](#4-catalogue-détaillé-des-8-races)
5. [Catalogue Détaillé des 8 Pouvoirs & VFX Élémentaires](#5-catalogue-détaillé-des-8-pouvoirs--vfx-élémentaires)
6. [Matrice Croisée & Catalogue des 16 Synergies](#6-matrice-croisée--catalogue-des-16-synergies)
   - [6.1. Les 12 Synergies Harmoniques](#61-les-12-synergies-harmoniques)
   - [6.2. Les 4 Combinaisons Instables (Haut Risque / Haut Rendement)](#62-les-4-combinaisons-instables-haut-risque--haut-rendement)
7. [Audit Critique : Le Paradoxe "Rareté vs Puissance Réelle"](#7-audit-critique--le-paradoxe-rareté-vs-puissance-réelle)
   - [7.1. Diagnostic du Problème Actuel](#71-diagnostic-du-problème-actuel)
   - [7.2. Solutions d'Équilibrage Recommandées pour le Ticket T0.5](#72-solutions-déquilibrage-recommandées-pour-le-ticket-t05)
8. [Formules Mathématiques Complètes du Moteur](#8-formules-mathématiques-complètes-du-moteur)

---

## 1. Vision du Jeu & Philosophie Procédurale

Dans **WheelBorn**, chaque héros est le produit unique de la combinaison de 3 rouleaux indépendants lors de l'invocation :
$$\text{Combinaisons Possibles} = 8\text{ Races} \times 8\text{ Armes} \times 8\text{ Pouvoirs} = 512\text{ Archétypes Fondamentaux}$$

Associés aux **jets de statistiques aléatoires** et aux **6 paliers de rareté**, le jeu propose des dizaines de milliers de cartes distinctes.

Pour que le gameplay soit à la fois lisible, intuitif et stratégique pour le joueur :
- **Chaque race** oriente la silhouette corporelle et impose un tempérament de base (ex: le Golem est lourd et résistant, l'Elfe est vif et fragile).
- **Chaque arme** dicte le mode d'engagement en duel (allonge, étourdissement, critique, opportunisme).
- **Chaque pouvoir** apporte une dimension magique active (brûlure DoT, ralentissement, esquive spatiale).
- **Les synergies** récompensent l'harmonie narrative et martiale entre ces 3 éléments.

---

## 2. Clarification sur le Roster des Armes (Le Cas du Marteau)

> [!IMPORTANT]
> ### 🔨 Pourquoi le Marteau et NON la Hache ni le Bouclier ?
> - **L'Arme Lourde Officielle est le Marteau (`hammer`)** :  
>   Dans la vision de combat et d'équilibrage de WheelBorn, l'archétype lourd repose sur le **traumatisme contondant** et le **contrôle de foule (étourdissement / stun à 25%)**. Dans l'esprit des joueurs et dans les synergies (notamment *Impact Tellurique* avec le Golem 🗿), le marteau symbolise une onde de choc sismique qui fait chanceler l'adversaire.  
> - **La Hache (`hache.webp`)** : Une hache évoque une arme tranchante, des dégâts de lacération et du saignement. Elle ne correspond pas aux effets de choc sismique et d'étourdissement du marteau. Elle est donc **exclue du roster des armes officielles**.
> - **Le Bouclier (`bouclier.webp`)** : Le bouclier est un équipement défensif passif et non une arme d'attaque de la roue. Il est écarté du tirage d'armes offensives et réservé pour une éventuelle mécanique ultérieure d'objets ou de sous-calque d'armure.

Les 8 armes de WheelBorn sont donc strictement :  
**Épée ⚔️ • Arc 🏹 • Marteau 🔨 • Lance 🔱 • Dague 🗡️ • Bâton 🪄 • Pistolet 🔫 • Grimoire 📖**.

---

## 3. Catalogue Détaillé des 8 Armes

Fichier source : [`app/Data/WeaponData.php`](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/app/Data/WeaponData.php)

| Arme | Emoji | ID BDD | Type | Modificateurs Stats | Passif de Combat | Effet Mécanique Chiffré | Poids | Synergies Associées |
| :--- | :---: | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **Épée** | ⚔️ | `sword` | Mêlée | +12 Force<br>+4 Vitesse<br>+6 Défense | *Tranchant droit* | Équilibre robuste réduisant les dégâts subis en parade | 15 | Humain + Épée (*Maître d'Armes*) |
| **Arc** | 🏹 | `bow` | Distance | +6 Force<br>+16 Vitesse<br>-2 Défense | *Tir d'alerte* | Initiative maximale : attaque quasi-systématiquement en premier | 15 | Elfe + Arc (*Archer Naturel*) |
| **Marteau** | 🔨 | `hammer` | Lourd | +5 PV<br>+24 Force<br>-12 Vitesse<br>+8 Défense | *Impact lourd* | **25% de chance d'étourdir** l'adversaire (fait passer son tour) | 12 | Golem + Marteau (*Impact Tellurique*) |
| **Lance** | 🔱 | `spear` | Allonge | +2 PV<br>+10 Force<br>+10 Vitesse<br>+4 Défense | *Estoc fulgurant* | **+25% de dégâts** lors du tout premier coup porté du duel | 14 | Lance + Gravité (*Pointe Gravitationnelle*) |
| **Dague** | 🗡️ | `dagger` | Assassin | -2 PV<br>+8 Force<br>+20 Vitesse<br>-4 Défense | *Opportunisme* | **+30% de dégâts** si la vitesse du héros dépasse celle de la cible | 12 | Dague + Invisibilité (*Assassin Fantôme*) |
| **Bâton** | 🪄 | `staff` | Magique | +8 PV<br>+6 Force<br>+6 Vitesse<br>+6 Défense | *Résonance arcanique*| **+25% sur l'efficacité** des soins et boucliers magiques | 12 | Bâton + Régénération (*Harmonie Sylvestre*) |
| **Pistolet** | 🔫 | `gun` | Balistique | +14 Force<br>+12 Vitesse<br>-4 Défense | *Tir de précision* | **25% de chance de critique** avec multiplicateur amplifié à **×1.75** | 11 | Pistolet + Foudre (*Tir Électro-Magnétique*) |
| **Grimoire** | 📖 | `grimoire` | Incantation | +16 Force<br>+2 Vitesse<br>+2 Défense | *Magie primordiale*| **+35% sur tous les effets élémentaires** (Feu, Glace, Poison, Foudre) | 11 | Grimoire + Éléments (*Incantation Arcanique*) |

---

## 4. Catalogue Détaillé des 8 Races

Fichier source : [`app/Data/RaceData.php`](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/app/Data/RaceData.php)

| Race | Emoji | ID BDD | Archétype | Modificateurs Stats | Passif Racial | Mécanique en Combat | Poids | Synergies Clés |
| :--- | :---: | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **Humain** | 👤 | `human` | Polyvalent | +5 PV, +5 Force<br>+5 Vitesse, +5 Défense | *Adaptabilité* | Profil sans faille statistique, s'adapte à tous les rôles | 20 | Humain + Épée |
| **Elfe** | 🧝 | `elf` | Assassin / Ranger | -2 PV, +2 Force<br>+18 Vitesse, -10 Défense | *Grâce Sylvestre* | Vitesse explosive, garantit l'initiative mais encaisse mal | 15 | Elfe + Arc |
| **Démon** | 😈 | `demon` | Berzerker | +4 PV, +20 Force<br>+6 Vitesse, -12 Défense | *Fureur Infernale*| Frappes d'une violence inouïe au détriment de l'armure | 12 | Démon + Feu, Démon + Glace |
| **Robot** | 🤖 | `robot` | Tank Titane | +12 PV, +6 Force<br>-14 Vitesse, +22 Défense| *Châssis Titane* | Blindage d'acier réduisant fortement les dégâts physiques | 12 | Robot + Foudre, Robot + Poison |
| **Mutant** | ☣️ | `mutant` | Imprévisible | 0 de base<br>Variance [-12, +20] | *Génome Chaotique*| Chaque statistique reçoit un jet indépendant chaotique | 10 | Mutant + Gravité |
| **Golem** | 🗿 | `golem` | Colosse de Roc | +25 PV, +12 Force<br>-22 Vitesse, +25 Défense| *Écorce Rocheuse* | Réserve de PV colossale et armure impénétrable | 10 | Golem + Marteau, Golem + Téléportation |
| **Vampire** | 🧛 | `vampire` | Voleur de Vie | -4 PV, +14 Force<br>+10 Vitesse, -6 Défense | *Soif de Sang* | **Vole 12% des dégâts infligés** sous forme de soins PV | 11 | Vampire + Régénération |
| **Alien** | 👽 | `alien` | Spatiale | +6 PV, +8 Force<br>+8 Vitesse, +6 Défense | *Biologie Stellaire*| **+8% d'esquive innée** face à tous les coups adverses | 10 | Alien + Téléportation |

---

## 5. Catalogue Détaillé des 8 Pouvoirs & VFX Élémentaires

Fichier source : [`app/Data/PowerData.php`](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/app/Data/PowerData.php)

| Pouvoir | Emoji | ID BDD | Tier | Type | Effet Spécial & Formule de Combat | Poids | Synergies Associées |
| :--- | :---: | :--- | :---: | :--- | :--- | :---: | :--- |
| **Feu** | 🔥 | `fire` | Commun | Élémentaire | **DoT de Brûlure** : 8 dégâts continus par tour pendant 3 tours | 30 | Démon + Feu, Grimoire |
| **Poison** | 🧪 | `poison` | Commun | Toxique | **Envenimement croissant** : 5 dégâts au tour 1, +5 par tour pendant 3 tours | 30 | Robot + Poison, Grimoire |
| **Glace** | ❄️ | `ice` | Rare | Élémentaire | **Ralentissement glacial** : Réduit la vitesse ennemie de 35% et 15% chance de gel | 10 | Démon + Glace, Grimoire |
| **Foudre** | ⚡ | `lightning` | Rare | Élémentaire | **Choc foudroyant** : +35% chances de critique avec multiplicateur ×1.8 | 10 | Robot + Foudre, Pistolet + Foudre |
| **Régénération** | 💖 | `regeneration`| Rare | Vital | **Soin continu** : Restaure passivement **+12 PV par tour** tout le duel | 8 | Vampire + Regen, Bâton + Regen |
| **Gravité** | 🌌 | `gravity` | Mythique | Cosmique | **Pression écrasante** : -45% vitesse ennemie et -15% de ses dégâts infligés | 4 | Lance + Gravité, Mutant + Gravité |
| **Téléportation**| 🌀 | `teleportation`| Mythique | Spatial | **Saut dimensionnel** : **28% de chances d'esquiver** totalement l'attaque | 4 | Alien + Téléport, Golem + Téléport |
| **Invisibilité** | 👁️‍🗨️| `invisibility` | Mythique | Furtif | **Camouflage optique** : Esquive 100% garantie au Tour 1, +20% esquive globale, critique surprise ×1.6 | 4 | Dague + Invisibilité |

> [!TIP]
> ### 🎨 Manifestation Visuelle : Les Auras d'Armes Élémentaires (Layer 4)
> Pour conférer un impact visuel maximal et une lecture instantanée de l'élément en duel et dans la Collection, les pouvoirs s'expriment à présent via des **auras dessinées spécifiquement pour chaque arme** :
> - **Superposition (Layer 4)** : Affichée à `z-index: 40`, strictement **au-dessus de l'arme** et au premier plan.
> - **Dossiers d'assets** : `public/images/characters/weapons/{weapon}_auras/{weapon}_{element}.webp`.
> - **Les 8 auras de l'Épée actives** : Feu (`sword_fire.webp`), Glace (`sword_ice.webp`), Foudre (`sword_lightning.webp`), Poison (`sword_poison.webp`), Gravité (`sword_gravity.webp`), Régénération (`sword_regeneration.webp`), Téléportation (`sword_teleportation.webp`), Invisibilité (`sword_invisibility.webp`).
> - **Règle Spécifique de l'Aura d'Invisibilité (Toutes Armes)** : L'aura d'invisibilité reprend l'**outline (tracé au trait sans couleur)** de l'arme. Le moteur Paper Doll (`PaperDollService`) **masque automatiquement l'arme de base (Layer 3)** uniquement pour cet effet, évitant que les couleurs pleines de l'arme ne recouvrent la translucidité du tracé invisible.
> - Détails d'exportation et gabarit disponibles dans le [GUIDE_ASSETS_PAPER_DOLL.md](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/docs/GUIDE_ASSETS_PAPER_DOLL.md).

---

## 6. Matrice Croisée & Catalogue des 16 Synergies

Fichier source : [`app/Data/SynergyData.php`](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/app/Data/SynergyData.php)

### 6.1. Les 12 Synergies Harmoniques

| # | Synergie | Emoji | Condition Requise | Effets Mécaniques en Duel | Description Tactique |
| :-: | :--- | :---: | :--- | :--- | :--- |
| **1** | **Affinité Démoniaque** | 🔥😈 | `demon` + `fire` | Dégâts bruts ×1.18 (+18%)<br>Dégâts de brûlure ×1.50 (+50%) | Le Démon embrase ses lames infernales. La brûlure consume rapidement l'adversaire. |
| **2** | **Archer Naturel** | 🏹🧝 | `elf` + `bow` | Vitesse +16<br>Chances critique +20% | L'Elfe tire à une cadence effrénée, s'assurant l'initiative et des flèches critiques dévastatrices. |
| **3** | **Supraconducteur** | ⚡🤖 | `robot` + `lightning` | Défense +15<br>Soin +6 PV par coup électrique reçu | Le châssis robotique absorbe l'électricité ambiante pour se réparer au lieu de subir des décharges. |
| **4** | **Sangsue Immortelle** | 💖🧛 | `vampire` + `regeneration` | Soin passif doublé (+12 PV/tour)<br>Vol de vie accru à 25% | Le Vampire devient une machine d'usure quasi-increvable sur les combats longs. |
| **5** | **Impact Tellurique** | 🔨🗿 | `golem` + `hammer` | **Force +25**<br>**40% chance d'étourdissement** (+15%) | Le Golem abat son marteau de 5 tonnes avec une onde de choc sismique qui cloue net la cible au sol. |
| **6** | **Incantation Arcanique** | 📖✨ | `grimoire` + (`fire`/`ice`/`lightning`/`poison`) | Dégâts des sorts ×1.25<br>Durée des DoT +1 tour | Le Grimoire canalise et prolonge les effets élémentaires sur la cible. |
| **7** | **Maître d'Armes** | ⚔️👤 | `human` + `sword` | Force +12<br>Défense +12<br>Réduction dégâts subis -10% | L'Humain compense son absence de magie par une maîtrise absolue du fer et de la parade. |
| **8** | **Distorsion Spatiale**| 🌀👽 | `alien` + `teleportation` | **Esquive globale +30%**<br>Contre-attaque automatique à chaque esquive | L'Alien s'évanouit dans le néant et réapparaît dans le dos de l'attaquant pour frapper. |
| **9** | **Assassin Fantôme** | 🗡️👁️‍🗨️| `dagger` + `invisibility` | Vitesse +12<br>Premier coup critique garanti à **×2.2** | Frappe furtive létale dès l'ouverture du combat. Capable d'éliminer une cible fragile d'un coup. |
| **10**| **Harmonie Sylvestre** | 🪄💖 | `staff` + `regeneration` | **Bouclier de 25 PV** au début du duel<br>Régénération +8 PV/tour | Le Bâton déploie une barrière protectrice sacrée protégeant la vie du porteur. |
| **11**| **Tir Électro-Magnétique**| 🔫⚡ | `gun` + `lightning` | Chances critique +25%<br>Multiplicateur critique porté à **×2.0** | Les balles ionisées percent les armures les plus épaisses avec des surtensions mortelles. |
| **12**| **Pointe Gravitationnelle**| 🔱🌌 | `spear` + `gravity` | L'ennemi ne peut **JAMAIS esquiver**<br>Dégâts globaux ×1.15 | La lance alourdit la masse ennemie, interdisant toute cabriole ou téléportation adverse. |

---

### 6.2. Les 4 Combinaisons Instables (Haut Risque / Haut Rendement)

Ces combinaisons octroient un bonus au Power Score (+2) mais introduisent de la volatilité chaotique en combat :

| # | Synergie Instable | Emoji | Condition Requise | Risque / Pénalité | Récompense / Avantage |
| :-: | :--- | :---: | :--- | :--- | :--- |
| **13**| **Combustion Glacée** | ❄️😈💥 | `demon` + `ice` | Dégâts imprévisibles oscillant entre **50% et 220%** à chaque tour | Inflige simultanément les DoT de **Brûlure ET de Gel** à l'adversaire |
| **14**| **Infection Artificielle** | 🧪🤖☣️ | `robot` + `poison` | Le Robot subit une corrosion acide interne (**-4 PV par tour**) | Réduit la défense adverse de **30%** dès le premier contact |
| **15**| **Masse Déplacée** | 🌀🗿☄️ | `golem` + `teleportation` | Téléportation lourde hasardeuse | **+20% d'esquive**, puis retombe avec un choc d'écrasement de **+30 dégâts bonus** |
| **16**| **Singularité Vivante** | 🌌☣️🌀 | `mutant` + `gravity` | Effet gravitationnel chaotique | Stats redistribuées aléatoirement à chaque tour (**[-10, +25] en Force et Vitesse**) |

---

## 7. Audit Critique : Le Paradoxe "Rareté vs Puissance Réelle"

### 7.1. Diagnostic du Problème Actuel

Actuellement dans [`CharacterGeneratorService.php`](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/app/Services/CharacterGeneratorService.php) :
1. **Les statistiques brutes sont tirées en premier** :
   ```php
   $hp       = mt_rand(55, 75) + $race['mods']['hp'] + $weapon['mods']['hp'];
   $strength = mt_rand(45, 70) + $race['mods']['strength'] + $weapon['mods']['strength'];
   $speed    = mt_rand(40, 68) + $race['mods']['speed'] + $weapon['mods']['speed'];
   $defense  = mt_rand(42, 68) + $race['mods']['defense'] + $weapon['mods']['defense'];
   ```
2. **Le Power Score est calculé à partir de ces stats** :
   $$\text{Power} = (\text{Force} \times 0.32) + (\text{PV} \times 0.24) + (\text{Défense} \times 0.24) + (\text{Vitesse} \times 0.20)$$
3. **La Rareté est tirée APRÈS COUP** selon des probabilités indépendantes (72% Commun, 21% Rare, 6% Épique, 1% Légendaire).

#### ❌ La Conséquence Négative (Dissonance Cognitive) :
- Un personnage **Commun** avec d'excellents jets naturels (ex: Golem + Marteau qui fait 75 PV, 70 Force, 68 Défense) peut atteindre un **Power Score de 265 pts**.
- Un personnage **Légendaire** à 1% de chance ou tiré lors du Super Spin avec des jets moyens (ex: Humain + Dague à 55 PV, 48 Force) aura un **Power Score de 225 pts**.
- **Pour le joueur, c'est incompréhensible et frustrant** : la rareté d'une carte est universellement perçue dans les jeux comme un gage de supériorité martiale. Obtenir une carte dorée Légendaire plus faible qu'une carte grise Commune brise l'excitation du tirage.

---

### 7.2. Solutions d'Équilibrage Recommandées pour le Ticket T0.5

Pour résoudre définitivement ce problème, trois approches complémentaires sont recommandées :

#### Solution Recommandée : Multiplicateur Statistique & Plancher Garanti par Rareté
Lors de la génération, le tirage de la Rareté doit s'effectuer **en amont** du calcul des statistiques et agir comme un **coefficient d'amplification martial** :

| Rareté | Badge Visuel | Plancher de Base Garanti | Multiplicateur Global de Stats | Power Score Moyen Cible |
| :--- | :---: | :---: | :---: | :---: |
| **Commun** | Gris ardoise | Stats normales | **× 1.00** | ~ 210 - 240 pts |
| **Rare** | Cyan lumineux | Au moins 1 stat $\ge 70$ | **× 1.10** (+10%) | ~ 235 - 265 pts |
| **Épique** | Violet mystique | Au moins 2 stats $\ge 75$ | **× 1.22** (+22%) | ~ 260 - 290 pts |
| **Légendaire** | Or flamboyant | Toutes stats $\ge 65$ & 1 stat $\ge 85$ | **× 1.35** (+35%) | ~ 285 - 330 pts |

#### Avantages de cette Réforme :
1. **Hiérarchie respectée à 100%** : Une carte Légendaire surpasse systématiquement une carte Commune.
2. **Satisfaction d'obtention maximale** : Le Super Spin 10/10 garantit une unité d'élite réellement redoutable en arène de combat.
3. **Valeur de recyclage cohérente** : La formule actuelle multiplie déjà l'or de recyclage par la rareté ($\times 1.50$ pour un Légendaire), ce qui sera désormais parfaitement justifié par sa puissance.

---

## 8. Formules Mathématiques Complètes du Moteur

### 8.1. Statistiques Finales du Héros
$$\text{Stat Finale} = \text{Clamp}\Big( \big( \text{JetBase} + \text{ModRace} + \text{ModArme} + \text{BonusSynergie} \big) \times \text{CoeffRareté},\ \text{Min},\ \text{Max} \Big)$$

- Bornes de sécurité :
  - $\text{PV} \in [30 ; 160]$
  - $\text{Force} \in [15 ; 120]$
  - $\text{Vitesse} \in [10 ; 120]$
  - $\text{Défense} \in [10 ; 120]$

### 8.2. Score de Puissance (Power Score)
$$\text{Power Score} = \text{Arrondi}\Big( (\text{Force} \times 0.32) + (\text{PV} \times 0.24) + (\text{Défense} \times 0.24) + (\text{Vitesse} \times 0.20) + (\text{Synergies} \times 3) + (\text{Instable} ?\ 2 : 0) \Big)$$

### 8.3. Valeur de Recyclage en Pièces d'Or (🪙)
Fichier : [`app/Services/RecycleService.php`](file:///c:/Users/natha/Documents/PROJET/WHEELBORN_PROJECT/WHEELBORN/app/Services/RecycleService.php)
$$\text{Recycle Gold} = \max\Big(35,\ \text{Arrondi}\Big( \big( (\text{Power Score} \times 1.2) + (\text{Nb Synergies} \times 25) \big) \times \text{MultRareté} \Big) \Big)$$

- Multiplicateurs de rareté pour le recyclage :
  - **Commun** : $\times 1.00$
  - **Rare** : $\times 1.15$
  - **Épique** : $\times 1.30$
  - **Légendaire** : $\times 1.50$

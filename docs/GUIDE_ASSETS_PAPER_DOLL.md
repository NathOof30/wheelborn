# 🎨 Guide de Création des Assets Graphiques — Système "Paper Doll" (WheelBorn)

Ce guide définit les normes techniques, les dimensions, la hiérarchie des calques, la nomenclature et les recommandations ergonomiques pour concevoir les assets visuels du système de génération procédurale de personnages de **WheelBorn**.

---

## 📑 Sommaire
1. [Architecture Globale & Philosophie](#1-architecture-globale--philosophie)
2. [La Problématique des Cadres (Frames) & Solution Responsive](#2-la-problématique-des-cadres-frames--solution-responsive)
3. [Spécifications Techniques du Canevas](#3-spécifications-techniques-du-canevas)
4. [Hiérarchie & Rôle des Calques (Layer Stack)](#4-hiérarchie--rôle-des-calques-layer-stack)
5. [Nomenclature Exacte des Fichiers (Mapping BDD)](#5-nomenclature-exacte-des-fichiers-mapping-bdd)
6. [Gabarit & Règles de Dessin (Alignement)](#6-gabarit--règles-de-dessin-alignement)
7. [Workflow d'Export & Optimisation Web](#7-workflow-dexport--optimisation-web)
8. [Intégration Blade / CSS (Exemple Pratique)](#8-intégration-blade--css-exemple-pratique)

---

## 1. Architecture Globale & Philosophie

Le système **Paper Doll** permet de générer des milliers de combinaisons visuelles uniques sans avoir à dessiner chaque carte individuellement. 

Une carte WheelBorn est découpée en **deux sous-systèmes indépendants** :
1. **L'Illustration (Portrait Paper Doll)** : Une zone carrée au format fixe (512x512 px) composée de calques superposés transparents (Aura, Race, Élément, Arme).
2. **Le Châssis de Carte (Card Shell & Frame)** : Une structure externe responsive qui entoure l'illustration, s'étire selon l'écran (mobile, desktop, modale de zoom) et affiche le cadre de rareté, le nom, le niveau et les statistiques.

```
+-------------------------------------------------+  <-- CHÂSSIS DE CARTE (Responsive CSS/Tailwind)
| [Rareté] Nom du Héros               Niveau 1    |      Gère les bordures, halos de rareté,
| +---------------------------------------------+ |      statistiques, modales & redimensionnements.
| |               PORTRAIT AVATAR               | |
| |                                             | |  <-- ZONE PAPER DOLL (512x512 Carré Fixe)
| |  [Layer 0] Aura / Décor Fond                | |      Superposition de calques transparents
| |  [Layer 1] Corps / Silhouette Race          | |      ne subissant AUCUNE déformation.
| |  [Layer 2] Effet Élémentaire / VFX          | |
| |  [Layer 3] Arme Équipée                     | |
| +---------------------------------------------+ |
| ATK: 120  |  DEF: 85  |  VIT: 105             |
| Synergies : [Elfe] [Arc]                      |
+-------------------------------------------------+
```

---

## 2. La Problématique des Cadres (Frames) & Solution Responsive

### Le Problème d'un Cadre Raster (PNG/WebP fixe)
Si on dessine un cadre sous forme d'image raster rigide (ex: `frames/legendary.webp` en 512x512 ou 400x600) :
- **Déformation sur resize** : Dès que la carte passe en responsive (grille 3 colonnes sur PC vs 1 colonne sur mobile vs modale grand format), l'aspect ratio varie légèrement. L'image du cadre s'étire (`stretch`), s'écrase ou rogne les bordures.
- **Flou ou pixellisation** : Agrandie dans la modale d'inspection (T1.4), une bordure raster peut perdre de sa netteté.
- **Poids inutile** : Télécharger 6 images de cadres lourdes pour chaque carte ralentit l'affichage.

### La Solution Recommandée : Châssis Découplé (CSS + SVG)

#### A. Le Cadre Principal : 100% Vectoriel en CSS / Tailwind
Au lieu d'une image, le cadre de la carte est géré par **les styles dynamiques du conteneur HTML** :
- **Bordures adaptatives** : `border-2`, `rounded-2xl`
- **Dégradés de rareté** : `bg-gradient-to-b from-amber-500/10 via-slate-900 to-amber-950/20`
- **Halos lumineux (Glows)** : `shadow-[0_0_20px_rgba(245,158,11,0.25)]`
- **Liseré intérieur** : `ring-1 ring-inset ring-amber-400/30`

> **Bénéfices :** 0 Ko d'image à charger, netteté absolue sur écran 4K et mobile Retina, étirement parfait à n'importe quelle largeur/hauteur sans la moindre distorsion.

#### B. Optionnels : Les Éléments d'Angle (Corner Ornaments) en SVG
Si tu souhaites des dorures gothiques, des runes ou des gravures fantastiques dans les coins de la carte :
- On crée **un petit composant SVG vectoriel de coin** (ex: 32x32 px).
- On le positionne en CSS dans les angles :
  - Coin haut gauche : `absolute top-0 left-0`
  - Coin haut droit : `absolute top-0 right-0 rotate-90`
  - Coin bas droit : `absolute bottom-0 right-0 rotate-180`
  - Coin bas gauche : `absolute bottom-0 left-0 -rotate-90`
- Les coins conservent ainsi leur ratio parfait quel que soit l'étirement des parois de la carte.

---

## 3. Spécifications Techniques du Canevas

Tous les calques du **Portrait Paper Doll** doivent être dessinés et exportés selon le même gabarit strict :

| Propriété | Spécification | Justification |
| :--- | :--- | :--- |
| **Résolution du Canevas** | **512 x 512 px** (Carré 1:1) | Idéal pour le web (compromis optimal netteté / temps de chargement). |
| **Format d'Export** | **WebP** (avec couche Alpha) | ~30% plus léger que le PNG-24 avec une transparence parfaite. |
| **Poids Cible par Calque** | **< 35 Ko** par fichier | Permet d'assembler 4 calques en moins de 150 Ko au total. |
| **Arrière-plan** | **Transparent (Canal Alpha)** | Seul le Layer 0 (Aura) possède un fond texturé. Tous les autres calques (1, 2, 3) doivent avoir un fond 100% transparent. |
| **Espace Colorimétrique** | **sRGB** | Évite les altérations de teinte sur les navigateurs web. |
| **Point d'Ancrage** | **Centre-Bas (X: 256, Y: 460)** | Tous les personnages posent leurs pieds ou leur base au même repère pour que les armes et effets tombent juste. |

---

## 4. Hiérarchie & Rôle des Calques (Layer Stack)

L'image finale du personnage est composée de **4 calques superposés** dans le DOM (du fond vers l'avant-plan) :

```
[AVANT-PLAN]
   ▲
   │  Layer 3 : Arme / Équipement  (z-index: 30) -> Main/Attaque
   │  Layer 2 : Pouvoir Élémentaire (z-index: 20) -> Flammes, éclairs, auras
   │  Layer 1 : Corps de la Race    (z-index: 10) -> Silhouette du héros
   │  Layer 0 : Fond d'Aura / Rareté(z-index: 0)  -> Texture magique / Halo
   ▼
[ARRIÈRE-PLAN]
```

### Layer 0 : Fond d'Aura & Rareté (`backgrounds/`)
- **Contenu** : Vortex magique, nébuleuse, étincelles ou runes liées à la rareté du combattant (Commun à Mythique).
- **Règle** : Doit être subtil et contrasté pour ne pas noyer le personnage (zones sombres au centre, lumière sur les bords).

### Layer 1 : Corps / Silhouette de la Race (`races/`)
- **Contenu** : Le personnage complet (tête, buste, membres, vêtements ou traits de base).
- **Les 8 Races du jeu** : Humain (`human`), Elfe (`elf`), Démon (`demon`), Robot (`robot`), Mutant (`mutant`), Golem (`golem`), Vampire (`vampire`), Alien (`alien`).
- **Règle vitale** : Le personnage doit avoir une **pose neutre standardisée** (ex: torse de 3/4 face, main droite légèrement avancée/ouverte pour recevoir l'arme du Layer 3).

### Layer 2 : Effet Élémentaire & Pouvoirs (`elements/`)
- **Contenu** : Flammes vives, éclairs crépitants, brume empoisonnée, cristaux de glace, particules de soin, distorsion gravitationnelle ou spatiale.
- **Les 8 Pouvoirs du jeu** : Feu (`fire`), Poison (`poison`), Glace (`ice`), Foudre (`lightning`), Régénération (`regeneration`), Gravité (`gravity`), Téléportation (`teleportation`), Invisibilité (`invisibility`).
- **Règle** : Les effets enveloppent le corps (ex: étincelles sur les épaules, aura autour des poings). Privilégier des zones semi-transparentes ou mix-blend pour laisser transparaître le corps.

### Layer 3 : Arme Équipée (`weapons/`)
- **Contenu** : Épée, arc, marteau, lance, dague, bâton, pistolet, grimoire.
- **Les 8 Armes du jeu** : Épée (`sword`), Arc (`bow`), Marteau (`hammer`), Lance (`spear`), Dague (`dagger`), Bâton (`staff`), Pistolet (`gun`), Grimoire (`grimoire`).
- **Règle vitale** : La poignée de l'arme doit s'aligner **exactement** avec la main droite du gabarit universel du Layer 1 (X: 180 px, Y: 320 px).

---

## 5. Nomenclature Exacte des Fichiers (Mapping BDD)

> [!WARNING]
> ### ⚠️ Les 8 Armes Officielles de Combat
> - **Les 8 Armes officielles** du projet sont strictement : **Épée (`sword`), Arc (`bow`), Marteau (`hammer`), Lance (`spear`), Dague (`dagger`), Bâton (`staff`), Pistolet (`gun`), Grimoire (`grimoire`)**.
> - **Hache & Bouclier** : Ne font pas partie des 8 armes officielles de la roue. Les fichiers `hache.webp` et `bouclier.webp` créés précédemment ne sont pas pris en compte pour les armes de combat. L'arme lourde officielle est le **Marteau** (`marteau.webp`).
> - **Races** : Les 8 races sont **Humain, Elfe, Démon, Robot, Mutant, Golem, Vampire, Alien** (pas de Nain, Orc, Ange ni Mort-vivant).
> - **Pouvoirs** : Les 8 pouvoirs sont **Feu, Poison, Glace, Foudre, Régénération, Gravité, Téléportation, Invisibilité** (pas d'Eau, Terre, Vent, Ombre, Lumière génériques).

Emplacement des dossiers dans le projet : `public/images/characters/`

### 1. Armes (`public/images/characters/weapons/`)
Le système supporte le nom français ou anglais (WebP). Voici la liste officielle des 8 armes du jeu :

| Arme | Emoji | ID BDD (`weapon_id`) | Type | Fichier Recommandé | Alias Acceptés par le Jeu | Statut Actuel |
| :--- | :---: | :--- | :--- | :--- | :--- | :---: |
| **Épée** | ⚔️ | `sword` | Mêlée équilibrée | `epee.webp` | `sword.webp` | ✅ Présent |
| **Bâton** | 🪄 | `staff` | Magique | `baton.webp` | `staff.webp` | ✅ Présent |
| **Arc** | 🏹 | `bow` | Distance | `arc.webp` | `bow.webp` | ✅ Présent |
| **Dague** | 🗡️ | `dagger` | Assassinat rapide | `dague.webp` | `dagger.webp` | ✅ Présent |
| **Marteau** | 🔨 | `hammer` | Mêlée lourde / Étourdissement | `marteau.webp` | `hammer.webp` | ⏳ À créer |
| **Lance** | 🔱 | `spear` | Allonge / Perforant | `lance.webp` | `spear.webp` | ⏳ À créer |
| **Pistolet** | 🔫 | `gun` | Distance / Balistique | `pistolet.webp` | `gun.webp` | ⏳ À créer |
| **Grimoire** | 📖 | `grimoire` | Sorts élémentaires | `grimoire.webp` | `book.webp` | ⏳ À créer |

> **Synthèse d'avancement des armes :**
> - `epee.webp`, `baton.webp`, `arc.webp`, `dague.webp` : 4/8 armes déjà créées et parfaitement fonctionnelles.
> - Reste à créer : `marteau.webp`, `lance.webp`, `pistolet.webp`, `grimoire.webp`.
> - `hache.webp` et `bouclier.webp` ne sont pas rattachés aux armes de duel du jeu.

---

### 2. Races (`public/images/characters/races/`)
Les 8 races déterminent la silhouette de base du héros :

| Race | Emoji | ID BDD (`race_id`) | Archétype & Rôle | Fichier Recommandé | Alias Acceptés | Statut Actuel |
| :--- | :---: | :--- | :--- | :--- | :--- | :---: |
| **Humain** | 👤 | `human` | Polyvalent / Équilibré | `human.webp` | `humain.webp` | ✅ Présent |
| **Elfe** | 🧝 | `elf` | Agile / Archerie rapide | `elf.webp` | `elfe.webp` | ⏳ À créer |
| **Démon** | 😈 | `demon` | Force brute / Dégâts de feu | `demon.webp` | `demon.webp` | ⏳ À créer |
| **Robot** | 🤖 | `robot` | Blindé / Armure lourde | `robot.webp` | `cyborg.webp` | ⏳ À créer |
| **Mutant** | ☣️ | `mutant` | Chaotique / Statistiques aléatoires | `mutant.webp` | `mutant.webp` | ⏳ À créer |
| **Golem** | 🗿 | `golem` | Colosse de pierre / PV colossaux | `golem.webp` | `golem.webp` | ⏳ À créer |
| **Vampire** | 🧛 | `vampire` | Voleur de vie / Saignement | `vampire.webp` | `vampire.webp` | ⏳ À créer |
| **Alien** | 👽 | `alien` | Spatiale / Esquive cosmique | `alien.webp` | `alien.webp` | ⏳ À créer |

---

### 3. Pouvoirs & Effets Élémentaires (`public/images/characters/elements/`)
Les 8 pouvoirs ajoutent une aura magique ou un effet visuel (Layer 2) au personnage :

| Pouvoir | Emoji | ID BDD (`power_id`) | Tier / Rareté | Fichier Recommandé | Alias Acceptés | Effet Visuel Suggéré |
| :--- | :---: | :--- | :--- | :--- | :--- | :--- |
| **Feu** | 🔥 | `fire` | Commun | `feu.webp` | `fire.webp` | Flammes orangées autour des poings ou de la lame |
| **Poison** | 🧪 | `poison` | Commun | `poison.webp` | `poison.webp` | Volutes toxiques verdâtres, gouttes acides |
| **Glace** | ❄️ | `ice` | Rare | `glace.webp` | `ice.webp` | Cristaux de givre translucides bleutés |
| **Foudre** | ⚡ | `lightning` | Rare | `foudre.webp` | `lightning.webp` | Arcs électriques crépitants cyan/jaunes |
| **Régénération** | 💖 | `regeneration` | Rare | `regeneration.webp` | `regen.webp` | Halo guérisseur doux, particules de vie rosées |
| **Gravité** | 🌌 | `gravity` | Mythique | `gravite.webp` | `gravity.webp` | Anneaux gravitationnels sombres, effet de lentille |
| **Téléportation** | 🌀 | `teleportation` | Mythique | `teleportation.webp` | `teleport.webp` | Faille dimensionnelle en spirale pourpre/violette |
| **Invisibilité** | 👁️‍🗨️ | `invisibility` | Mythique | `invisibilite.webp` | `invisibility.webp` | Silhouette fantomatique semi-transparente, fumée d'ombre |

---

### 4. Fonds de Rareté (`public/images/characters/backgrounds/`)
Les 6 fonds correspondent au palier de rareté de la carte (Layer 0) :

| Rareté | ID BDD (`rarity`) | Fichier Recommandé | Ambiance & Teinte |
| :--- | :--- | :--- | :--- |
| **Commun** | `common` | `common.webp` | Ardoise / Acier sobre avec runes discrètes |
| **Peu Commun** | `uncommon` | `uncommon.webp` | Reflets émeraude / Vert forêt mystique |
| **Rare** | `rare` | `rare.webp` | Nébuleuse cyan / Saphir énergétique |
| **Épique** | `epic` | `epic.webp` | Tempête améthyste / Pourpre arcanique |
| **Légendaire** | `legendary` | `legendary.webp` | Rayonnement doré flamboyant & dorures royales |
| **Mythique** | `mythic` | `mythic.webp` | Cosmos cramoisi, distorsion stellaire |

---

## 6. Gabarit & Règles de Dessin (Alignement)

Pour que n'importe quelle arme aille à n'importe quelle race, un **gabarit de référence (Mannequin Guide)** doit être utilisé dans votre logiciel de dessin (Photoshop, Krita, Procreate, Figma, Aseprite) :

1. **Repère Tête** : Sommet du crâne situé entre Y: 80 et Y: 110 px.
2. **Repère Yeux** : Ligne horizontale à Y: 160 px.
3. **Repère Main de Préhension (Arme)** :
   - Coordonnées du creux de la main droite : **X: 180 px, Y: 320 px**.
   - Angle standard de tenue de l'arme : **~45°** vers le haut/gauche.
4. **Variations morphologiques selon la Race** :
   - *Golem* : Silhouette plus massive et large d'épaules, mais la main de préhension doit impérativement rester dans la zone de tolérance (cercle de 20px de rayon autour de X:180, Y:320).
   - *Robot* : Structure mécanique anguleuse, jointures bien délimitées pour que l'arme semble fixée ou empoignée fermement.
   - *Démon / Vampire / Alien / Mutant* : Éléments distinctifs (cornes, capes sombres, tentacules, ailes) orientés vers l'arrière ou les côtés pour ne jamais masquer l'arme au Layer 3.
   - *Elfe / Humain* : Silhouettes classiques élancées, posture droite de 3/4 face.

---

## 7. Workflow d'Export & Optimisation Web

### Paramètres d'Exportation Recommandés
- **Logiciel** : Photoshop / Affinity / GIMP / Squoosh / cwebp
- **Format** : WebP
- **Qualité** : 82% à 88% (compression avec pertes douce)
- **Conservation de l'Alpha** : Oui (canal transparent préservé)
- **Métadonnées** : Supprimer toutes les métadonnées (EXIF, profil couleur ICC) pour réduire la taille de 5 Ko supplémentaire.

### Commande CLI Batch (Optionnel)
Si vous exportez des PNG depuis votre logiciel, vous pouvez tous les convertir en WebP ultra-optimisés avec la commande suivante :
```bash
# Exemple avec Google cwebp
cwebp -q 85 input.png -o output.webp
```

---

## 8. Intégration Blade / CSS (Exemple Pratique)

Dans le projet, le rendu du Paper Doll est entièrement encapsulé dans le composant réutilisable `<x-character-avatar>` qui utilise `PaperDollService` pour résoudre dynamiquement chaque calque avec fallback résilient :

```html
{{-- Utilisation directe dans n'importe quelle vue Blade ou Livewire (Collection, Roue, Arène) --}}
<x-character-avatar :character="$character" class="w-full max-w-[270px]" />
```

### Fonctionnement interne du composant (`resources/views/components/character-avatar.blade.php`) :

```php
@php
    // Résolution multi-calques par PaperDollService (détecte la présence sur disque dans public/images/characters/)
    $layers = \App\Services\PaperDollService::resolveLayers(
        $character->race_id,
        $character->weapon_id,
        $character->power_id,
        $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->value : $character->rarity
    );
    $hasVisualLayer = !empty($layers['race']) || !empty($layers['weapon']);
@endphp

@if ($hasVisualLayer)
    <div class="relative w-full max-w-[270px] aspect-square flex items-center justify-center select-none">
        
        {{-- Layer 0 : Fond d'aura / Décor de rareté --}}
        @if ($layers['background'])
            <img src="{{ asset($layers['background']) }}" 
                 alt="Fond" 
                 class="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none" 
                 loading="lazy">
        @endif

        {{-- Layer 1 : Race / Silhouette --}}
        @if ($layers['race'])
            <img src="{{ asset($layers['race']) }}" 
                 alt="Race" 
                 class="absolute inset-0 w-full h-full object-contain z-10 pointer-events-none filter drop-shadow-[0_8px_20px_rgba(0,0,0,0.6)]" 
                 loading="lazy">
        @endif

        {{-- Layer 2 : Effet Élémentaire / Pouvoir (VFX) --}}
        @if ($layers['power'])
            <img src="{{ asset($layers['power']) }}" 
                 alt="Pouvoir" 
                 class="absolute inset-0 w-full h-full object-contain z-20 pointer-events-none mix-blend-screen opacity-90" 
                 loading="lazy">
        @endif

        {{-- Layer 3 : Arme tenue (Toujours au premier plan) --}}
        @if ($layers['weapon'])
            <img src="{{ asset($layers['weapon']) }}" 
                 alt="Arme" 
                 class="absolute inset-0 w-full h-full object-contain z-30 pointer-events-none filter drop-shadow-[0_4px_12px_rgba(0,0,0,0.5)]" 
                 loading="lazy">
        @endif
    </div>
@else
    {{-- Fallback élégant avec piédestal d'emojis quand aucun calque n'est encore dessiné --}}
    <div class="border-2 border-dashed border-slate-700/80 rounded-2xl p-4 text-center">
        <span>Artwork en attente</span>
    </div>
@endif
```

---

## 9. Synthèse des Bénéfices de cette Approche

1. **Aucune distorsion visuelle** : Le portrait reste toujours un carré parfait (ratio 1:1), tandis que le châssis s'adapte en hauteur et largeur selon le support.
2. **Économie de bande passante** : Au lieu de stocker des milliers de cartes générées (plusieurs Go), vous n'avez que ~30 fichiers WebP légers (< 1 Mo pour tout le jeu).
3. **Plaisir de création** : Vous pouvez enrichir le jeu en ajoutant simplement 1 nouvelle arme ou 1 nouvelle race sans jamais toucher aux autres calques.

---

## 10. Checklist Récapitulative des Assets (30 Fichiers Cibles)

### Armes (8 fichiers dans `public/images/characters/weapons/`)
- [x] `epee.webp` (Épée ⚔️)
- [x] `baton.webp` (Bâton 🪄)
- [x] `arc.webp` (Arc 🏹)
- [x] `dague.webp` (Dague 🗡️)
- [ ] `marteau.webp` (Marteau 🔨)
- [ ] `lance.webp` (Lance 🔱)
- [ ] `pistolet.webp` (Pistolet 🔫)
- [ ] `grimoire.webp` (Grimoire 📖)
- *(Note : `hache.webp` et `bouclier.webp` créés précédemment ne sont pas utilisés dans les 8 armes officielles de combat)*

### Races (8 fichiers dans `public/images/characters/races/`)
- [x] `human.webp` (Humain 👤)
- [ ] `elf.webp` (Elfe 🧝)
- [ ] `demon.webp` (Démon 😈)
- [ ] `robot.webp` (Robot 🤖)
- [ ] `mutant.webp` (Mutant ☣️)
- [ ] `golem.webp` (Golem 🗿)
- [ ] `vampire.webp` (Vampire 🧛)
- [ ] `alien.webp` (Alien 👽)

### Pouvoirs / VFX Élémentaires (8 fichiers dans `public/images/characters/elements/`)
- [ ] `feu.webp` (Feu 🔥)
- [ ] `poison.webp` (Poison 🧪)
- [ ] `glace.webp` (Glace ❄️)
- [ ] `foudre.webp` (Foudre ⚡)
- [ ] `regeneration.webp` (Régénération 💖)
- [ ] `gravite.webp` (Gravité 🌌)
- [ ] `teleportation.webp` (Téléportation 🌀)
- [ ] `invisibilite.webp` (Invisibilité 👁️‍🗨️)

### Fonds de Rareté (6 fichiers dans `public/images/characters/backgrounds/`)
- [ ] `common.webp` (Commun)
- [ ] `uncommon.webp` (Peu Commun)
- [ ] `rare.webp` (Rare)
- [ ] `epic.webp` (Épique)
- [ ] `legendary.webp` (Légendaire)
- [ ] `mythic.webp` (Mythique)

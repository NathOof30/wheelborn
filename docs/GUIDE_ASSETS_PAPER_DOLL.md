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
| Synergies : [Elfe] [Foudre]                   |
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
- **Contenu** : Vortex magique, nébuleuse, étincelles ou runes liées à la rareté ou à l'élément.
- **Règle** : Doit être subtil et contrasté pour ne pas noyer le personnage (zones sombres au centre, lumière sur les bords).

### Layer 1 : Corps / Silhouette de la Race (`races/`)
- **Contenu** : Le personnage complet (tête, buste, membres, vêtements de base).
- **Règle vitale** : Le personnage doit avoir une **pose neutre standardisée** (ex: torse de 3/4 face, main droite légèrement avancée/ouverte pour recevoir l'arme du Layer 3).

### Layer 2 : Effet Élémentaire (`elements/`)
- **Contenu** : Tourbillons de feu, orbes d'eau, éclairs crépitants, brume d'ombre, rayons de lumière.
- **Règle** : Les effets peuvent envelopper le corps (ex: étincelles sur les épaules, aura autour des poings). Privilégier des zones semi-transparentes pour laisser transparaître le corps.

### Layer 3 : Arme Équipée (`weapons/`)
- **Contenu** : Épée, dague, bâton magique, hache, arc, etc.
- **Règle vitale** : La poignée de l'arme doit s'aligner **exactement** avec la main droite du gabarit universel du Layer 1.

---

## 5. Nomenclature Exacte des Fichiers (Mapping BDD)

Pour que le code Laravel / Livewire charge automatiquement les images sans condition compliquée, les noms de fichiers doivent correspondre **strictement** aux `slugs` ou identifiants utilisés dans la base de données.

Emplacement des dossiers dans le projet : `public/images/characters/`

### 1. Races (`public/images/characters/races/`)
- `humain.webp`
- `elfe.webp`
- `nain.webp`
- `orc.webp`
- `mort_vivant.webp`
- `demon.webp`
- `ange.webp`
- `hybride.webp`

### 2. Éléments / Pouvoirs (`public/images/characters/elements/`)
- `feu.webp`
- `eau.webp`
- `foudre.webp`
- `terre.webp`
- `vent.webp`
- `ombre.webp`
- `lumiere.webp`

### 3. Armes (`public/images/characters/weapons/`)
- `epee.webp`
- `dague.webp`
- `arc.webp`
- `baton.webp`
- `hache.webp`
- `bouclier.webp`
- `lance.webp`
- `grimoire.webp`

### 4. Fonds de Rareté (`public/images/characters/backgrounds/`)
- `common.webp`
- `uncommon.webp`
- `rare.webp`
- `epic.webp`
- `legendary.webp`
- `mythic.webp`

---

## 6. Gabarit & Règles de Dessin (Alignement)

Pour que n'importe quelle arme aille à n'importe quelle race, un **gabarit de référence (Mannequin Guide)** doit être utilisé dans votre logiciel de dessin (Photoshop, Krita, Procreate, Figma, Aseprite) :

1. **Repère Tête** : Sommet du crâne situé entre Y: 80 et Y: 110 px.
2. **Repère Yeux** : Ligne horizontale à Y: 160 px.
3. **Repère Main de Préhension (Arme)** :
   - Coordonnées du creux de la main droite : **X: 180 px, Y: 320 px**.
   - Angle standard de tenue de l'arme : **~45°** vers le haut/gauche.
4. **Variations morphologiques** :
   - *Nain* : Plus trapu et large, mais la main de préhension doit impérativement rester dans la zone de tolérance (cercle de 20px de rayon autour de X:180, Y:320).
   - *Orc* : Plus massif, muscles plus volumineux, mais main calée sur le même point.
   - *Elfe / Ange* : Silhouettes plus élancées, ailes déployées vers l'arrière pour ne pas masquer l'arme au Layer 3.

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

Voici comment le composant Blade assemble les calques Paper Doll dans le châssis responsive :

```html
<!-- Châssis Responsive de la Carte (Bordure, Dégradé et Halo gérés en CSS) -->
<div class="relative w-full max-w-[320px] rounded-2xl p-3 bg-slate-900/90 border-2 border-amber-500/60 shadow-[0_0_25px_rgba(245,158,11,0.25)] transition-transform hover:scale-[1.02]">

    <!-- Fenêtre Carrée du Portrait Paper Doll -->
    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-950 border border-slate-800">
        
        <!-- Layer 0 : Fond d'Aura de Rareté -->
        <img src="{{ asset('images/characters/backgrounds/' . $character->rarity . '.webp') }}" 
             alt="Background" 
             class="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none" 
             loading="lazy">

        <!-- Layer 1 : Race / Silhouette -->
        <img src="{{ asset('images/characters/races/' . $character->race_slug . '.webp') }}" 
             alt="{{ $character->race }}" 
             class="absolute inset-0 w-full h-full object-contain z-10 pointer-events-none" 
             loading="lazy">

        <!-- Layer 2 : Effet Élémentaire (VFX) -->
        <img src="{{ asset('images/characters/elements/' . $character->element_slug . '.webp') }}" 
             alt="{{ $character->element }}" 
             class="absolute inset-0 w-full h-full object-contain z-20 pointer-events-none mix-blend-screen opacity-90" 
             loading="lazy">

        <!-- Layer 3 : Arme Équipée -->
        <img src="{{ asset('images/characters/weapons/' . $character->weapon_slug . '.webp') }}" 
             alt="{{ $character->weapon }}" 
             class="absolute inset-0 w-full h-full object-contain z-30 pointer-events-none" 
             loading="lazy">
             
    </div>

    <!-- Informations & Statistiques de la Carte (S'étirent sans contrainte) -->
    <div class="mt-3 text-center">
        <h3 class="font-bold text-white text-base truncate">{{ $character->name }}</h3>
        <p class="text-xs text-amber-400 font-semibold uppercase tracking-wider">{{ $character->rarity }}</p>
    </div>
</div>
```

---

## 9. Synthèse des Bénéfices de cette Approche

1. **Aucune distorsion visuelle** : Le portrait reste toujours un carré parfait (ratio 1:1), tandis que le châssis s'adapte en hauteur et largeur selon le support.
2. **Économie de bande passante** : Au lieu de stocker des milliers de cartes générées (plusieurs Go), vous n'avez que ~30 fichiers WebP légers (< 1 Mo pour tout le jeu).
3. **Plaisir de création** : Vous pouvez enrichir le jeu en ajoutant simplement 1 nouvelle arme ou 1 nouvelle race sans jamais toucher aux autres calques.

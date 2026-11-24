# Fiche de Direction Artistique — « Grimoire Vivant & Sceaux sur Papier »

> **Objectif de ce document :** Servir de boussole visuelle et d'atelier d'idées pour prototyper la Direction Artistique (DA) sur l'application web de génération, collection et combat de cartes.  
> Les éléments ci-dessous ne sont pas des règles figées : ce sont des **directives, des pistes créatives et des exemples d'adaptation** basés sur le prototype HTML analysé (`Codex Anomalium`).

---

## 1. Vision & Fil Conducteur

### Le concept adapté au projet
Dans le prototype HTML, l'univers est centré sur un bestiaire purement médiéval (escargots, hybrides de marge). Pour coller à ton projet (races fantasy : *Humains, Elfes, Démons* ; équipement : *Bâton magique, Dague* ; pouvoirs : *Feu, Glace* ; système de **points** et de **combats**), la piste la plus forte consiste à faire évoluer le concept vers un **Grimoire de Confinement / Codex Arcanique** :

* **La métaphore :** Chaque carte n'est pas une carte à jouer moderne plastifiée, mais un **folio enchanté** (une page de parchemin ou un talisman de papier scellé) dans lequel une entité, une arme et un sortilège ont été invoqués et fixés à l'encre et à la cire.
* **Le lien avec la magie :** L'ajout de **sceaux magiques à l'ancienne tracés sur papier** (cercles géométriques fins, sigils à l'encre rouge ou dorée, glyphes en filigrane) permet de justifier naturellement :
  1. **La génération aléatoire** (un rituel d'invocation qui imprime une entité sur une page vierge).
  2. **Les pouvoirs élémentaires** (encres alchimiques : cinabre pour le feu, outremer pour la glace, etc.).
  3. **Les combats de cartes** (deux parchemins scellés posés sur l'établi en bois dont on brise les sceaux pour libérer leur puissance).

---

## 2. Bilan du Prototype HTML : Ce qu'on garde vs Ce qu'on fait évoluer

| Élément du prototype | Verdict | Analyse & Piste d'adaptation |
| :--- | :--- | :--- |
| **Le décor (Table sombre + Cuir + Fermoirs métal + Vélin)** | **À conserver** | Très fort effet « objet précieux ». Le contraste entre le fond sombre (`#1E1408`), le cuir brun, le laiton vieilli des fermoirs et la chaleur du vélin (`#EBE0C8`) met immédiatement le contenu en valeur. |
| **Les Lettrines enluminées** | **À conserver & exploiter** | Superbe point d'ancrage visuel. Peut servir d'initiale du nom, ou de badge identifiant la **Race** (ex. un *E* orné de feuilles pour Elfe, un *D* cramoisi pour Démon). |
| **La palette (Vélin, Encre, Rouge cire, Or)** | **À conserver & enrichir** | Très cohérente et lisible. À compléter subtilement avec des pigments anciens (comme le `--outremer` déjà présent dans le CSS mais inutilisé) pour distinguer les pouvoirs/éléments. |
| **La navigation latérale (Signets de cuir)** | **À conserver & adapter** | Excellente trouvaille UX/DA. L'effet de languette qui glisse au survol et passe en doré à l'état actif est très réussi. Il faudra prévoir une **version responsive** (actuellement masquée sous `1180px`). |
| **Les mises en page « copiste » (filets pointillés)** | **À conserver** | La présentation `Label ........ Valeur` est ultra lisible et immédiatement typée manuscrit. Parfaite pour afficher Race, Arme, Pouvoir et Points. |
| **Les boutons (Sceaux ovales étirés)** | **À revoir** | Dans le HTML, `border-radius: 50% / 40%` sur des boutons larges donne un rendu un peu « aplati » ou « pastille » qui perd le côté crédible du sceau. Voir les 3 pistes alternatives en section 6. |
| **Les marginalia animées (mouche, escargot, gouttes)** | **À doser** | Très sympa en petite touche d'ambiance (ex. dans les marges du Codex global), mais à éviter en surnombre sur une grille de 15 cartes pour préserver la lisibilité et les performances. |
| **La police gothique (`UnifrakturCook`)** | **À encadrer** | Magnifique pour les grands titres et les lettrines, mais difficile à lire en petit corps ou sur des noms complexes. À réserver aux éléments courts et décoratifs. |

---

## 3. Système Visuel : Palette & Matières

### A. Palette cœur (extraite du prototype)
L'équilibre repose sur une base neutre chaude (papier + encre brune) réveillée par deux couleurs nobles (**Or** et **Rouge cinabre/cire**).

* **Fonds & Supports**
  * Bureau / Établi sombre : `#1E1408` à `#2A1A0A`
  * Cuir de reliure : `#4A1F0A` (clair `#6B3218`, sombre `#2A0E04`)
  * Vélin principal : `#EBE0C8` (clair `#F5EDD8`, ombré `#D9CBA8`, bordure `#C8B88F`)
* **Encres & Textes**
  * Encre principale : `#3A2A1A` (jamais de noir pur `#000`, toujours un brun très sombre)
  * Encre adoucie : `#5A4530` | Sépia (notes, sous-titres) : `#6B4A2A` | Encre fanée : `#8A7555`
* **Accents Précieux & Métal**
  * Or enluminé : `#B8923A` (reflet `#D8B860`, ombre `#8A6E20`)
  * Laiton / Métal des fermoirs : `#8A7048` (reflet `#C8A870`, ombre `#4A3A18`)
  * Rouge cire / Encre rubriquée : `#8A1A14` (reflet `#B3261E`)

### B. Piste d'extension : Pigments Alchimiques (pour tes Pouvoirs & Races)
Pour ne pas casser l'ambiance médiévale avec des couleurs fluo ou modernes, chaque pouvoir ou race peut être associé à un **pigment historique** légèrement désaturé :

* **Feu / Démon :** Cinabre & Braise (`#8A1A14` / `#B33A1E`)
* **Glace / Arcane :** Lapis-lazuli / Outremer (`#2A3A7A` — *déjà présent dans le CSS !* — / `#4A6690`)
* **Nature / Elfe :** Vert-de-gris / Vert herbier (`#5A6B47` / `#3E5438`)
* **Lumière / Humain / Sacré :** Ocre d'or (`#B8923A`) ou Acier bruni (`#5C646B`)
* **Ombre / Poison / Malédiction :** Pourpre de Tyr / Prune sombre (`#54274A`)

### C. Recettes de matières (pistes techniques CSS/SVG)
* **Grain de papier & cuir :** Utilisation d'un `feTurbulence` SVG en `data-uri` avec une faible opacité (`0.06` à `0.18`), superposé à plusieurs `radial-gradient` très doux pour simuler l'usure, l'humidité et les taches d'âge.
* **Effet métallique (fermoirs, cadres d'interface, boutons) :** Dégradés linéaires en 3 étapes (`--metal-light` -> `--metal` -> `--metal-dark`) + `inset box-shadow` clair en haut (reflet) et sombre en bas (biseau) + petits rivets circulaires aux extrémités.
* **Profondeur du livre :** Ombres internes (`inset`) sur la page pour simuler la courbure près de la reliure, et tranche dorée simulée par un `repeating-linear-gradient` fin sur le bord droit.

---

## 4. Typographie & Lisibilité

| Rôle dans l'app | Police suggérée | Usage & Précautions |
| :--- | :--- | :--- |
| **Titres majeurs, Lettrines, Chapitres** | `UnifrakturCook` *(ou alternative plus lisible si besoin, ex: `Pirata One` ou `MedievalSharp`)* | **Directive :** À réserver aux titres courts (1 à 3 mots) et à grande taille (`> 24px`). Éviter le tout-majuscules en gothique (illisible). |
| **Sous-titres, Labels, Boutons, Navigation** | `Cormorant Garamond` | Excellent en *italique* pour l'ambiance littéraire, et en `small-caps` (petites capitales) avec un `letter-spacing` généreux (`1.5px` à `3px`) pour les étiquettes d'interface. |
| **Corps de texte, Descriptions** | `EB Garamond` | Très confortable à lire sur fond vélin, même à `14px–16px`. |
| **Notes de marge, Citations, Lore** | `IM Fell English` | Donne un aspect « impression ancienne imparfaite / note manuscrite » très authentique en encre sépia. |
| **Points, Scores, Stats de combat (Manquant dans le HTML)** | `Cormorant Garamond` (avec `font-variant-numeric: lining-nums tabular-nums`) ou `Cinzel` | **Important pour ton projet :** Les points doivent sauter aux yeux en 0,5 seconde, notamment en combat. Des chiffres alignés, nets, éventuellement entourés d'un cartouche doré ou d'un petit sceau. |

---

## 5. Anatomie de la Carte (« Le Folio Scellé »)

Comment passer de la carte « Bestiaire » du HTML à une **vraie carte de jeu** (Race + Arme + Pouvoir + Points) ?

### Proposition de structure (de haut en bas)
1. **Bandeau supérieur (En-tête de folio)**
   * *À gauche :* Catégorie ou référence (ex. `FOLIO N° 042` ou la **Race** en petites capitales).
   * *À droite :* **Le Score / Les Points de la carte** mis en valeur dans un petit médaillon métallique, une couronne de laurier fine ou un losange doré (pour remplacer ou compléter le simple `F° 32r`).
2. **Zone Titre + Lettrine**
   * La **Lettrine enluminée** (carré doré/rouge en haut à gauche) affiche l'initiale du personnage ou un glyphe de race.
   * Le nom de la carte à droite, suivi d'un sous-titre court (ex. *« Elfe · Porteur de Dague »*).
3. **Zone Illustration & Sceau Magique (Le cœur visuel)**
   * L'illustration de la créature/personnage au centre (style gravure, encre avec rehauts de couleur).
   * **Idée « Magie sur papier » :** En arrière-plan derrière l'illustration (très basse opacité, `10%–20%`), un **cercle de sceau arcanique / géométrie sacrée** dessiné en SVG fin, dont le motif et la couleur dépendent du **Pouvoir** (ex. cercles flamboyants rouges pour Feu, étoile géométrique bleue pour Glace).
4. **Table des attributs (Style copiste)**
   * Présentation avec filets pointillés (`dt` ... `dd`) reprise du prototype :
     * `Race` ............ *Démon*
     * `Arme` ............ *Bâton magique*
     * `Pouvoir` ......... *Feu*
   * *Piste d'enrichissement :* Ajouter une minuscule icône encre/or devant chaque valeur, ou afficher la contribution en points de chaque élément (ex. `+15 pts`).
5. **Pied de carte : Note & Sceau**
   * Une courte note sépia optionnelle (lore généré ou phrase d'ambiance).
   * En bas à droite (ou centré en bas), un **Sceau de cire ou Cachet de rareté** : sa couleur ou son métal peut refléter le palier de puissance de la carte (ex. *Cire brune/verte* = Commun, *Cire rouge* = Rare, *Cire dorée / Sceau double* = Légendaire).

---

## 6. Composants UI : Pistes pour revoir les Boutons & la Navigation

### A. Repenser les Boutons (Point prioritaire)
Pour remplacer les boutons-sceaux ovales du HTML qui manquent un peu de tenue sur les textes longs, voici **3 pistes complémentaires** qui utilisent le vocabulaire visuel que tu aimes (métal, or, papier, encre rouge) :

1. **Piste « Plaque de Laiton / Fermoir » (Action principale d'interface)**
   * *Idée :* Reprendre exactement le style des fermoirs métalliques du codex (`.clasp`) : un rectangle aux coins légèrement arrondis ou biseautés, dégradé laiton/or vieilli, deux petits rivets sur les côtés, texte gravé en petites capitales.
   * *Effet au survol :* Un léger reflet métallique qui glisse en diagonale, ou une lueur chaude sur la gravure.
   * *Idéal pour :* « Lancer le combat », « Ouvrir la collection », « Confirmer ».

2. **Piste « Cartouche Enluminé / Encre & Or » (Actions secondaires & filtres)**
   * *Idée :* Un bouton plat intégré au papier, avec un double filet fin doré ou brun (style cadre de manuscrit), des petits losanges ou fleurons aux extrémités gauche/droite.
   * *Effet au survol :* Le fond se teinte d'encre rouge sombre (`#8A1A14`) ou d'un lavis doré transparent, et le texte passe en vélin clair.
   * *Idéal pour :* Filtres de la collection, tri par points, boutons « Retour », « Annuler ».

3. **Piste « Vrai Sceau de Cire / Cachet Rituel » (Strictement rond, usage exceptionnel)**
   * *Idée :* Garder le sceau de cire rouge **uniquement pour 1 action emblématique** (ex. le gros bouton rond **« INVOQUER »** ou **« SCELLER »**). Le garder parfaitement circulaire (`aspect-ratio: 1 / 1`) avec un symbole ou un mot court au centre, et un bord légèrement irrégulier.
   * *Effet au clic :* Enfoncement net comme un tampon qu'on presse dans la cire chaude.

### B. La Navigation (Signets latéraux)
Le système de **signets de cuir** sur la droite est une excellente signature visuelle pour naviguer entre les grands chapitres de ton app :
* Exemples de chapitres : **I. Invocation** (Générateur) · **II. Grimoire** (Collection) · **III. Arène / Duel** (Combat) · **IV. Compendium** (Races/Armes/Pouvoirs).
* **Corrections à prévoir par rapport au HTML :**
  * *Ancrage :* Attacher visuellement les signets au bord droit du `.codex` (et non flottants loin au bord de l'écran sur les très grands moniteurs), ou les garder `fixed` mais bien alignés.
  * *Responsive (< 1180px) :* Au lieu de les cacher (`display: none`), les transformer en **onglets de cuir ou rubans** qui dépassent du haut de la couverture du codex.

### C. États d'interface (Feedbacks narratifs)
Conserver l'idée très maline du HTML de traduire les états techniques dans le langage du grimoire :
* **Collection vide :** *« Folio vierge — Aucune entité n'a encore été scellée dans ce chapitre. »*
* **Chargement / Génération :** *« Traçage du sceau en cours… »* (animation d'un cercle magique ou d'un trait de plume doré qui se dessine en SVG `stroke-dashoffset`).
* **Erreur :** *« Le sceau s'est brisé — L'invocation a échoué. »* (cadre teinté d'encre rouge).

---

## 7. Vie, Magie & Interactions (Spécial Génération & Combat)

### A. Le « Vivant » subtil (sans surcharger)
* **À garder partout :** Le coin de page qui se soulève légèrement au survol, la micro-élévation des cartes, le clignement discret de la lettrine ou un léger miroitement doré sur les cartes à haut score.
* **À réserver au décor global :** Les petites animations autonomes (fleurons qui respirent, poussière/mouche, goutte d'encre) fonctionnent mieux en tant que détails rares dans les marges de la page principale, plutôt que dupliquées sur chaque carte.
* **Accessibilité :** Toujours conserver le bloc `@media (prefers-reduced-motion: reduce)` déjà présent dans le prototype.

### B. Pistes d'animations « Magie de Sceaux » pour tes mécaniques clés
* **Lors de la génération d'une carte :**
  1. Le parchemin apparaît vierge ou flouté.
  2. Le cercle magique / sceau en filigrane se trace rapidement à l'encre rouge ou or.
  3. L'illustration et les stats (Race, Arme, Pouvoir) « s'impriment » dans le papier (`opacity` + léger décalage vertical).
  4. Le sceau de cire s'écrase en dernier pour verrouiller la carte et révéler son **total de points**.
* **Lors d'un Combat entre deux cartes (Piste de mise en scène) :**
  * Poser les **deux cartes-parchemins côte à côte** sur la table en bois sombre (ou sur une double page ouverte du Codex), séparées par un **sceau d'affrontement** central en laiton ou en cire (ex. un médaillon *« VS »* ou deux épées gravées).
  * Au déclenchement du duel : les sceaux magiques en arrière-plan des deux cartes s'illuminent selon leur pouvoir (halo rouge braise, bleu givre, or).
  * **Résultat :** La carte gagnante reçoit un tampon doré *« VICTORIEUX »* (ou un halo doré et un gain de points), tandis que le sceau de la carte perdante se fissure ou se teinte d'encre sépia/grisée (*« VAINCU »*).

---

## 8. Feuille de Route : Prototyper sur le Projet

Pour tester cette DA rapidement sur ta vraie web app sans tout bloquer :

1. **Étape 1 — Fondations (Tokens CSS)**
   * Importer les 4 polices Google Fonts et définir les variables `:root` (vélin, encres, or, métal, pigments élémentaires).
   * Mettre en place le fond « table sombre » + le conteneur principal « Codex relié ».
2. **Étape 2 — Navigation & Boutons V2**
   * Implémenter les signets latéraux reliés aux vraies routes/vues de l'app (avec fallback mobile en haut).
   * Créer et tester 2 variantes de nouveaux boutons (ex. *Plaque de laiton* + *Cartouche enluminé* + 1 *Sceau rond* pour l'invocation) pour valider ce qui te plaît le plus.
3. **Étape 3 — Composant « Carte-Folio »**
   * Construire le composant de carte connecté à tes vraies données (Race, Arme, Pouvoir, Points).
   * Tester un filigrane SVG de sceau magique derrière l'illustration qui change de couleur selon le pouvoir.
4. **Étape 4 — Vue Collection & Vue Combat**
   * Vérifier que la grille de collection reste aérée et lisible avec 6 à 12 cartes affichées (ajuster les ombres et marges si besoin).
   * Prototyper l'écran de duel (2 cartes face à face + comparaison des points + feedback de victoire).
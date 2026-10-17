# 🎡 WHEELBORN

> **Projet Universitaire Full-Stack**  
> Générateur procédural de combattants, duels en arène, calcul d'Élo mathématique (FIDE) et économie circulaire de recyclage.  
> **Stack :** Laravel 11 • Livewire 3/4 • Tailwind CSS • Alpine.js • SQLite / MySQL

---

## ⚡ Démarrage Rapide (Pour les collaborateurs)

Si tu viens de cloner le projet, suis ces 5 étapes pour être prêt en moins de 3 minutes :

### 1. Prérequis
- **PHP 8.2+** (avec extensions `pdo_sqlite`, `curl`, `mbstring`)
- **Composer 2.x**
- **Node.js 18+** & npm

### 2. Installation des dépendances

```bash
# Dépendances PHP
composer install

# Dépendances front-end (Vite, Tailwind, etc.)
npm install
```

### 3. Fichier d'environnement & Clé de sécurité

```bash
# Sous Windows (PowerShell) :
Copy-Item .env.example .env

# Sous Linux / macOS / Bash :
cp .env.example .env

# Génération de la clé d'application
php artisan key:generate
```

### 4. Base de Données & Données de Test (Seeders)

Le projet utilise **SQLite** par défaut (zéro configuration requise).  
La commande suivante crée les tables et injecte les comptes de test et le compte administrateur :

```bash
php artisan migrate --seed
```

### 5. Lancer l'application en développement

Ouvre **deux terminaux** :

```bash
# Terminal 1 : Serveur PHP Laravel
php artisan serve
# Accessible sur http://127.0.0.1:8000

# Terminal 2 : Compilateur Vite (Tailwind CSS + Hot Module Reload)
npm run dev
```

---

## 🔑 Comptes de Test Disponibles

Après avoir exécuté `php artisan migrate --seed`, ces comptes sont immédiatement utilisables :

| Rôle | Email | Mot de passe | Privilèges |
|:---|:---|:---|:---|
| **Administrateur** | `admin@wheelborn.test` | `password` | **Tirages illimités (`⚡ ∞`)**, **Argent infini (`999 999 🪙`)**, panneau d'outils de test dans l'interface |
| **Joueur test** | `nathanael.leang@gmail.com` | `password` | Également configuré avec privilèges administrateur |

> [!TIP]
> **Promouvoir un compte en Admin à tout moment :**  
> Si tu t'inscris avec ton propre email via `/register`, tape simplement dans ton terminal :  
> `php artisan user:admin ton_email@exemple.com`

---

## 🏗️ Architecture du Projet

Le projet respecte une séparation stricte entre logique métier et interface réactive :

```
wheelborn/
├── app/
│   ├── Livewire/              # 🎡 Composants réactifs full-stack
│   │   └── CharacterWheel.php #   Roue de tirage, jauge Pity 10/10, modale révélation
│   ├── Services/              # ⚙️ Logique métier pure (indépendante de l'UI)
│   │   ├── CharacterGeneratorService.php # Moteur de génération procédurale & rareté
│   │   ├── EnergyManagerService.php      # Gestion de la régénération temporelle
│   │   ├── RecycleService.php            # Recyclage sécurisé en or (transactions BDD)
│   │   └── EloService.php                # Formule d'Élo FIDE & matchmaking
│   ├── Models/                # 🗄️ Modèles Eloquent & relations
│   │   ├── User.php           #   Joueur, monnaies, charges, stats
│   │   ├── Character.php      #   Combattant avec UUID, stats, rareté, synergies
│   │   ├── Battle.php         #   Historique des combats et seeds
│   │   ├── ArenaSession.php   #   Sessions d'arène virtuelle (3 cartes)
│   │   ├── WorkshopUpgrade.php#   Améliorations d'atelier achetées
│   │   ├── PlayerStat.php     #   Statistiques globales du joueur
│   │   └── Achievement.php    #   Succès débloqués
│   ├── Enums/                 # 🏷️ Énumérations PHP 8.2 typées
│   │   ├── Rarity.php         #   Common, Rare, Epic, Legendary (couleurs, multiplicateurs)
│   │   ├── PowerTier.php      #   Common, Rare, Mythic
│   │   └── League.php         #   Bronze, Silver, Gold, Platinum, Master
│   └── Data/                  # 📊 Fichiers de données statiques du jeu
│       ├── RaceData.php       #   8 Races (stats mods, passifs)
│       ├── WeaponData.php     #   8 Armes (types, dégâts, passifs)
│       ├── PowerData.php      #   8 Pouvoirs élémentaires
│       ├── SynergyData.php    #   16 Synergies (dont 4 combinaisons instables)
│       ├── GameConfig.php     #   Constantes d'équilibrage et règles
│       └── NameGenerator.php  #   Générateur de noms procéduraux
├── resources/
│   ├── views/
│   │   ├── layouts/           # Layout principal dark néon (`app.blade.php`, `navigation.blade.php`)
│   │   ├── livewire/          # Templates Blade des composants réactifs
│   │   └── welcome.blade.php  # Page d'accueil pour les visiteurs non connectés
│   └── css/app.css            # Thème sombre, variables CSS et animations néon
├── tests/                     # 🧪 Suite de tests automatisés (PHPUnit)
└── docs/                      # 📚 Documentation complète du projet (voir ci-dessous)
```

---

## 📚 Documentation Complète (`docs/`)

Toute la conception du jeu et les spécifications techniques sont centralisées dans le dossier [`docs/`](docs/) :

| Document | Description |
|:---|:---|
| [**DOCUMENTATION_DU_JEU.md**](docs/DOCUMENTATION_DU_JEU.md) | **Bible du Game Design (GDD)** : Règles du jeu, 8 races, 8 armes, 8 pouvoirs, synergies, formules mathématiques et Paper Doll. |
| [**NOUVELLE_BOUCLE_GAMEPLAY.md**](docs/NOUVELLE_BOUCLE_GAMEPLAY.md) | **Boucle de gameplay V2** : Sessions Coffee Break (5-10 min), jauge de Pity 10/10, recyclage en pièces et arbre de l'Atelier. |
| [**PLAN_PORTAGE_LARAVEL.md**](docs/PLAN_PORTAGE_LARAVEL.md) | **Spécifications techniques complètes** : Schémas SQL, mapping JS → Livewire, sécurité anti-triche et architecture détaillée. |
| [**STRATEGIES_TECHNIQUES.md**](docs/STRATEGIES_TECHNIQUES.md) | **Détails avancés** : Match Rapide vs Arène AFK, gestion des bots, cache Redis/Database et tests unitaires. |
| [**TUTO_DEMARRAGE_LARAVEL.md**](docs/TUTO_DEMARRAGE_LARAVEL.md) | **Guide pas-à-pas & Checklist de progression** du projet. |
| [**ANALYSE_FAISABILITE.md**](docs/ANALYSE_FAISABILITE.md) | **Périmètre & Roadmap** : Priorités MVP vs post-MVP et comparatif d'hébergements. |

---

## 🧪 Tests Automatisés

Le projet inclut une suite de tests unitaires et fonctionnels validant le bon fonctionnement de l'authentification et des composants Livewire :

```bash
# Exécuter tous les tests
php artisan test

# Exécuter uniquement les tests de la Roue Livewire
php artisan test --filter=CharacterWheelTest
```

---

## 📋 Prochaines Étapes pour l'Équipe

Consulte la checklist dans [`docs/TUTO_DEMARRAGE_LARAVEL.md`](docs/TUTO_DEMARRAGE_LARAVEL.md) pour suivre l'avancement :
- [x] Phase 1 : Fondations, BDD, Enums, Modèles, Layout dark et Livewire 3
- [x] Phase 2.1 : Roue de tirage (`CharacterWheel`), Super Spin 10/10, compte Admin
- [ ] Phase 2.2 : Grille de collection de cartes (`CollectionGrid`) avec filtres et recyclage
- [ ] Phase 3 : Moteur de combat 1v1 (`BattleEngineService`), Arène et Classement Élo (`Leaderboard`)
- [ ] Phase 4 : Atelier d'améliorations (`Workshop`) et succès

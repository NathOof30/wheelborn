# 🏛️ Épic 4 — Économie Circulaire & Atelier (`Workshop`)

> **Statut :** ✅ Terminé (100% - 5/5 tickets validés)  
> **Responsable principal :** 🧑‍💻 Mathéo  

---

### [x] T4.1 — Modèle, Migration & Service `WorkshopService`
- **Priorité :** 🔴 P0 (Boucle de Rétention V2)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/WorkshopService.php`, `app/Models/WorkshopUpgrade.php`, `tests/Feature/WorkshopServiceTest.php`
- **Réalisations validées :**
  - [x] Définition des 3 arbres d'améliorations :
    1. *Aimant à Pouvoirs Rares* (Niveaux 1 à 5 : +3% par niveau de chance de pouvoirs épiques/mythiques).
    2. *Forge des Héros* (Niveaux 1 à 5 : +2 Force et +3 PV constants sur les futurs tirages).
    3. *Coffre de Guerre* (Niveaux 1 à 3 : +25%, +50%, +100% d'or par victoire d'arène).
  - [x] Vérification stricte du solde de pièces avant achat et déduction sécurisée.
  - [x] Sauvegarde du niveau de chaque amélioration par utilisateur.
  - [x] Validé par `tests/Feature/WorkshopServiceTest.php`.

---

### [x] T4.2 — Intégration des bonus de l'Atelier dans le Moteur de Génération
- **Priorité :** 🔴 P0 (Cohérence Gameplay)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/CharacterGeneratorService.php`, `tests/Feature/CharacterGeneratorWorkshopTest.php`
- **Réalisations validées :**
  - [x] Injection du niveau de l'Aimant à Pouvoirs pour modifier les probabilités de `PowerData`.
  - [x] Injection du niveau de la Forge des Héros pour ajouter les points de stats bonus lors de la création du personnage.
  - [x] Exclusion des bots d'arène pour ne pas dérégler leur équilibre.
  - [x] Validé par `tests/Feature/CharacterGeneratorWorkshopTest.php`.

---

### [x] T4.3 — Ticket d'Urgence (Achat de tirage immédiat)
- **Priorité :** 🟡 P1 (Confort de Jeu)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Services/WorkshopService.php`, `app/Livewire/CharacterWheel.php`, `tests/Feature/EmergencySpinTest.php`
- **Réalisations validées :**
  - [x] Achat d'une charge de spin pour 120 pièces d'or quand le joueur est à 0 charge.
  - [x] Limitation : maximum 2 achats par tranche de 24 heures (coût progressif 120 🪙 $\rightarrow$ 250 🪙 $\rightarrow$ blocage).
  - [x] Validé par `tests/Feature/EmergencySpinTest.php`.

---

### [x] T4.4 — Composant Livewire `Workshop`
- **Priorité :** 🟡 P1 (Interface Dédiée)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Livewire/Workshop.php`, `resources/views/livewire/workshop.blade.php`, `tests/Feature/WorkshopPageTest.php`
- **Réalisations validées :**
  - [x] Cartes d'améliorations avec barres de progression de niveau (ex: Niv. 2/5).
  - [x] Bouton d'achat dynamique affichant le coût du niveau suivant.
  - [x] Feedback visuel en cas de fonds insuffisants.
  - [x] Animation de déblocage avec notification de succès.
  - [x] Validé par `tests/Feature/WorkshopPageTest.php`.

---

### [x] T4.5 — Succès & Déblocages Spéciaux (`Achievements`)
- **Priorité :** 🟢 P2 (Accomplissements & Bourses)
- **Attribué à :** 🧑‍💻 Mathéo
- **Cible :** `app/Models/Achievement.php`, `app/Listeners/ProcessAchievements.php`, `tests/Feature/AchievementTest.php`
- **Réalisations validées :**
  - [x] Succès "Premier Sang" (1ère victoire en arène).
  - [x] Succès "Maître Alchimiste" (Recycler 10 cartes).
  - [x] Succès "Bénédiction Divine" (Obtenir une carte Légendaire).
  - [x] Succès "Combinaison Instable" (Tirer une synergie à risque).
  - [x] Attribution automatique des récompenses en or et déblocage de titres honorifiques.
  - [x] Validé par `tests/Feature/AchievementTest.php`.


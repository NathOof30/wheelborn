---
trigger: always_on
description: "Règles d'audit et de rigueur pour le projet WHEELBORN"
---

# Règles d'audit WHEELBORN

- Toute affirmation doit être liée à un fichier et une ligne quand c'est possible.
- Signaler explicitement les zones non couvertes par les tests comme « risque non vérifié ».
- Ne jamais proposer un correctif sans avoir identifié la cause racine.
- Pour les transactions (recyclage, charges, jauge de Pity), vérifier systématiquement les conditions de course et l'atomicité avant de conclure.
- Le compte `player@wheelborn.test` (collection vide) est la référence pour juger l'expérience débutant : toute friction sur ce parcours est prioritaire.
- Ne pas modifier de fichier pendant la phase d'audit ; attendre la validation du plan.
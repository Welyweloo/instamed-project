# Instamed

Ce projet a été réalisé par un groupe de 5 personnes dans le cadre d'un Hackaton réalisé durant le mois de Novembre 2021.

Contexte : Instamed met a disposition une application permettant de mutualiser les données au sein du parcours médical d'un patient.

Objectif du développement :
- Créer un script d'import et d'update de données provenant de l'annuaire médical national (fichiers de 300Mo) à lancer en ligne de commande
- Créer une API 
- Automatiser l'update via une Tâche CRON
- Permettre l'authentification sur l'API (optionnel)

>[Voir la Démonstration](https://www.youtube.com/watch?v=657P00zlX3c)

Contributors
--

Alexis : Tâche CRON
Aurélie : Installation du projet, Script d'import, Tests Unitaires 
Barthélémy : Chef de projet
Maxime : Préparation de l'environnement de développement, Téléchargement des fichiers 
Thomas : Script d'update


Content
--

Les données proviennent du site suivant : https://annuaire.sante.fr/web/site-pro/extractions-publiques

Realisation
--

J'ai pu m'exercer sur la création de commandes Symfony et tests unitaires.

- Temps pris : 5 jours de développement en Novembre 2020
  
- Configuration imposée par le client:
  - PHP Symfony
  - API Platform
  - MySQL


- Difficultés rencontrées par le groupe :
    - L'update de fichiers conséquents vers la base de donnée : Problème de mémoire de stockage
    - L'organisation du code 
    - La méconnaissance des outils pour certains
    - Le manque d'autonomie pour certaines
    - Le temps réduit


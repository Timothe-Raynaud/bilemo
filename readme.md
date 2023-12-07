# Projet - Bilemo

## Installation

Voici les différentes étapes d'installation du projet en local :
- Lancer la commande : ```composer install```
- Paramétrer un __.env.local__ pour qu'il ait access à une base de données.
- Lancer les commandes suivantes en acceptant avec 'yes' lorsque nécessaire :
    - ```php bin/console doctrine:database:create```
    - ```php bin/console doctrine:migrations:migrate```
    - ```php bin/console doctrine:fixtures:load```

## Livrable

À la racine du projet, vous trouverez un dossier ___livrable___ comprenant les different élement demandé pour le livrable :
- Un dossier diagramme comprenant tous les diagrammes.
- Un fichier ___api.html___ comprenant la documentation (Elle est aussi accessible via le site à l'url ```/api/doc```)
- Un fichier ___symfonyInsight.txt___ avec un lien vers la dernière analyse du projet.

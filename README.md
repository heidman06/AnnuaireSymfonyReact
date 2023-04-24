# Mon Projet Symfony

Ce projet utilise le framework Symfony. Voici les étapes à suivre pour installer et exécuter ce projet sur votre machine locale.

## Prérequis

- PHP 8.2.3 ou supérieur
- Activer l'extension LDAP dans le php.ini
- Composer
- Node.js et npm
- Symfony CLI

## Installation

1. Clonez ou téléchargez le dépôt git :

        git clone https://iut-git.unice.fr/tm101686/sae-401-equipe-4c.git

2. Accédez au répertoire du projet :

        cd ReactSymfony

3. Installez les dépendances PHP avec Composer :

        composer install

4. Installez les dépendances Node.js avec npm :

        npm install

3. Compiler les composants React.

        npm run build

5. Générez les fichiers de production :

        symfony serve

## Lancer le serveur

Utilisez la CLI Symfony pour démarrer le serveur de développement :


Ouvrez votre navigateur et accédez à l'URL indiquée par la CLI Symfony (généralement `http://127.0.0.1:8000`).

## En cas de problème

Si vous rencontrez des problèmes lors de l'installation ou de l'exécution du projet, consultez la documentation officielle de Symfony : https://symfony.com/doc/current/index.html


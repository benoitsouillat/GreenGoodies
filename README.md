# GreenGoodies - E-commerce

## Installation utilisant Make
    *(requiert un environnement Linux)*

    Pré-requis: 
        - installateur make
        - docker

   **Configuration du projet**
    Commande pour accéder à l'ensemble des commandes make disponible pour ce projet
```bash
    make help
```

    Éditez le fichier `.env.docker` avec les paramètres souhaités :
```bash
    DB_USER=votre_utilisateur
    DB_PASSWORD=votre_mot_de_passe
    DB_NAME=votre_base_de_donnees
    # Ainsi que les ports souhaités pour accéder à votre site
```

   **Lancer l'installation**
```bash
   make install
```
   Cette commande va automatiquement :
   - ✅ Générer le fichier `.env.local` avec les bonnes variables
   - ✅ Construire et démarrer les conteneurs Docker
   - ✅ Installer les dépendances Composer
   - ✅ Créer la base de données et exécuter les migrations
   - ✅ Compiler les fichiers SCSS

   **Générer les fixtures**
```bash
   make fixtures
```
    ⚠️ Cette commande est protégée pour ne pas fonctionner en environnement de production

   **Accéder au projet**
   - Application : `http://localhost:8181` (ou le port configuré dans votre .env.docker)
   - PhpMyAdmin : `http://localhost:8182` 
   - Créer votre compte `http://localhost/register`

## Installation Manuelle

    Pré-requis: 
        - WAMP ou un serveur web : (Apache ou Nginx)
        - composer
        - php
        - mysql
        - sass (pour compiler le scss)

    1. Éditez le fichier .env pour décommenter la ligne de la base de donnée.
    2. Remplacez les valeurs username, password, urldatabase, nomdelabase par les valeurs de votre environnement
    3. Installez les dépendances : 
        - composer install
    4. Générez la base de donnée :
        - php bin/console doctrine:database:create --if-not-exists
        - php bin/console doctrine:migrations:migrate (Pour mettre à jour le schéma depuis les migrations du projet)
    5. Générez les fixtures : 
        - php bin/console doctrine:fixtures:load
    6. Accédez au site sur l'url configurée sur votre serveur
    7. Créer votre compte utilisateur sur l'URI /register








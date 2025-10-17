# GreenGoodies - E-commerce

## 🚀 Installation rapide

Ce projet est conçu pour être **facilement exportable et partageable**. Toute la configuration se fait via le fichier `.env.docker`.

### Étapes d'installation :

1. **Cloner le projet**
   ```bash
   git clone <votre-repo>
   cd greenGoodiesTest
   ```

2. **Configurer les variables d'environnement**
   
   Éditez le fichier `.env.docker` avec vos paramètres :
   ```bash
   DB_USER=votre_utilisateur
   DB_PASSWORD=votre_mot_de_passe
   DB_NAME=votre_base_de_donnees
   # ... autres variables
   ```

3. **Lancer l'installation**
   ```bash
   make install
   ```
   
   Cette commande va automatiquement :
   - ✅ Générer le fichier `.env.local` avec les bonnes variables
   - ✅ Construire et démarrer les conteneurs Docker
   - ✅ Installer les dépendances Composer
   - ✅ Créer la base de données et exécuter les migrations
   - ✅ Compiler les fichiers SCSS

4. **Accéder au projet**
   - Application : `http://localhost:8181` (ou le port défini dans `HOST_NGINX_PORT`)
   - PhpMyAdmin : `http://localhost:8182`
   - MailHog : `http://localhost:8025`

5. **Connexion administrateur**
   - Email : `admin@greengoodiestest.com`
   - Mot de passe : `admin123`

## 📋 Commandes disponibles

```bash
make install    # Installation complète du projet
make up         # Démarrer les conteneurs
make down       # Arrêter les conteneurs
make env        # Regénérer .env.local à partir de .env.docker
make watch      # Compiler le SCSS en temps réel
make cache      # Vider le cache Symfony
make fixtures   # Charger les fixtures (dev uniquement)
make restart    # Redémarrer les conteneurs
make prune      # Supprimer conteneurs et volumes
```

## 🔧 Configuration automatique

Le fichier `.env.local` est **généré automatiquement** par le Makefile à partir de `.env.docker`. 

**Exemple :** Si vous définissez dans `.env.docker` :
```bash
DB_USER=toto
DB_PASSWORD=secret123
DB_NAME=ma_base
```

Le Makefile va automatiquement créer `.env.local` avec :
```bash
DATABASE_URL="mysql://toto:secret123@database:3306/ma_base?serverVersion=8.0.32&charset=utf8mb4"
```

Pour regénérer `.env.local` après modification de `.env.docker` :
```bash
make env
```

## 📦 Portabilité

Pour partager le projet avec quelqu'un d'autre :

1. **Commitez** `.env.docker` avec des valeurs par défaut
2. **Ne committez jamais** `.env.local` (déjà dans .gitignore)
3. La personne qui récupère le projet n'a qu'à :
   - Modifier `.env.docker` selon ses besoins
   - Lancer `make install`

Tout sera configuré automatiquement ! 🎉


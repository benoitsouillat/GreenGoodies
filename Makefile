# Makefile pour simplifier la gestion de Docker

# Variable pour raccourcir les commandes
DC = docker-compose --env-file .env.docker

# Charger les variables depuis .env.docker
include .env.docker
export

# 1. Définit l'aide pour l'utilisation du Makefile
.PHONY: help
help:
	@echo "Makefile pour gérer les conteneurs Docker et les tâches courantes."
	@echo ""
	@echo "Commandes disponibles :"
	@echo "  make install      : Construit et lance les conteneurs, installe les dépendances et prépare le projet."
	@echo "  make up           : Démarre les conteneurs"
	@echo "  make down         : Arrête les conteneurs"
	@echo "  make env          : Regénère le fichier .env.local à partir de .env.docker"
	@echo "  make watch        : Compile le SCSS en direct (à lancer dans une console séparée)"
	@echo "  make fix-owner    : Corrige les permissions des fichiers pour pouvoir les exécuter côté WSL"
	@echo "  make cache        : Vide le cache de Symfony"
	@echo "  make prune        : Arrête et supprime les conteneurs et les données (volumes)"
	@echo "  make restart      : Redémarre les conteneurs"
	@echo ""

# 1. Construit et lance les conteneurs
# 2. Installation des dépendances du composer.json
# 3. Création de la base de données et lancement des migrations pour créer le schéma
# 4. Compile le SCSS une première fois
.PHONY: install
install: env up
	@echo "Installation des dépendances Composer..."
	$(DC) exec php composer install
	sleep 4
	$(DC) exec php bin/console doctrine:database:create --if-not-exists
	$(DC) exec php bin/console doctrine:migrations:migrate --no-interaction
	$(DC) exec php sass assets/scss/main.scss assets/css/main.css
	$(MAKE) fix-owner
	@echo "✅ Projet prêt ! Utilisez 'make up' pour démarrer et 'make down' pour arrêter."
	@echo "💡 Lancez 'make watch' dans un autre terminal pour compiler le SCSS en direct."

# Démarre les conteneurs
.PHONY: up
up:
	@echo "Lancement des conteneurs..."
	$(DC) up -d --build
	@echo "Vous pouvez utiliser 'make watch' dans un autre terminal pour compiler le SCSS en direct."

# Arrête les conteneurs
.PHONY: down
down:
	@echo "Arrêt des conteneurs..."
	$(DC) down

# Génère le fichier .env.local à partir de .env.docker
.PHONY: env
env:
	@echo "🔧 Génération du fichier .env.local..."
	@echo "###> symfony/framework-bundle ###" > .env.local
	@echo "APP_SECRET=e798a18192bb7b015c4f77fc192d7e19" >> .env.local
	@echo "###< symfony/framework-bundle ###" >> .env.local
	@echo "" >> .env.local
	@echo "###> Auto-generated from .env.docker ###" >> .env.local
	@echo "DATABASE_URL=\"mysql://$(DB_USER):$(DB_PASSWORD)@database:3306/$(DB_NAME)?serverVersion=8.0.32&charset=utf8mb4\"" >> .env.local
	@echo "###< Auto-generated from .env.docker ###" >> .env.local
	@echo "✅ Fichier .env.local généré avec succès !"

# Compile le SCSS en direct
.PHONY: watch
watch:
	@echo "👀 Lancement du watch SCSS... (CTRL+C pour arrêter)"
	$(DC) exec php sass --watch assets/scss/main.scss:assets/css/main.css

# Création des fixtures (Uniquement en dev)
.PHONY: fixtures
fixtures:
	@echo "Chargement des fixtures de développement..."
	$(DC) exec php bin/console doctrine:fixtures:load --no-interaction

# Corrige les permissions des dossiers var/ et public/
.PHONY: fix-perms
fix-perms:
	@echo "Correction des permissions pour les dossiers var/ et public/... $(whoami)"
	$(DC) exec php sh -c 'setfacl -R -m u:www-data:rwX -m u:$(whoami):rwX . || true'
	$(DC) exec php sh -c 'setfacl -dR -m u:www-data:rwX -m u:$(whoami):rwX . || true'

.PHONY: fix-owner
fix-owner:
	@echo "Correction du propriétaire côté WSL..."
	sudo chown -R $$(id -u):$$(id -g) .

# Vide le cache de Symfony
.PHONY: cache
cache:
	@echo "Nettoyage du cache Symfony..."
	$(DC) exec php bin/console cache:clear

# Arrête et supprime les données (volumes)
.PHONY: prune
prune:
	@echo "ATTENTION : Suppression des conteneurs et de toutes les données..."
	$(DC) down -v

# Redémarre les conteneurs
.PHONY: restart
restart: down up cache

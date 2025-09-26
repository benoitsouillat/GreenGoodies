# Makefile pour simplifier la gestion de Docker

# Variable pour raccourcir les commandes
DC = docker-compose --env-file .env.docker

# 1. Définit l'aide pour l'utilisation du Makefile
.PHONY: help
help:
	@echo "Makefile pour gérer les conteneurs Docker et les tâches courantes."
	@echo ""
	@echo "Commandes disponibles :"
	@echo "  make install      : Construit et lance les conteneurs, installe les dépendances et prépare le projet."
	@echo "  make up           : Démarre les conteneurs"
	@echo "  make down         : Arrête les conteneurs"
	@echo "  make watch        : Compile le SCSS en direct (à lancer dans une console séparée)"
	@echo "  make fix-perms    : Corrige les permissions des dossiers var/ et public/"
	@echo "  make cache        : Vide le cache de Symfony"
	@echo "  make prune        : Arrête et supprime les conteneurs et les données (volumes)"
	@echo "  make restart      : Redémarre les conteneurs"
	@echo ""

# 1. Construit et lance les conteneurs
# 2. Installe les outils (npm, sass) et les dépendances (composer)
.PHONY: install
install: up
	@echo "Installation des dépendances Composer..."
	$(DC) exec php composer install
	sleep 4
	$(DC) exec php bin/console doctrine:database:create --if-not-exists
	$(DC) exec php bin/console doctrine:migrations:migrate --no-interaction
	$(DC) exec php sass assets/scss/main.scss assets/css/main.css
	$(MAKE) fix-perms
	# $(MAKE) chown
	@echo "✅ Projet prêt ! Utilisez 'make up' pour démarrer et 'make down' pour arrêter."
	@echo "💡 Lancez 'make watch' dans un autre terminal pour compiler le SCSS en direct."

# Démarre les conteneurs (ou les reconstruit si des fichiers ont changé)
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

# Compile le SCSS en direct (à lancer dans un terminal séparé)
.PHONY: watch
watch:
	@echo "👀 Lancement du watch SCSS... (CTRL+C pour arrêter)"
	$(DC) exec php sass --watch assets/scss/main.scss:assets/css/main.css

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

.PHONY: chown
chown:
	@echo "Correction du propriétaire côté WSL..."
	sudo chown -R $$(id -u):$$(id -g) var public

# Redémarre les conteneurs
.PHONY: restart
restart: down up

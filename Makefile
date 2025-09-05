# Makefile pour simplifier la gestion de Docker

# Lance les conteneurs en arrière-plan
up:
	docker-compose --env-file .env.docker up -d --build && \
    	echo "Attente de 5s pour la stabilisation des conteneurs..." && \
    	sleep 5 && \
    	docker-compose --env-file .env.docker exec php chown -R www-data:www-data /code/var

# Arrête et supprime les conteneurs
down:
	docker-compose --env-file .env.docker down

# Vider les données
prune:
	docker-compose --env-file .env.docker down -v

# Affiche les logs en temps réel
logs:
	docker-compose --env-file .env.docker logs -f

# Démarre les conteneurs
start:
	docker-compose --env-file .env.docker up -d

# Redémarre les conteneurs
restart:
	docker-compose --env-file .env.docker restart

#Vider la cache Symfony
cache:
	docker-compose exec php console cache:clear

COMPOSE := docker compose -f docker/docker-compose.yml

all: up install test

up:
	$(COMPOSE) up -d

install:
	$(COMPOSE) run --rm php composer install

test:
	$(COMPOSE) run --rm php composer run-script test

down: 
	$(COMPOSE) down -v